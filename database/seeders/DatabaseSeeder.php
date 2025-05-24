<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Program; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
                'role' => 'admin'
            ]
        );

        // Create OJT Programs
        $programs = [
            'BSES',
            'BSCS',
            'BSIS',
            'BSInfoTech',
            'BSHM',
            'BSHM - Claver',
            'BSTM',
            'BAET',
            'BEET',
            'BEXET',
            'BMET-MT',
            'BMET-RAC',
            'BMET-WAFT'
        ];

        foreach ($programs as $program) {
            Program::updateOrCreate(
                ['programName' => $program],
                [
                    'programDescription' => $program . ' Program Description',
                    'status' => 'Active'
                ]
            );
        }
    }
}
