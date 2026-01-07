<?php

namespace Database\Seeders;

use App\Models\Division;
use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $enterpriseSales = Division::where('name', 'Enterprise Sales')->first();
        $backendDev = Division::where('name', 'Backend Development')->first();
        $frontendDev = Division::where('name', 'Frontend Development')->first();
        $recruitment = Division::where('name', 'Recruitment')->first();
        $digitalMarketing = Division::where('name', 'Digital Marketing')->first();
        $techSupport = Division::where('name', 'Technical Support')->first();

        $employees = [
            // Enterprise Sales employees
            [
                'division_id' => $enterpriseSales->id,
                'employee_number' => 'EMP001',
                'first_name' => 'John',
                'last_name' => 'Smith',
                'email' => 'john.smith@example.com',
                'phone' => '555-0101',
                'hire_date' => '2023-01-15',
                'status' => 'active',
            ],
            [
                'division_id' => $enterpriseSales->id,
                'employee_number' => 'EMP002',
                'first_name' => 'Sarah',
                'last_name' => 'Johnson',
                'email' => 'sarah.johnson@example.com',
                'phone' => '555-0102',
                'hire_date' => '2023-02-20',
                'status' => 'active',
            ],
            // Backend Development employees
            [
                'division_id' => $backendDev->id,
                'employee_number' => 'EMP003',
                'first_name' => 'Michael',
                'last_name' => 'Brown',
                'email' => 'michael.brown@example.com',
                'phone' => '555-0103',
                'hire_date' => '2022-06-10',
                'status' => 'active',
            ],
            [
                'division_id' => $backendDev->id,
                'employee_number' => 'EMP004',
                'first_name' => 'Emily',
                'last_name' => 'Davis',
                'email' => 'emily.davis@example.com',
                'phone' => '555-0104',
                'hire_date' => '2022-08-15',
                'status' => 'active',
            ],
            // Frontend Development employees
            [
                'division_id' => $frontendDev->id,
                'employee_number' => 'EMP005',
                'first_name' => 'David',
                'last_name' => 'Wilson',
                'email' => 'david.wilson@example.com',
                'phone' => '555-0105',
                'hire_date' => '2023-03-01',
                'status' => 'active',
            ],
            [
                'division_id' => $frontendDev->id,
                'employee_number' => 'EMP006',
                'first_name' => 'Jessica',
                'last_name' => 'Martinez',
                'email' => 'jessica.martinez@example.com',
                'phone' => '555-0106',
                'hire_date' => '2023-04-12',
                'status' => 'on_leave',
            ],
            // Recruitment employees
            [
                'division_id' => $recruitment->id,
                'employee_number' => 'EMP007',
                'first_name' => 'Robert',
                'last_name' => 'Taylor',
                'email' => 'robert.taylor@example.com',
                'phone' => '555-0107',
                'hire_date' => '2021-11-20',
                'status' => 'active',
            ],
            // Digital Marketing employees
            [
                'division_id' => $digitalMarketing->id,
                'employee_number' => 'EMP008',
                'first_name' => 'Amanda',
                'last_name' => 'Anderson',
                'email' => 'amanda.anderson@example.com',
                'phone' => '555-0108',
                'hire_date' => '2022-01-10',
                'status' => 'active',
            ],
            [
                'division_id' => $digitalMarketing->id,
                'employee_number' => 'EMP009',
                'first_name' => 'Christopher',
                'last_name' => 'Thomas',
                'email' => 'christopher.thomas@example.com',
                'phone' => '555-0109',
                'hire_date' => '2022-05-18',
                'status' => 'inactive',
            ],
            // Technical Support employees
            [
                'division_id' => $techSupport->id,
                'employee_number' => 'EMP010',
                'first_name' => 'Jennifer',
                'last_name' => 'Garcia',
                'email' => 'jennifer.garcia@example.com',
                'phone' => '555-0110',
                'hire_date' => '2023-07-01',
                'status' => 'active',
            ],
            [
                'division_id' => $techSupport->id,
                'employee_number' => 'EMP011',
                'first_name' => 'Matthew',
                'last_name' => 'Rodriguez',
                'email' => 'matthew.rodriguez@example.com',
                'phone' => '555-0111',
                'hire_date' => '2020-03-15',
                'status' => 'terminated',
            ],
        ];

        foreach ($employees as $employee) {
            Employee::create($employee);
        }
    }
}
