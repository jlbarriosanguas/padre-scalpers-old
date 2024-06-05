<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class JobOffer extends Model
{
     protected $fillable = [
        'id',
        'offer_status',
        'city',
        'title',
        'department',
        'body',
        'image',
        'applicants',
        'observations',
        'created_at',
        'updated_at'
    ];

    public function getApplicants()
    {
        return $this->hasMany('App\Applicant', 'job_id');
    }
}
