<?php

namespace App\Modules\Authentication\Controllers;


use App\Http\Controllers\ApiController;
use App\Modules\Authentication\Requests\LoginRequest;
use App\Modules\Authentication\Services\AuthenticationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends ApiController
{

    public function __construct(public AuthenticationService $authenticationService) {}

    /**
     * Login a user.
     * 
     * @param LoginRequest $request
     * 
     * @response array{message: string, data: array{token: string, user: UserResource}}
     */
    public function store(LoginRequest $request)
    {
        
        $response = $this->authenticationService->login($request->validated());

        return $this->success($response, Response::HTTP_OK);
    }

    /**
     * Logout User  
     * @param Request $request
     * 
     * @response array{message: string}
     */
    public function destroy(Request $request)
    {
        $this->authenticationService->logout($request);

        return $this->Ok(
            message: 'User logged out successfully',
            status: Response::HTTP_OK
        );
    }

    /**
     * Set User Language 
     * 
     * @param Request $request
     * 
     * @response array{success: boolean}
     */

    public function setLanguage(Request $request)
    {
        $request->validate([
            'languageName' => 'required',
        ]);


        session()->forget('languageName');
        Session::put('languageName', $request->get('languageName'));
        App::setLocale(session('languageName'));

        return $this->success(true, Response::HTTP_OK);
    }


    /**
     * Fetch User Profile 
     * 
     * @response array{success: boolean, data: UserResource}
     */

    public function profile()
    {
        $response = $this->authenticationService->getLogginUser();

        return $this->success($response, Response::HTTP_OK);
    }
}