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

    protected abstract function GetPath() : string;

    public function Render(...$args) : void {

        $data = [];
        $this->Args = $args;

        $callback = $this->RenderCallback;
        if(!is_null($callback) && is_callable($callback)){
            $data = call_user_func($callback, $args);
        }

        $fullPath = '';
        if(\App\App::GetConfig('allow_custom_renderable_path') && substr($this->FilePath, 0, 2) === './'){
            $filePath = substr($this->FilePath, 1);
            $fullPath = \App\App::DocumentRoot().$filePath;
        } else if(substr($this->FilePath, 0, 2) === './'){
            # TODO
            # Throw an error
            return;
        } else {
            $fullPath = Utils\Url::ToOS($this->GetPath());
        }

        $this->IncludeFile($fullPath, $data);
    }

    protected function IncludeFile(string $fullPath, $data) : void {

        if(!file_exists($fullPath)){
            if(\App\App::GetConfig('auto_create_renderables')){
                file_put_contents($fullPath, "<h1>{$fullPath}</h1><h1>This renderable has been created automatically!</h1><h2>Deactivate renderable creation in your \"app.ini\" file.</h2");
            } else {
                throw new Exceptions\RenderableNotFound("The route {$this->FilePath} does not exist.");
            }
        }

        foreach($this->Args as $__name__ => $__value__){
            $$__name__ = $__value__;

            unset($__name__);
            unset($__value__);
        }

        require $fullPath;
    }
}