<?php

namespace App\Modules\Authentication\Services;


use App\Models\OtpCode;

use App\Models\User;
use App\Modules\Authentication\Enums\OtpTypes;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Notifications\PasswordResetConfirmed;
use App\Modules\Authentication\Notifications\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AuthenticationService
{

    public function __construct(
        public readonly OtpService $otpService,
    ) {}

    public function createUser(array $data): User
    {
        return User::create($data);
    }


    public function login(array $data)
    {

        /** @var User $user */
        $user = User::where('email', $data['email'])->first();

        if ($user == null || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'message' => 'The provided credentials are incorrect',
            ]);
        }

        $token = $this->generateAuthToken($user);

        return [
            'user' => new UserResource($user),
            'token'  => $token,
        ];
    }

    public function generateAuthToken(User $user): string
    {
        
        $token = $user->createToken("$user->first_name token")->plainTextToken;
        return $token;
    }



    public function verifyEmailAddress(int $code, User $user): User|bool|array
    {
        $response = $this->otpService->verifyOtp($code, OtpTypes::EMAIL_VERIFICATION, $user->id);

        if (!is_array($response) && $response) {
            User::query()
                ->where('id', $user->id)
                ->update([
                    'email_verified_at' => now()
                ]);

            return $user->fresh();
        } else {
            return [
                'status' => false,
                'message' => $response['message']
            ];
        }
    }

    public function otpRequest(string $email, string $otpType)
    {
        try {

            /** @var User $user */
            $user = User::where('email', $email)->first();
            if ($user == null) return "You will be notified shortly";
            $code = $this->otpService->generateOtpEmail($email, $otpType);
            $user->notify(new ResetPassword($code, $email, $user->first_name));
            // $user->notify(new OtpInitiate($code, $email));
            return "OTP has been sent to your email";
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }

    public function passwordReset(string $email)
    {
        try {

            /** @var User $user */
            $user = User::where('email', $email)->first();
            if ($user == null) return "passwords.user";
            $code = $this->otpService->generateOtpEmail($email, OtpTypes::PASSWORD_RESET->value);
            $user->notify(new ResetPassword($code, $email, $user->first_name));
            return "passwords.sent";
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }

    public function updatePasswordReset($request): string|null
    {
        try {

            /** @var User $user */
            $user = User::where('email', $request->email)->first();
            $user->forceFill(['password' => Hash::make($request->password)])->setRememberToken(Str::random(60));
            $user->save();
            $user->notify(new PasswordResetConfirmed());
            OtpCode::query()->where('email', $request->email)->delete();
            return "passwords.reset";
        } catch (Throwable $e) {
            logger($e);
            return null;
        }
    }


    public function logout(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $user->tokens()->delete();
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
    }

    public function updateProfile(User $user, array $data): User
    {

        return $user;
    }


    public function getLogginUser()
    {
        $user = Auth::user();

        return new  UserResource($user);
    }

    public function setPasscode($user,$passcode)
    {
        User::find($user->id)->update(['passcode'=>Hash::make($passcode)]);
    
    }
}