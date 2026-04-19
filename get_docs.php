<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;

$doctors = User::where('role', 'doctor')->take(10)->get();
foreach ($doctors as $d) {
    echo $d->name . " => Email: " . $d->email . "\n";
}
