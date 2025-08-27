<?php
namespace App\Services;

use App\Models\Customer;
use App\Models\User;
use App\Models\VirtualAccount;
use Illuminate\Support\Str;

class VirtualAccountService
{
    protected $paystackService;

    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    public function createVirtualAccount(User $customer, string $preferredBank = 'wema-bank'): VirtualAccount
    {
        // Generate a unique reference
        $reference = 'VA_' . Str::upper(Str::random(8)) . '_' . $customer->id;

        try {
            // Split customer name into first and last names
            $nameParts = explode(' ', $customer->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Customer';

            // Create customer in Paystack if they don't have a paystack_code
            if (empty($customer->paystack_code)) {
                $paystackCustomer = $this->paystackService->createCustomer([
                    'email' => $customer->email,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $customer->phone
                ]);

                // Update customer with paystack code
                $customer->paystack_code = $paystackCustomer['data']['customer_code'];
                $customer->save();
            }

            // Create dedicated virtual account
            $virtualAccountData = $this->paystackService->createDedicatedVirtualAccount([
                'customer_code' => $customer->paystack_code,
                'preferred_bank' => $preferredBank,
            ]);

            $accountData = $virtualAccountData['data'];

            // Create and save virtual account in database
            $virtualAccount = new VirtualAccount([
                'customer_id' => $customer->id,
                'account_number' => $accountData['account_number'],
                'bank_code' => $accountData['bank']['code'],
                'bank_name' => $accountData['bank']['name'],
                'reference' => $reference,
                'status' => 'active',
                'account_name' => $accountData['account_name'],
                'provider_reference' => $accountData['dedicated_account_id'] ?? null,
            ]);

            $virtualAccount->save();
            return $virtualAccount;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create virtual account: ' . $e->getMessage());
        }
    }
}
