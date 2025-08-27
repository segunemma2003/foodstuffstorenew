<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class EmployeesExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Employee::with(['user', 'company'])->get();
    }

    public function headings(): array
    {
        return [
            'Employee ID',
            'Employee Name',
            'Card Number',
            'Company',
            'Date Created',
            'Salary',
            'Credit Limit'
        ];
    }

    public function map($employee): array
    {
        return [
            $employee->id,
            $employee->user->name,
            $employee->card_number,
            $employee->company->name,
            $employee->created_at->format('Y-m-d'),
            $employee->salary,
            $employee->credit_limit
        ];
    }
}
