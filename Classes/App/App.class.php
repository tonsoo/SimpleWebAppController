<?php

namespace App;

use App\Controllers\Route;

class App {

    const VIEWS_PATH = 'Classes'.DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR;

    private string $Root;
    private string $Host;
    private string $Title;
    private array $Routes;

    public function __construct(string $title = 'New App') {

        $this->Root = App::DocumentRoot();
        $this->Host = $_SERVER['HTTP_HOST'];

        $this->Title = $title;
        $this->Routes = [];
    }

    public static function DocumentRoot(bool $unixPath = true) : string {

        return $unixPath ? strtr($_SERVER['DOCUMENT_ROOT'], '\\', '/') : $_SERVER['DOCUMENT_ROOT'];
    }

    public static function ViewsPath(bool $unixPath = true) : string {

        return App::DocumentRoot($unixPath).DIRECTORY_SEPARATOR.App::VIEWS_PATH;
    }

    public function Bind(string $path, string $filePath, callable $callback = null) : Route {

        $path = $this->RemoveExtraSlash($path);
        $route = new Route($path, $filePath);
        $this->Routes[$path] = $route;

        return $route;
    }

    public function Mount() : void {

        $fileController = pathinfo($_SERVER['SCRIPT_FILENAME']);
        $fileControllerDirectory = str_replace($this->Root, '', strtr($fileController['dirname'], '\\', '/'));

        $request = $_SERVER['REQUEST_URI'];
        $realRequestUri = str_replace($fileControllerDirectory, '', $request);
        $realRequestUri = $this->RemoveExtraSlash($realRequestUri);

        if(!isset($this->Routes[$realRequestUri])){
            return;
        }

        $currentRoute = $this->Routes[$realRequestUri];
        $currentRoute->Render($this);
    }

    private function RemoveExtraSlash(string $text) : string {

        $len = strlen($text);
        $start = 0;
        $end = $len;

        if(in_array($text[$len - 1], ['/', '\\']) && $len > 1){
            $end = $len - (1 + $start);
        }

        return substr($text, $start, $end);
    }
}