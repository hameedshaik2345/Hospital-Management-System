<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Doctor;
use App\Models\Hospital;

$mappings = [
    'AIIMS' => 'AIIMS Hospital, Mangalgiri',
    'NRI' => 'NRI General Hospital, Mangalagiri',
    'Manipal' => 'Manipal Hospitals, Tadepalli',
    'Kamin' => 'Kamineni Hospital, Poranki',
    'Prasad' => 'L V Prasad Eye Institute, Poranki',
    'Aster Ramesh' => 'Aster Ramesh Guntur',
    'Vedanta' => 'Vedanta Hospital'
];

$doctors = Doctor::all();
foreach ($doctors as $doc) {
    foreach ($mappings as $key => $masterName) {
        if (stripos($doc->hospital_name, $key) !== false) {
            $doc->hospital_name = $masterName;
            $doc->save();
            break;
        }
    }
}

$hospitals = Hospital::all();
$keptHospitals = [];

foreach ($hospitals as $h) {
    foreach ($mappings as $key => $masterName) {
        if (stripos($h->name, $key) !== false) {
            if (!isset($keptHospitals[$masterName])) {
                // Keep this one and rename to the proper format
                $h->name = $masterName;
                $h->save();
                $keptHospitals[$masterName] = true;
            } else {
                // Duplicate found, remove it
                $h->delete();
            }
            break;
        }
    }
}

echo "Hospitals deduplicated successfully.\n";
