<x-filament::page>
    <div class="space-y-6">
        <!-- Report Form -->
        <form wire:submit.prevent="downloadReport" class="flex flex-wrap items-center p-6 bg-white rounded-lg shadow dark:bg-gray-800">
            <div class="flex gap-3 space-x-4">
                <div class="flex flex-col space-y-2">
                    <label for="month" class="text-sm font-semibold text-gray-800 dark:text-gray-200">Select Month:</label>
                    <select wire:model="month" id="month" class="border-gray-300 rounded-md dark:border-gray-600 form-select dark:bg-gray-700 dark:text-gray-200">
                        @foreach (range(1, 12) as $m)
                            <option value="{{ $m }}">{{ date("F", mktime(0, 0, 0, $m, 1)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col space-y-2">
                    <label for="year" class="text-sm font-semibold text-gray-800 dark:text-gray-200">Select Year:</label>
                    <input type="number" wire:model="year" id="year" class="border-gray-300 rounded-md dark:border-gray-600 form-input dark:bg-gray-700 dark:text-gray-200" min="2000" max="{{ now()->year }}">
                </div>
                <div class="pt-6">
                    <button wire:click="downloadReport" type="button" class="btn btn-primary">Download Excel Report</button>
                </div>
            </div>
        </form>

        <!-- Report Table -->
        <div class="p-6 overflow-x-auto bg-white rounded-lg shadow dark:bg-gray-800">
            <table class="w-full border-collapse table-auto">
                <thead class="bg-gray-100 dark:bg-gray-900">
                    <tr>
                        <th class="p-3 text-sm font-semibold text-left text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">Employee</th>
                        @foreach (range(1, 31) as $day) <!-- Adjust based on max days in month -->
                            <th class="p-3 text-sm font-semibold text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ $day }}</th>
                        @endforeach
                        <th class="p-3 text-sm font-semibold text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">Total Bought</th>
                        <th class="p-3 text-sm font-semibold text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">Balance Remaining</th>
                        <th class="p-3 text-sm font-semibold text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">Expected Deduction</th>
                    </tr>
                </thead>
                <tbody>
                    @if(count($reportData) > 0)
                    @foreach ($reportData as $data)
                        <tr class="odd:bg-gray-100 even:bg-white odd:dark:bg-gray-700 even:dark:bg-gray-800">
                            <td class="p-3 text-sm text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ $data['employee']->name }}</td>
                            @foreach ($data['daily_purchases'] as $amount)
                                <td class="p-3 text-sm text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ number_format($amount, 2) }}</td>
                            @endforeach
                            <td class="p-3 text-sm font-semibold text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ number_format($data['total_bought'], 2) }}</td>
                            <td class="p-3 text-sm text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ number_format($data['balance_remaining'], 2) }}</td>
                            <td class="p-3 text-sm text-center text-gray-800 border border-gray-200 dark:text-gray-200 dark:border-gray-600">{{ number_format($data['expected_deduction'], 2) }}</td>
                        </tr>
                    @endforeach
                    @else
                        <tr>
                            <td colspan="100%" class="p-3 text-center text-gray-800 dark:text-gray-200">No record for chosen date</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</x-filament::page>
