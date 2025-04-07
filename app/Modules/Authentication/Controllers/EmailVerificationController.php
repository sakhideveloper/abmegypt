<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Authentication\Enums\OtpTypes;
use Illuminate\Http\Request;
use App\Models\User;
use App\Modules\Authentication\Notifications\VerificationEmailNotification;
use App\Modules\Authentication\Resources\UserResource;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Services\OtpService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EmailVerificationController extends ApiController
{
     public function __construct(
        public readonly OtpService $otpService,
        public readonly AuthenticationService $userService
    ) {
    }

    /**
     * Verify user email address
     * @param Request $request
     *
     *
     * @response array{status: 'success', data: UserResource}
     */
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|numeric'
        ]);

        $verifiedUser = $this->userService->verifyEmailAddress(
            $validated['code'],
            $request->user()
        );

        if ($verifiedUser instanceof User) {
            return $this->success(
                new UserResource($verifiedUser),
                Response::HTTP_OK
            );
        } else {
            return $this->error($verifiedUser['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    /**
     * Resend email verification code
     *
     * @response array{message: 'Verification code has been successfully sent to your email address'}
     */
    public function resendOtp(Request $request)
    {
        /** @var User user */
        $user = Auth::user();

        if ($user->email_verified_at !== null) {
            return $this->error('Your email address is already verified, proceed to login', Response::HTTP_FORBIDDEN);
        }

        if ($code = $this->otpService->generateOtp(
            $user->id,
            OtpTypes::EMAIL_VERIFICATION
        )) {
            $user->notify(new VerificationEmailNotification($code));

            return $this->success([
                'message' => 'Verification code has been successfully sent to your email address'
            ], Response::HTTP_OK);
        } else {
            return $this->error('Something went wrong, please try again later', Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}