<?php
namespace App\Channels;

use Illuminate\Notifications\Notification;
use App\Notifications\SendSmsNotification;
use App\Services\JusibeService;

class JusibeSmsChannel
{
    protected $jusibeService;

    public function __construct(JusibeService $jusibeService)
    {
        $this->jusibeService = $jusibeService;
    }

    public function send($notifiable, SendSmsNotification $notification)
    {
        $data = $notification->toJusibeSms($notifiable);

        return $this->jusibeService->sendSingleSMS($data['to'], $data['from'], $data['message']);
    }
}
