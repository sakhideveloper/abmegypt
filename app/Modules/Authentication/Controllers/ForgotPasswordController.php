<?php

namespace App\Modules\Authentication\Controllers;

use App\Http\Controllers\ApiController;
use App\Modules\Authentication\Services\AuthenticationService;
use App\Modules\Authentication\Requests\ForgotPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends ApiController
{
    public function __construct(private readonly AuthenticationService $userService) {}
    /**
     * Send password reset link
     * @param ForgotPasswordRequest $request
     * 
     * @response array{message: 'OTP has been sent to your email'}
     */

    public function sendResetLinkEmail(ForgotPasswordRequest $request)
    {

        $createPasswordToken = $this->userService->passwordReset($request->email);
       
        return $createPasswordToken === Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $createPasswordToken)
            : $this->sendResetLinkFailedResponse($createPasswordToken);
    }
    /**
     * Get the response for a successful password reset link.
     *
     * @response array{message: 'OTP has been sent to your email'}
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return $this->success(
            "OTP has been sent to your email",
            Response::HTTP_OK
        );
    }

    /**
     * Get the response for a failed password reset link.
     * @error 400 { "status": "error", "message": "We can't find a user with that e-mail address." }
     */
    protected function sendResetLinkFailedResponse($response)
    {
        return $this->error(
            trans($response),
            Response::HTTP_BAD_REQUEST
        );
    }
}