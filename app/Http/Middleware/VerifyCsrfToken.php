<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'wheel/',
		'shopify-webhook/*',
        'sfy-user/*',
        'rrhh/create-offer',
        'rrhh/register-applicant',
        'graphqltest/*',
        'locations/store',
        'draftorder/create',
        'lty-ce/*',
        'geocode/*'
    ];
}
