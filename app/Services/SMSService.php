<?php

namespace App\Services;

use Twilio\Rest\Client;

class SMSService
{
    protected $client;
    protected $fromNumber;

    public function __construct()
    {
        $this->client = new Client(env('TWILIO_SID'), env('TWILIO_TOKEN'));
        $this->fromNumber = env('TWILIO_FROM');
    }

    public function sendSMS($to, $message)
    {
        try {
            $this->client->messages->create($to, [
                'from' => $this->fromNumber,
                'body' => $message,
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
