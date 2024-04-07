<?php

namespace App\Utils;

class Files {

    public function FileExists(string $path, bool $createRecursive = false) : bool {

        if($createRecursive){
            $pathPieces = explode('/', Url::ToUnix($path));
            $currentPath = '';

            for($i = 0; $i < count($pathPieces); $i++){
                $currentPath = "{$currentPath}/{$piece}";
                if(!file_exists($currentPath)){
                    mkdir($currentPath);
                }
            }
        }

        return file_exists($path);
    }
}