<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Hospital;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

$hospitals = Hospital::all();

foreach ($hospitals as $hospital) {
    if (!$hospital->name) continue;
    
    $slug = Str::slug($hospital->name, '_');
    // Just to make sure it's somewhat predictable
    $email = "admin_" . substr($slug, 0, 15) . "@example.com";
    
    User::updateOrCreate(
        ['email' => $email],
        [
            'name' => "Admin - " . $hospital->name,
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'hospital_name' => $hospital->name
        ]
    );
    echo "Created admin: $email (Hospital: {$hospital->name})\n";
}
