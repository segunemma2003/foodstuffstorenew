<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromView;
use Illuminate\Contracts\View\View;

class EmployeeReportExport implements FromView
// FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function collection()
    {
        return Employee::with('user','company')->get();
    }

    public function view(): View
    {
        // Get employees and transactions for the specified month/year
        $employees = Employee::with(['user','company','transactions' => function ($query) {
            $query->whereYear('date', $this->year)
                  ->whereMonth('date', $this->month);
        }])->get();


        // dd(gettype($this->month), gettype($this->year));
        // Get the total number of days in the specified month
        $daysInMonth = now()->setYear((int) $this->year)->setMonth((int)$this->month)->daysInMonth;

        // dd($daysInMonth);
        // Create report data for each employee with daily entries
        $reportData = $employees->map(function ($employee) use ($daysInMonth) {
            $dailyPurchases = collect();

            // Loop through each day of the month to populate daily purchases
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = now()->setYear($this->year)->setMonth($this->month)->setDay($day)->toDateString();
                // Check for a purchase on the specific date
                $purchase = $employee->transactions->firstWhere('date', $date);
                // Set the amount or 0 if there was no purchase
                $dailyPurchases[$date] = $purchase ? $purchase->amount : 0;
            }

            return [
                'employee_id' => $employee->id,
                'employee'=> $employee->user,
                'employee_name' => $employee->user->id, // Assuming the Employee model has a name attribute
                'daily_purchases' => $dailyPurchases,
                'total_bought' => $dailyPurchases->sum(), // Sum of all purchases in the month
                'balance_remaining' => $employee->balance, // Employee's current balance
                'expected_deduction' => $employee->debit_limit ?? 0, // Default to 0 if not set
            ];
        });

        return view('exports.employee_report', [
            'reportData' => $reportData,
            'daysInMonth' => $daysInMonth,
            'month'=> $this->month,
            'year'=> $this->year
        ]);
    }
}
