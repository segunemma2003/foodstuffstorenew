<?php

namespace App\Filament\CompanyUser\Pages;

use App\Exports\EmployeeReportExport;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;

class EmployeeReport extends Page
{
    // protected static ?string $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationIcon = null;
    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.company-user.pages.employee-report';


    public $month;
    public $year;
    public $reportData = [];
    public $daysInMonth;

    public function mount()
    {
        $this->month = now()->month;
        $this->year = now()->year;
        $this->loadReportData();
    }

    public function downloadReport()
    {
        return Excel::download(new EmployeeReportExport($this->month, $this->year), 'Employee_Report.xlsx');
    }

    public function loadReportData()
    {
        // Get report data and assign it to the component properties
        $data = (new EmployeeReportExport($this->month, $this->year))->view()->getData();
        // dd($data);
        $this->reportData = $data['reportData'];
        $this->daysInMonth = $data['daysInMonth'];
    }

    public function getReportData()
    {
        return (new EmployeeReportExport($this->month, $this->year))->view()->getData();
    }


    // public static function isHidden(): bool
    // {
    //     return true; // This ensures the page won't have a sidebar link
    // }

}
