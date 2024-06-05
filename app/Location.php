<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends \Eloquent
{
     protected $fillable = [
        'code',
        'name',
        'address1',
        'address2',
        'longitude',
        'latitude',
        'city',
        'zip',
        'province',
        'province_code',
        'country',
        'country_code',
        'country_name',
        'phone',
        'legacy',
        'active',
    ];
}