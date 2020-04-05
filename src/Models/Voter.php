<?php

namespace Daydevelops\Vote\Models;

use Illuminate\Database\Eloquent\Model;

class Voter extends Model
{

    protected $table = "dd_voter";

    protected $fillable = ['user_id', 'weight'];
}
