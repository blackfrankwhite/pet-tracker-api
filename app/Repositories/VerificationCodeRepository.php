<?php

namespace App\Repositories;

use App\Models\VerificationCode;
use App\Services\SMSService;
use Illuminate\Support\Facades\Hash;

class VerificationCodeRepository
{
    protected $smsService;

    public function __construct(SMSService $smsService)
    {
        $this->smsService = $smsService;
    }

    public function createCode($userIdentifier)
    {
        $verificationCode = rand(100000, 999999);
        $codeHash = Hash::make($verificationCode);

        VerificationCode::create([
            'user_identifier' => $userIdentifier,
            'code_hash' => $codeHash,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            $sent = $this->smsService->sendSMS($userIdentifier, "Your verification code is: {$verificationCode}");
        } catch (\Exception $e) {
            dd("SMS Sending Failed: " . $e->getMessage(), [
                'userIdentifier' => $userIdentifier,
                'exception' => $e,
            ]);
        }

        return $verificationCode;
    }

    public function verifyCode($userIdentifier, $code)
    {
        $record = VerificationCode::where('user_identifier', $userIdentifier)
                                  ->where('expires_at', '>', now())
                                  ->orderBy('id', 'DESC')
                                  ->first();

        if (!$record) {
            return false;
        }

        if (Hash::check($code, $record->code_hash)) {
            $record->delete();
            return true;
        }

        return false;
    }
}
