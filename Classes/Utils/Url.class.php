<?php

namespace App\Utils;

class Url {

    public const Unix = 0;
    public const OS = 1;

    public static function ToUnix(string $url) : string {

        return str_replace('\\', '/', $url);
    }

    public static function ToOS(string $url) : string {

        $urlPieces = explode('/', Url::ToUnix($url));
        return implode(DIRECTORY_SEPARATOR, $urlPieces);
    }

    public static function Diff(string $needle, string $search, int $returnType = Url::Unix, bool $caseSensitive = true) : string {

        $needle = Url::InnerPath($needle, $returnType);
        $search = Url::InnerPath($search, $returnType);

        $regexConf = '';
        if($caseSensitive === true){
            $regexConf .= 'i';
        }

        $needle = str_replace('/', "\/", $needle);
        return Url::InnerPath(preg_replace("/^{$needle}/{$regexConf}", '', $search), $returnType);
    }

    public static function InnerPath(string $url, int $returnType = Url::Unix) : string {

        $url = Url::ToUnix($url);
        $len = strlen($url);

        if(substr($url, $len - 1) === '/'){
            $len -= 1;
            $url = substr($url, 0, $len);
        }

        if(substr($url, 0, 1) === '/'){
            $url = substr($url, 1);
        }

        switch($returnType){
            case Url::Unix:
                # Do nothing, url is a Unix by default
                break;
            case Url::OS:
                $url = Url::ToOS($url);
                break;
            default:
                # TODO
                # Error
        }

        return $url;
    }

    public static function ToArray(string $url) : array {

        $url = Url::InnerPath($url);
        return explode('/', $url);
    }

    public static function ToString(array $urlPieces, int $returnType = Url::Unix) : string {

        $url = '';
        switch($returnType){
            case Url::Unix:
                $url = implode('/', $urlPieces);
                break;
            case Url::OS:
                $url = implode(DIRECTORY_SEPARATOR, $urlPieces);
                break;
            default:
                # TODO
                # Error
        }

        return $url;
    }
}
