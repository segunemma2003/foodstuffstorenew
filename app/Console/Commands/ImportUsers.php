<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use League\Csv\Reader;
use App\Models\User;

class ImportUsers extends Command
{
    protected $signature = 'users:import {file=users.csv} {--batch-size=100} {--offset=0}';
    protected $description = 'Import users from a CSV file';

    public function handle()
    {
        $filePath = public_path($this->argument('file'));
        if (!file_exists($filePath)) {
            $this->error('File not found!');
            return;
        }

        $csv = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $failedRecords = [];
        $processedRecords = 0;
        $batchSize = (int) $this->option('batch-size');
        $offset = (int) $this->option('offset');

        DB::beginTransaction();

        try {
            foreach ($csv as $index => $record) {
                if ($index < $offset) {
                    continue; // Skip processed rows
                }

                $email = $record['UserEmail'];
                $phone = $record['Phone'];
                if (substr($phone, 0, 1) === '0') {
                    $phone = '+234' . substr($phone, 1);
                }
                if (User::where('email', $email)->orWhere('phone', $phone)->exists()) {
                    $this->warn("User with email $email or phone $phone already exists.");
                    continue;
                }

                // Split username to get fname and lname
                $nameParts = explode(' ', $record['Username']);
                $fname = $nameParts[0];
                $lname = $nameParts[1] ?? '';

                // Create new user
                $user = new User();
                $user->fname = $fname;
                $user->lname = $lname;
                $user->name = $fname . ' ' . $lname;
                $user->email = $email;
                $user->image = $record['Image'] ?? "https://default-image-url.com";
                $user->phone = $phone;
                $user->residential = 'Abuja';  // Default to 'Abuja'
                $user->wallet_balance = 0.00;
                $user->points = 0.00;
                $user->password = bcrypt($record['Passphrase']);  // Hash the password
                $user->user_type = strtolower($record['AccountType']) === 'regular' ? 'regular' : 'company';

                $user->save();

                $processedRecords++;

                if ($processedRecords >= $batchSize) {
                    break; // Stop after processing the batch size
                }
            }

            DB::commit();

            $this->info("Processed $processedRecords records.");
            $this->info("Next offset: " . ($offset + $processedRecords));

            if ($offset + $processedRecords >= count($csv)) {
                $this->info('All records successfully added.');
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error during processing: ' . $e->getMessage());
        }
    }
}
