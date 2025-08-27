<?php
namespace App\Services;
use Unicodeveloper\Paystack\Facades\Paystack;


class PaymentService {
    public static function redirectToGatewayTwo($data)
    {
        try{
            $data = array(
                "amount" => $data['amount'],
                "reference" => $data['reference'],
                "email" => $data['email'],
                "currency" => "NGN",
                "orderID" => $data['orderID'],
                'metadata'=> $data['metadata']
            );

            return ["status"=>true, "data"=>Paystack::getAuthorizationUrl($data)];
        }catch(\Exception $e) {
            return ["status"=>false, "msg"=>$e->getMessage()];
            // return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }
    }



    public static function redirectToGateway($data)
    {
        try{
            $data = array(
                "amount" => floatval($data['amount'])*100,
                "reference" => $data['reference'],
                "email" => $data['email'],
                "currency" => "NGN",

                "orderID" => $data['orderID'],
                'metadata'=>[
                    "from"=>$data["from"]??"mobile",
                    "email" => $data['email'],
                    "order_type"=> $data['order_type'] ?? "",
                    "orderID" => $data['orderID'],
                    "reference" => $data['reference'],
                    "amount" => floatval($data['amount'])*100,
                ]
            );

            return ["status"=>true, "data"=>Paystack::getAuthorizationUrl($data)];
        }catch(\Exception $e) {
            return ["status"=>false, "msg"=>$e->getMessage()];
            // return Redirect::back()->withMessage(['msg'=>'The paystack token has expired. Please refresh the page and try again.', 'type'=>'error']);
        }
    }
}
