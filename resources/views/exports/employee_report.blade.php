<!-- resources/views/exports/employee_report.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Employee Report</title>
    <meta charset="utf-8">
</head>
<body>
    <h1>Employee Report for {{ $month }}/{{ $year }}</h1>
    <table border="1">
        <thead>
            <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                @foreach (range(1, $daysInMonth) as $day)
                    <th>{{ $day }}</th>
                @endforeach
                <th>Total Bought</th>
                <th>Balance Remaining</th>
                <th>Expected Deduction</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($reportData as $data)
                <tr>
                    <td>{{ $data['employee_id'] }}</td>
                    <td>{{ $data['employee_name'] }}</td>
                    @foreach ($data['daily_purchases'] as $amount)
                        <td>{{ $amount }}</td>
                    @endforeach
                    <td>{{ $data['total_bought'] }}</td>
                    <td>{{ $data['balance_remaining'] }}</td>
                    <td>{{ $data['expected_deduction'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
