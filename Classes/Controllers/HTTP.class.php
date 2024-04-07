<?php

namespace App\Utils;

use App\App;

class HTTP {

    public static function ValidHTTPReponseCode(int $code) : bool {

        $codeIsValid = preg_match('/^[1-5][0-9]{2}$/', $code);
        if(!$codeIsValid){
            trigger_error("Error code '{$code}' is invalid.", E_USER_WARNING);
        }
        return $codeIsValid;
    }

    public static function DefineHTTPCode(int $code, string $requestUrl = '', callable $after = null, mixed ...$params) : void {

        if(!HTTP::ValidHTTPReponseCode($code)){
            if($code === 500){
                # Avoid infinite redirects
                http_response_code(508);
                die('Fatal error');
            }
            $this->DefineHTTPCode(500);
            return;
        }

        http_response_code($code);

        if(!is_null($after) && is_callable($after)){
            call_user_func($after);
        }

        die();
    }

    public static function Redirect(string $url, int $code = null) : void {

        $url = Url::InnerPath($url);
        if(!is_null($code)){
            http_response_code($code);
        }
        $root = App::Root();
        $redirectPath = "/{$root}/{$url}";
        header("Location: {$redirectPath}");
        die();
    }
}