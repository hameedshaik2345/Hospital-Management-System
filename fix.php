<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$user = User::where('email', 'admin_aiims_hospital_@example.com')->first();
if ($user) {
    $user->email = 'admin_aiims_hospital@example.com';
    $user->save();
    echo "Fixed AIIMS email to admin_aiims_hospital@example.com\n";
} else {
    echo "User not found\n";
}
