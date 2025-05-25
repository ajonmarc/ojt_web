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

            // Create Student User
    User::updateOrCreate(
        ['email' => 'student1@gmail.com'],
        [
            'name' => '2021-00191',
            'password' => Hash::make('password'),
            'studentId'=> '09382989291',
            'studentPhone'=> '09382989291',
            'ojtProgram'=> 'BSIS',
            'status'=> 'active',

            'role' => 'student'
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
