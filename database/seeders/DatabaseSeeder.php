<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Intern;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // create an Admin User
        $adminUser = User::create([
            'name' => 'Supervisor Ferdy',
            'email' => 'admin@inturn.com',
            'password' => Hash::make('admin123'),
            'gender' => 'Male',
        ]);

        $admin = Admin::create([
            'user_id' => $adminUser->user_id,
        ]);

        // creating 5 interns then linking to supervisor ferdy admin
        User::factory(5)->create()->each(function ($user) use ($admin) {
            Intern::create([
                'user_id' => $user->user_id,
                'admin_id' => $admin->admin_id,
                'university' => 'University of Westeros',
                'department' => 'Computer Science',
                'supervisor' => 'Ferdy',
                'start_date' => now(),
                'phone_number' => '09123456789',
                'emergency_contact' => '09987654321',
                'emergency_contact_name' => 'Marites Ollaban',
                'address' => '123 Main St, Mandaue City',
                'status' => 'Active',
            ]);
        });
    }
}
