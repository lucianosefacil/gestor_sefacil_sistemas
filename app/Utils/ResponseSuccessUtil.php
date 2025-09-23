<?php

namespace App\Utils;

class ResponseSuccessUtil
{

    public static function response(string $msg = "")
    {
        if(!empty($msg)){
            $msg = $msg.' com sucesso.';
            return ['success' => true, 'message' => $msg, 'msg' => $msg];
        }
        $msg = 'Feito com sucesso';
        return ['success' => true, 'message' => $msg, 'msg' => $msg];
    }
}
