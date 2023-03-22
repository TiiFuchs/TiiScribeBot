<?php

namespace App\Support;

enum UserSharedRequest: int
{

    case GRANT_ACCESS = 1;

    case REVOKE_ACCESS = 2;

}
