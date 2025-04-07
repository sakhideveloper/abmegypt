<?php

namespace App\Modules\Authentication\Enums;

enum RolesEnum: string
{

  case ADMIN = 'admin';
  case DEALER = 'dealer';
  case CLIENT = 'client';
}