<?php

namespace Daydevelops\Vote\Models;

use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{

    protected $table = "dd_votes";

    protected $fillable = ['user_id', 'voted_id', 'voted_type', 'votable_user_id', 'value'];
}
