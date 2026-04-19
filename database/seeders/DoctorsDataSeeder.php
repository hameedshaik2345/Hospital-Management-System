<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Hospital;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DoctorsDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/hospitals_doctors.csv');
        if (!file_exists($csvPath)) {
            $this->command->error("CSV file not found at {$csvPath}");
            return;
        }

        $file = fopen($csvPath, 'r');
        $headers = fgetcsv($file);

        while (($row = fgetcsv($file)) !== false) {
            if (count($headers) !== count($row)) {
                continue;
            }
            $data = array_combine($headers, $row);

            $hospitalName = trim($data['Hospital']);
            $doctorName = trim($data['Doctor']);
            $specialization = trim($data['Specilization']);
            $experienceStr = trim($data['Experience']);
            
            // Handle potentially empty rows
            if (empty($doctorName)) {
                continue;
            }

            // Extract numbers from experience string e.g. "37+ Years"
            preg_match('/\d+/', $experienceStr, $matches);
            $experienceYears = isset($matches[0]) ? (int)$matches[0] : null;

            // 1. Create or get Hospital
            if (!empty($hospitalName)) {
                $hospital = Hospital::firstOrCreate(
                    ['name' => $hospitalName]
                );
            }

            // 2. Create User for Doctor
            $email = Str::slug($doctorName, '.') . rand(1000, 9999) . '@example.com';
            
            $user = User::firstOrCreate(
                ['name' => $doctorName, 'role' => 'doctor'],
                [
                    'email' => $email,
                    'password' => Hash::make('password123'),
                    'role' => 'doctor',
                    'specialty' => $specialization,
                    'experience_years' => $experienceYears,
                ]
            );

            // 3. Create Doctor record
            Doctor::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $doctorName,
                    'specialization' => $specialization,
                    'hospital_name' => $hospitalName,
                    'fees' => rand(500, 2000), // Setting a random fee since it is not provided
                ]
            );
        }

        fclose($file);
        $this->command->info('Doctors and Hospitals imported successfully.');
    }
}
