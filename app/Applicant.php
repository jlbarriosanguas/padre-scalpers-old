<?php
// 
namespace App;

use Illuminate\Database\Eloquent\Model;

class Applicant extends \Eloquent
{
     protected $fillable = [
        'id',
        'job_id',
        'saved',
        'name',
        'surname',
        'birthday',
        'phone',
        'email',
        'studies',
        'english_level',
        'retail_exp',
        'location',
        'job',
        'last_exp',
        'country',
        'city',
        'time_availability',
        'travel_availability',
        'curriculum',
        'photo',
        'observations',
        'motivation_letter',
        'created_at',
    ];

    protected $dates = ['birthday'];

    public function jobOffers()
    {
        return $this->belongsTo('App\JobOffers');
    }
}