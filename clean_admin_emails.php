<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$admins = User::where('role', 'admin')->whereNotNull('hospital_name')->get();
foreach ($admins as $admin) {
    // Basic cleanup: remove trailing underscores before @example.com
    $newEmail = preg_replace('/_+@example\.com/', '@example.com', $admin->email);
    if ($newEmail !== $admin->email) {
        $admin->email = $newEmail;
        $admin->save();
        echo "Updated to: " . $newEmail . "\n";
    } else {
        echo "Kept: " . $admin->email . "\n";
    }
}
