<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$apiKey = env('FAST2SMS_API_KEY');
echo "API Key: " . substr($apiKey, 0, 20) . "...\n";

// Test SMS
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$url = 'https://www.fast2sms.com/dev/bulkV2?' . http_build_query([
    'message'  => 'MedFlow Test SMS - this is a test alert',
    'language' => 'english',
    'route'    => 'q',
    'numbers'  => '6302972086',
]);

$result = file_get_contents($url, false, stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "authorization: $apiKey\r\n",
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]));

echo "SMS API Response:\n";
echo $result . "\n";

// Test Email
echo "\n--- Testing Email ---\n";
try {
    \Illuminate\Support\Facades\Mail::raw('This is a test email from MedFlow token alert system.', function($msg) {
        $msg->to('hameedshaik786.01@gmail.com')
            ->subject('MedFlow Test Email');
    });
    echo "Email sent successfully!\n";
} catch (\Exception $e) {
    echo "Email Error: " . $e->getMessage() . "\n";
}
