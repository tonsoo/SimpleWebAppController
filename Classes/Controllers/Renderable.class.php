<?php

namespace App\Controllers;

use App\Exceptions;
use App\Utils;

abstract class Renderable {

    protected string $FilePath;
    protected $RenderCallback;
    protected array $Args;

    public function __construct(string $filePath, callable $callback = null) {

        $this->FilePath = $filePath;

        if(!is_null($callback) && is_callable($callback)){
            $this->RenderCallback = $callback;
        }
    }

    protected abstract function GetPath(\App\App $app) : string;

    public function Render(\App\App $app, ...$args) : void {

        $data = [];
        $this->Args = $args;

        $callback = $this->RenderCallback;
        if(!is_null($callback) && is_callable($callback)){
            $data = call_user_func($callback, $args);
        }

        $fullPath = Utils\Url::ToOS($this->GetPath($app));
        $this->IncludeFile($fullPath, $data);
    }

    protected function IncludeFile(string $fullPath, $data) : void {

        if(!file_exists($fullPath)){
            throw new Exceptions\RenderableNotFound("The route {$this->FilePath} does not exist.");
        }

        foreach($this->Args as $__name__ => $__value__){
            $$__name__ = $__value__;

            unset($__name__);
            unset($__value__);
        }

        require $fullPath;
    }
}