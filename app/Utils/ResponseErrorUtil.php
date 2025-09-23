<?php

namespace App\Utils;

use Exception;
use Illuminate\Support\Facades\Log;

class ResponseErrorUtil
{

    public static function response(Exception $error): array
    {
        Log::error($error->getMessage() . ' Line: ' . $error->getLine() . " File: " . $error->getFile());

        if (getenv('APP_DEBUG') === false) {
            return ['sucess' => false,'success' => false, 'message' => 'Ocorreu um erro'];
        }
        return ['success' => false,'sucess' => false, 'message' => $error->getMessage() ];
    }
}
