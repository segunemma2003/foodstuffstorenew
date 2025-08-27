<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class UpdateEmployeeSalaries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-employee-salaries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $halfSalary = $employee->salary / 2;

            // Update balance and credit limit
            $employee->available_balance += $halfSalary;
            $employee->credit_limit += $halfSalary;
            $employee->save();
        }

        $this->info('Employee salaries updated successfully.');
    }
}
