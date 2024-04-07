<?php

namespace App\Controllers;

use App\Exceptions\RouteNotFound;
use App\Utils;

class Route {

    private \App\App $App;
    private array $Args;
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

    public function Render(\App\App $app, ...$args) : void {

        $this->App = $app;
        $this->Args = $args;

        $data = [];
        $callback = $this->RenderCallback;
        if(!is_null($callback) && is_callable($callback)){
            $data = call_user_func($callback, $args);
        }

        $this->IncludeFile($data);
    }

    protected function IncludeFile($data) : void {

        $fullPath = Utils\Url::ToOS($this->App->ViewsPath(false)."/{$this->FilePath}.view.phtml");
        if(!file_exists($fullPath)){
            throw new RouteNotFound("The route {$this->FilePath} does not exist.");
        }

        foreach($this->Args as $__name__ => $__value__){
            $$__name__ = $__value__;

            unset($__name__);
            unset($__value__);
        }

        require $fullPath;
    }
}
