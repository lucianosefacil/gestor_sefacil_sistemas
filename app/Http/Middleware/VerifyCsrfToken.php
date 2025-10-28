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
        'tef/processar',
        'tef/processar-mock',
        'tef/iniciar',
        'tef/confirmar',
        'tef/cancelar',
        'tef/status',
        'tef/adm',
        'tef/imprimir',
        'tef/desfazer',
        'tef/test-post',
    ];
}
