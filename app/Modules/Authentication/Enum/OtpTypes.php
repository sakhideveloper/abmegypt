<?php

namespace App\Modules\Authentication\Enums;

enum OtpTypes: string
{

  case EMAIL_VERIFICATION = 'email-verification';  
  case PASSWORD_RESET = 'password-reset';
}