<div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-4xl p-6 bg-white rounded-lg shadow-lg">
        <h2 class="mb-6 text-2xl font-bold text-center text-gray-700">Available Tables and Chairs</h2>

        <!-- Display all tables and chairs -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($tables as $table => $chairs)
                <div class="p-4 border rounded-lg shadow">
                    <h3 class="mb-2 text-lg font-semibold text-gray-700">{{ $table }}</h3>
                    <ul class="ml-0">
                        @foreach ($chairs as $chair)
                        <li
                            wire:key="chair-{{ $chair }}"
                            class="block px-2 py-1 text-gray-600 transition duration-200 bg-red-500 rounded cursor-pointer hover:bg-blue-300 hover:text-white"
                            wire:click="selectChair('{{ $table }}', '{{ $chair }}')">
                        {{ $chair }}
                        </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

        <!-- Display the selected chair -->
        @if ($selectedChair)
            <div class="mt-6 font-semibold text-center text-gray-800">
                <p>You selected: <span class="text-blue-600">{{ $selectedChair }}</span></p>
            </div>
        @endif

        <!-- Display Success Message -->
        @if (session()->has('message'))
            <div class="mt-6 font-semibold text-center text-green-600">
                {{ session('message') }}
            </div>
        @endif
    </div>
</div>
