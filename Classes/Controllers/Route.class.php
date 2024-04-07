<?php

namespace App\Controllers;

use App\Exceptions\RouteNotFound;

class Route {

    private \App\App $App;
    private string $UrlPath;
    private string $FilePath;
    private $RenderCallback;

    public function __construct(string $urlPath, string $filePath, callable $callback = null) {

        $this->FilePath = $filePath;
        $this->UrlPath = $urlPath;

        if(!is_null($callback) && is_callable($callback)){
            $this->RenderCallback = $callback;
        }
    }

    public function Render(\App\App $app) : void {

        $this->App = $app;
        $data = [];
        $callback = $this->RenderCallback;
        if(!is_null($callback) && is_callable($callback)){
            $data = call_user_func($callback);
        }

        $this->IncludeFile($data);
    }

    protected function IncludeFile($data) : void {

        $fullPath = $this->App->ViewsPath(false)."/{$this->FilePath}.view.phtml";
        if(!file_exists($fullPath)){
            throw new RouteNotFound("The route {$this->FilePath} does not exist.");
        }

        require $fullPath;
    }
}
