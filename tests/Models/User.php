<?php

namespace Daydevelops\Vote\Tests\Models;

use Daydevelops\Vote\Traits\CanVote;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use CanVote;

    protected $table = "dd_user";
}
