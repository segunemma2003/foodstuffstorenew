<?php

namespace App\Livewire;

use Livewire\Component;

class OrderMenu extends Component
{
    public $selectedChair = null; // Stores the selected chair
    public $tables = [];          // Holds tables and chairs dynamically

    public function mount()
    {
        // Generate tables and chairs dynamically
        for ($table = 1; $table <= 31; $table++) {
            $chairs = ($table === 30 || $table === 31) ? 9 : 8; // 9 chairs for Table 30 & 31
            $this->tables["Table {$table}"] = []; // Initialize table as a key in the array

            for ($char = 65; $char < 65 + $chairs; $char++) {
                $this->tables["Table {$table}"][] = "Chair " . chr($char);
            }
        }
    }

    public function selectChair($table, $chair)
    {
        $this->selectedChair = "{$table} - {$chair}"; // Store the selected chair

        // Flash a success message
        session()->flash('message', "You selected: {$this->selectedChair}");
    }

    public function render()
    {
        return view('livewire.order-menu');
    }
}
