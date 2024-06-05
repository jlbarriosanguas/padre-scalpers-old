<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TestabUser extends \Eloquent
{
     protected $fillable = [
        'id',
        'email',
        'tag',
    ];
}