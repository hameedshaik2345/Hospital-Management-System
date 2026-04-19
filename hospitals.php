<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$hospitals = App\Models\Hospital::all();
foreach($hospitals as $h) {
    // Mangalagiri / Vijayawada roughly bounds:
    // Lat: 16.400000 to 16.550000
    // Lng: 80.500000 to 80.650000
    $lat = 16.400000 + (mt_rand(0, 150000) / 1000000);
    $lng = 80.500000 + (mt_rand(0, 150000) / 1000000);
    
    $h->latitude = $lat;
    $h->longitude = $lng;
    $h->save();
}
echo "Updated hospitals with coordinates.\n";
