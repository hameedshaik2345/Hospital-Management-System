<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$doctors = User::where('role', 'doctor')->take(10)->get();
foreach ($doctors as $d) {
    $d->password = Hash::make('password123');
    $d->save();
    echo "Reset password for: " . $d->email . "\n";
}
