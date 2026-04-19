<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class PushNotificationController extends Controller
{
    /**
     * Save the patient's push subscription from browser
     */
    public function subscribe(Request $request)
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        PushSubscription::updateOrCreate(
            ['user_id' => Auth::id(), 'endpoint' => $request->endpoint],
            [
                'p256dh_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Send a push notification to a specific user (called internally)
     */
    public static function sendToUser($userId, $title, $body, $url = '/patient/dashboard')
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();

        foreach ($subscriptions as $sub) {
            try {
                self::sendPushMessage($sub, [
                    'title' => $title,
                    'body'  => $body,
                    'url'   => $url,
                ]);
            } catch (\Exception $e) {
                Log::error("Push notification failed for user $userId: " . $e->getMessage());
                // If endpoint is gone (user unsubscribed), delete it
                if (strpos($e->getMessage(), '410') !== false || strpos($e->getMessage(), '404') !== false) {
                    $sub->delete();
                }
            }
        }
    }

    /**
     * Send the actual Web Push message using VAPID
     */
    private static function sendPushMessage($subscription, array $payload)
    {
        $vapidPublicKey  = env('VAPID_PUBLIC_KEY');
        $vapidPrivateKey = env('VAPID_PRIVATE_KEY');
        $vapidSubject    = env('APP_URL', 'http://localhost');

        if (!$vapidPublicKey || !$vapidPrivateKey) {
            Log::warning("VAPID keys not configured. Push notification skipped.");
            return;
        }

        $payloadJson = json_encode($payload);

        // Build JWT for VAPID
        $header = self::base64UrlEncode(json_encode(['typ' => 'JWT', 'alg' => 'ES256']));
        $audience = parse_url($subscription->endpoint, PHP_URL_SCHEME) . '://' . parse_url($subscription->endpoint, PHP_URL_HOST);
        $claims = self::base64UrlEncode(json_encode([
            'aud' => $audience,
            'exp' => time() + 86400,
            'sub' => 'mailto:' . env('MAIL_FROM_ADDRESS', 'noreply@medflow.com'),
        ]));

        $signingInput = "$header.$claims";
        $privateKeyPem = self::vapidPrivateToPem($vapidPrivateKey);
        $privateKeyResource = openssl_pkey_get_private($privateKeyPem);
        
        if (!$privateKeyResource) {
            Log::error("Failed to load VAPID private key.");
            return;
        }

        if (!openssl_sign($signingInput, $signature, $privateKeyResource, OPENSSL_ALGO_SHA256)) {
            Log::error("Failed to sign VAPID JWT.");
            return;
        }
        
        // Convert DER signature to raw R|S (64 bytes)
        $rawSignature = self::derToRawSignature($signature, 64);
        $jwtToken = "$signingInput." . self::base64UrlEncode($rawSignature);

        $authHeader = "vapid t=$jwtToken, k=$vapidPublicKey";

        // Encrypt the payload
        $encryptionData = self::encryptPayload(
            $payloadJson,
            $subscription->auth_token,
            $subscription->p256dh_key
        );

        if (!$encryptionData) {
            Log::error("Failed to encrypt push payload.");
            return;
        }

        [$encryptedPayload, $salt, $serverPublicKey] = $encryptionData;

        // Send the request
        $ch = curl_init($subscription->endpoint);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                "Content-Type: application/octet-stream",
                "Content-Encoding: aes128gcm",
                "Authorization: $authHeader",
                "TTL: 86400",
            ],
            CURLOPT_POSTFIELDS => $encryptedPayload,
            CURLOPT_SSL_VERIFYPEER => false,  // Local dev only
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            throw new \Exception("CURL failure: $error");
        }

        if ($httpCode === 410 || $httpCode === 404) {
            throw new \Exception("Subscription expired or not found (HTTP $httpCode)");
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            throw new \Exception("Push service returned HTTP $httpCode: $response");
        }

        Log::info("Push sent to user. HTTP: $httpCode");
    }

    private static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function encryptPayload($payload, $authToken, $p256dhKey)
    {
        $salt = random_bytes(16);
        $serverPrivateKey = openssl_pkey_new(['curve_name' => 'prime256v1', 'private_key_type' => OPENSSL_KEYTYPE_EC]);
        $serverPublicKeyDetails = openssl_pkey_get_details($serverPrivateKey);
        $serverPublicKey = $serverPublicKeyDetails['key'];

        $clientPublicKey = base64_decode(strtr($p256dhKey, '-_', '+/'));
        $authSecret = base64_decode(strtr($authToken, '-_', '+/'));

        // Convert raw client public key to PEM
        $clientPublicKeyPem = self::rawPubKeyToPem($clientPublicKey);
        $clientPublicKeyResource = openssl_pkey_get_public($clientPublicKeyPem);
        
        if (!$clientPublicKeyResource) {
            return null;
        }

        // Compute shared secret using ECDH
        if (!openssl_pkey_derive($clientPublicKeyResource, $serverPrivateKey, $sharedSecret)) {
            return null;
        }

        // HKDF PRK
        $prk = hash_hmac('sha256', $sharedSecret, $authSecret, true);
        
        // Content encryption key and nonce
        $serverPublicKeyBin = self::ecPublicKeyToDer($serverPublicKeyDetails);
        $info = "Content-Encoding: aes128gcm\x00";
        $contentEncryptionKey = substr(self::hkdf($salt, $prk, $info, 16), 0, 16);
        $nonceInfo = "Content-Encoding: nonce\x00";
        $nonce = substr(self::hkdf($salt, $prk, $nonceInfo, 12), 0, 12);

        // Encrypt with AES-128-GCM
        $paddedPayload = $payload . "\x02";
        $encrypted = openssl_encrypt($paddedPayload, 'aes-128-gcm', $contentEncryptionKey, OPENSSL_RAW_DATA, $nonce, $tag);
        
        $recordSize = pack('N', strlen($paddedPayload) + 16 + 1);
        $keyidLen = pack('C', strlen($serverPublicKeyBin));
        $result = $salt . $recordSize . $keyidLen . $serverPublicKeyBin . $encrypted . $tag;

        return [$result, $salt, $serverPublicKeyBin];
    }

    private static function hkdf($salt, $ikm, $info, $length)
    {
        $prk = hash_hmac('sha256', $ikm, $salt, true);
        $t = '';
        $lastT = '';
        $counter = 1;
        while (strlen($t) < $length) {
            $lastT = hash_hmac('sha256', $lastT . $info . chr($counter++), $prk, true);
            $t .= $lastT;
        }
        return substr($t, 0, $length);
    }

    private static function ecPublicKeyToDer($keyDetails)
    {
        $x = $keyDetails['ec']['x'];
        $y = $keyDetails['ec']['y'];
        return "\x04" . str_pad($x, 32, "\x00", STR_PAD_LEFT) . str_pad($y, 32, "\x00", STR_PAD_LEFT);
    }

    private static function vapidPrivateToPem($base64Key)
    {
        $keyData = base64_decode(strtr($base64Key, '-_', '+/'));
        // Construct a proper EC private key PEM (SEC1 format)
        $der = "\x30\x77\x02\x01\x01\x04\x20" . $keyData . "\xa0\x0a\x06\x08\x2a\x86\x48\xce\x3d\x03\x01\x07";
        return "-----BEGIN EC PRIVATE KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END EC PRIVATE KEY-----\n";
    }

    private static function rawPubKeyToPem($rawKey)
    {
        // P-256 Public Key Header
        $derHeader = pack('H*', '3059301306072a8648ce3d020106082a8648ce3d030107034200');
        $der = $derHeader . $rawKey;
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($der), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }

    private static function derToRawSignature($der, $length)
    {
        $signature = unpack('C*', $der);
        $pos = 3;
        $rLen = $signature[$pos++];
        if ($signature[$pos] === 0) { $pos++; $rLen--; }
        $r = substr($der, $pos - 1, $rLen);
        $pos += $rLen + 1;
        $sLen = $signature[$pos++];
        if ($signature[$pos] === 0) { $pos++; $sLen--; }
        $s = substr($der, $pos - 1, $sLen);
        return str_pad($r, $length / 2, "\x00", STR_PAD_LEFT) . str_pad($s, $length / 2, "\x00", STR_PAD_LEFT);
    }
}
