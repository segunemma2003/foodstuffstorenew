<?php

namespace App\Jobs;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateUserSalaries implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            if ($employee->salary) {
                // Calculate half of the employee's salary
                $halfSalary = $employee->salary / 2;

                // Update credit limit and available balance
                $employee->credit_limit += $halfSalary;
                $employee->balance += $halfSalary; // Deduct from available balance

                // Save the changes
                $employee->save();
            }
        }
    }
}
