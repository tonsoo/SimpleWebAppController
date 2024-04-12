<?php

namespace App\Controllers;

class Reusable extends Renderable {

    public const POSITION_BEFORE_CONTENT = 0;
    public const POSITION_AMONG_CONTENT = 1;
    public const POSITION_AFTER_CONTENT = 2;

    private int $Position;
    private string $Alias;
    private string $Path;

    public function __construct(string $alias, string $filePath, int $contentPosition = Reusable::POSITION_AMONG_CONTENT, callable $callback = null) {

        switch($contentPosition){
            case Reusable::POSITION_BEFORE_CONTENT:
                
                break;
            case Reusable::POSITION_AMONG_CONTENT:
            
                break;
            case Reusable::POSITION_AFTER_CONTENT:
            
                break;
            default:
                # TODO
                # Throw an error
        }

        parent::__construct($filePath, $callback);

        $this->Position = $contentPosition;
        $this->Alias = $alias;
    }

    public function __get(string $request) : mixed {

        if(!isset($this->$request)){
            return null;
        }

        return $this->$request;
    }

    protected function GetPath() : string {

        $fullPath = \App\App::GetPath(\App\App::PATH_TYPE_REUSABLES, false)."/{$this->FilePath}.reusable.phtml";
        return $fullPath;
    }
}