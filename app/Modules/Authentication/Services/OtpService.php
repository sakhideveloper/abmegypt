<?php

namespace App\Modules\Authentication\Services;

use App\Models\OtpCode;
use App\Modules\Authentication\Enums\OtpTypes;
use Illuminate\Support\Facades\Hash;
use Throwable;

class OtpService
{

    public function generateOtp(string $userId, OtpTypes $type): ?int
    {

        OtpCode::query()
            ->where('user_id', $userId)
            ->where('type', $type->value)
            ->delete();

        do {
            $code = random_int(100000, 999999);
        } while (OtpCode::query()
            ->where('code', Hash::make((string) $code))
            ->where('type', $type->value)
            ->where('user_id', $userId)
            ->exists()
        );

        try {
            OtpCode::query()
                ->create([
                    'user_id' => $userId,
                    'code' => Hash::make((string) $code),
                    'type' => $type->value,
                    'expire_at' => now()->addMinutes(config('app.otp_duration'))->getTimestamp(),
                ]);

            return $code;
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }


    public function generateOtpEmail(string $email, string $type): ?int
    {

        OtpCode::query()
            ->where('email', $email)
            ->where('type', $type)
            ->delete();

        do {
            $code = random_int(100000, 999999);
        } while (OtpCode::query()
            ->where('code', Hash::make((string) $code))
            ->where('type', $type)
            ->where('email', $email)
            ->exists()
        );

        try {
            OtpCode::query()
                ->create([
                    'email' => $email,
                    'code' => Hash::make((string) $code),
                    'type' => $type,
                    'reference' => $code,
                    'expire_at' => now()->addMinutes(config('app.otp_duration'))->getTimestamp(),
                ]);

            return $code;
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }
    public function verifyOtp(int $code, OtpTypes $type, string $userId): bool|array
    {
        $otpCode = OtpCode::query()
            ->where('type', $type->value)
            ->where('user_id', $userId)
            ->where('expire_at', '>', now()->getTimestamp())
            ->latest()
            ->first();


        if ($otpCode) {
            $isValid = Hash::check((string) $code, $otpCode->code);
            if ($isValid) {
                $otpCode->update(['verified_at' => now()]);
                return true;
            } else {
                return [
                    'status' => false,
                    'message' => "Invalid Token Supplied"
                ];
            }
        } else {
            return [
                'status' => false,
                'message' => "Token Has Expired"
            ];

        }
    }
    public function verifyOtpProcess($request): string
    {
        /** @var OtpCode $otpCode */
        $otpCode = OtpCode::query()
            ->where('type', $request->otpType)
            ->where('email', $request->email)
            ->latest()
            ->first();



        if ($otpCode) { //@phpstan-ignore-line
            if ($otpCode->reference) throw new \ErrorException("Token already used, you will need to regenerate token");
            if ($otpCode->expire_at < now()->getTimestamp()) throw new \ErrorException("Token Has Expired");

            $isValid = Hash::check((string) $request->code, $otpCode->code);
            if ($isValid) {
                $reference = uniqid();
                $otpCode->update(['verified_at' => now(), 'reference' => $reference]);
                return $reference;
            } else {
                throw new \ErrorException("Invalid Token Supplied");
            }
        } else {
            throw new \ErrorException("Invalid Token Supplied");
        }
    }
}