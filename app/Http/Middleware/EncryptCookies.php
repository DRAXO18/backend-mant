<?php

// Source - https://stackoverflow.com/a
// Posted by Mike Rockétt
// Retrieved 2025-12-08, License - CC BY-SA 3.0

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        'token'
    ];
}

