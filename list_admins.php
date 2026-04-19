<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$admins = User::where('role', 'admin')->get();
foreach ($admins as $admin) {
    echo $admin->email . "\n";
}
