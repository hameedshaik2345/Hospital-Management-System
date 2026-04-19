<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Email sending to hameedshaik786.01@gmail.com...\n";
echo "Mail host: " . env('MAIL_HOST') . "\n";
echo "Mail user: " . env('MAIL_USERNAME') . "\n";

try {
    \Illuminate\Support\Facades\Mail::raw(
        "Hello! This is a test email from MedFlow. If you receive this, your email configuration is working correctly!\n\nToken Alert system is ready.",
        function($msg) {
            $msg->to('hameedshaik786.01@gmail.com')
                ->subject('✅ MedFlow Email Test - Working!');
        }
    );
    echo "SUCCESS! Email sent!\n";
} catch (\Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}
