<?php

namespace Daydevelops\Vote\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Daydevelops\Vote\Traits\Votable;

class Comment extends Model
{
    use Votable;

    protected $table = "dd_comment";

    protected $fillable = ['user_id'];
}
