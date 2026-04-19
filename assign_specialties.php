<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$doctors = App\Models\User::where('role', 'doctor')->get();
$specialties = ['General Physician', 'Cardiologist', 'Neurologist', 'Dentist', 'ENT Specialist', 'Orthopedic', 'Pediatrician', 'Dermatologist'];
foreach ($doctors as $index => $doctor) {
    $spec = $specialties[$index % count($specialties)];
    $doctor->update(['specialty' => $spec, 'department' => $spec]);
    if ($doctor->doctorProfile) {
        $doctor->doctorProfile->update(['specialization' => $spec]);
    }
    echo 'Updated ' . $doctor->name . ' to ' . $spec . PHP_EOL;
}
