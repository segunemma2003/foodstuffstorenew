<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SamuelMwangiW\Africastalking\Facades\Africastalking;

class JusibeService
{
    protected $baseUrl;
    protected $publicKey;
    protected $accessToken;

    public function __construct()
    {
        $this->baseUrl = 'https://jusibe.com/smsapi/';
        $this->publicKey = env('JUSIBE_PUBLIC_KEY', 'f501426d5edec1f6f68188eaf50bb80c');
        $this->accessToken = env('JUSIBE_ACCESS_TOKEN', "abdb3cf7da7fa70fc4ab00a2d61b503c");

    }

    public function sendWhatsapp($to, $from, $message){
        $data = array("api_key" => env('TERMII_API_KEY'), "to" => $to,  "from" => $from,
        "sms" => $message,  "type" => "plain",  "channel" => "whatsapp" );
        try {
            $curl = curl_init();


            $post_data = json_encode($data);

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://v3.api.termii.com/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);
            Log::info('Failed to send SMS: ' . $response);
            return $response;



        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage());
            return null;
        }
    }

    public function sendNormal($to, $from, $message){
        $data = array("api_key" => env('TERMII_API_KEY'), "to" => $to,  "from" => "FSSTORE",
        "sms" => $message,  "type" => "plain",  "channel" => "dnd" );
        try {
            $curl = curl_init();


            $post_data = json_encode($data);

            curl_setopt_array($curl, array(
            CURLOPT_URL => "https://v3.api.termii.com/api/sms/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => array(
            "Content-Type: application/json"
            ),
            ));

            $response = curl_exec($curl);

            curl_close($curl);

            Log::info('Failed to send SMS: ' . $response);
            return $response;

        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage());
            return null;
        }
    }

    public function sendSingleSMSTermii($to, $from, $message)
    {

        try {
            // $whatsapp = $this->sendWhatsapp($to, $from, $message);
            $generic = $this->sendNormal($to, $from, $message);
            return [
                // "whatsapp"=> $whatsapp,
                "generic"=> $generic
            ];

        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage());
            return null;
        }
    }
    public function sendSingleSMSAfrica($to, $from, $message)
    {

        try {
            $response = Africastalking::sms($message)
                    ->to($to)
                    ->send();

            Log::info($response);
            return $response;
            // // Or using the global helper function
            // $response = africastalking()->sms("Hello Mom")
            //         ->to('+254712345678')
            //         ->send();



        } catch (\Exception $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage());
            return null;
        }
    }

    public function sendSingleSMS($to, $from, $message)
{
    // Debugging: Log the values of publicKey and accessToken
    Log::info('Public Key: ' . $this->publicKey);
    Log::info('Access Token: ' . $this->accessToken);

    // Check if credentials are null
    if (is_null($this->publicKey) || is_null($this->accessToken)) {
        Log::error('Basic Auth credentials are missing.');
        return null;
    }

    try {
        $response = Http::withBasicAuth($this->publicKey, $this->accessToken)
            ->post($this->baseUrl . 'send_sms', [
                'to' => $to,
                'from' => $from,
                'message' => $message,
            ]);

        return $response->json();
    } catch (\Exception $e) {
        Log::error('Failed to send SMS: ' . $e->getMessage());
        return null;
    }
}

    public function sendBulkSMS($to, $from, $message)
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->accessToken)
                ->post($this->baseUrl . 'bulk/send_sms', [
                    'to' => $to,
                    'from' => $from,
                    'message' => $message,
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to send Bulk SMS: ' . $e->getMessage());
            return null;
        }
    }

    public function checkSingleMessageStatus($messageId)
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->accessToken)
                ->post($this->baseUrl . 'delivery_status', [
                    'message_id' => $messageId,
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to check SMS status: ' . $e->getMessage());
            return null;
        }
    }

    public function checkBulkMessageStatus($bulkMessageId)
    {
        try {
            $response = Http::withBasicAuth($this->publicKey, $this->accessToken)
                ->post($this->baseUrl . 'bulk/status', [
                    'bulk_message_id' => $bulkMessageId,
                ]);

            return $response->json();
        } catch (\Exception $e) {
            Log::error('Failed to check Bulk SMS status: ' . $e->getMessage());
            return null;
        }
    }
}
