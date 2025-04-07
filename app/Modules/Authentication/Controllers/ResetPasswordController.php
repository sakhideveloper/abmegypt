<?php

namespace App\Modules\Authentication\Controllers;

use App\Modules\Authentication\Events\ResetPassword;
use App\Http\Controllers\ApiController;
use App\Modules\Authentication\Enums\OtpTypes;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Requests\ResetPasswordRequest;
use App\Traits\TokenTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\{
    Facades\Hash,
    Facades\Password,
    Str
};

class ResetPasswordController extends ApiController
{
    use TokenTrait;

    public function __construct(
        private readonly AuthenticationService $userService
    ) {}

    
    /**
     * Reset password
     * 
     * @param ResetPasswordRequest $request
     * 
     * @response array{status: 'passwords.reset'}
     */
    public function resetPassword(ResetPasswordRequest $request)
    {

        try {

            $status = $this->userService->updatePasswordReset($request);

            return $status === Password::PASSWORD_RESET
                ? $this->sendResetResponse($request, $status)
                : $this->sendResetFailedResponse($request, $status);
        } catch (\Throwable $th) {
            $message = $th->getMessage();
            return $this->error(
                $message,
                Response::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Get the response for a successful password reset.
     *
     * @response array{status: 'passwords.reset'}
     */

    protected function sendResetResponse(Request $request, $response)
    {
        return $this->success(
            trans($response),
            Response::HTTP_OK
        );
    }

    /**
     * Get the response for a failed password reset.
     *
     * @param Request $request
     * @param string $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {

        return $this->error(
            trans($response),
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * Verify Forgot Password  OTP
     * 
     * @param Request $request
     * 
     * @response array{status: 'OTP verified successfully'}
     */

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'reference' => 'required|string',
            'email' => 'required|email'
        ]);
        $this->validateTokenReference($request->reference, OtpTypes::PASSWORD_RESET, $request->email);

        return $this->success(
            "OTP verified successfully",
            Response::HTTP_OK
        );
    }
}