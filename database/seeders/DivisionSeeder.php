<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sales = Department::where('name', 'Sales')->first();
        $engineering = Department::where('name', 'Engineering')->first();
        $hr = Department::where('name', 'Human Resources')->first();
        $marketing = Department::where('name', 'Marketing')->first();
        $support = Department::where('name', 'Customer Support')->first();
        $finance = Department::where('name', 'Finance')->first();

        $divisions = [
            // Sales divisions
            [
                'department_id' => $sales->id,
                'name' => 'Enterprise Sales',
                'status' => 'active',
            ],
            [
                'department_id' => $sales->id,
                'name' => 'Retail Sales',
                'status' => 'active',
            ],
            // Engineering divisions
            [
                'department_id' => $engineering->id,
                'name' => 'Backend Development',
                'status' => 'active',
            ],
            [
                'department_id' => $engineering->id,
                'name' => 'Frontend Development',
                'status' => 'active',
            ],
            [
                'department_id' => $engineering->id,
                'name' => 'DevOps',
                'status' => 'active',
            ],
            // HR divisions
            [
                'department_id' => $hr->id,
                'name' => 'Recruitment',
                'status' => 'active',
            ],
            [
                'department_id' => $hr->id,
                'name' => 'Payroll',
                'status' => 'active',
            ],
            // Marketing divisions
            [
                'department_id' => $marketing->id,
                'name' => 'Digital Marketing',
                'status' => 'active',
            ],
            [
                'department_id' => $marketing->id,
                'name' => 'Content Marketing',
                'status' => 'active',
            ],
            // Customer Support divisions
            [
                'department_id' => $support->id,
                'name' => 'Technical Support',
                'status' => 'active',
            ],
            [
                'department_id' => $support->id,
                'name' => 'Customer Success',
                'status' => 'inactive',
            ],
            // Finance divisions
            [
                'department_id' => $finance->id,
                'name' => 'Accounting',
                'status' => 'active',
            ],
            [
                'department_id' => $finance->id,
                'name' => 'Auditing',
                'status' => 'active',
            ],
        ];

        foreach ($divisions as $division) {
            Division::create($division);
        }
    }
}
