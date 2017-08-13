<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

class VerifyCsrfToken extends BaseVerifier
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'Stanford',
        'WatsonNLU',
        'Ambiverse',
        'OpenCalais',
        'Dandelion',
        'GCloud',
        'Haven',
        'TextRazor',
        'FindICD10Codes',
        'FindSNOWMEDCodes',
        'FindATCCodes',
        'UMLS',
        'insert_extraction',
        'insert_annotations_extraction',
        //
    ];
}
