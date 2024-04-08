<?php

namespace App\Controllers;

class Route extends Renderable {

    private string $UrlPath;

    public function __construct(string $urlPath, string $filePath, callable $callback = null) {

        parent::__construct($filePath, $callback);
        $this->UrlPath = $urlPath;
    }

    protected function GetPath(\App\App $app) : string {

        $fullPath = $app->GetPath(\App\App::PATH_TYPE_VIEWS, false)."/{$this->FilePath}.view.phtml";
        return $fullPath;
    }
}
