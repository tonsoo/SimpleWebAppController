<?php

namespace App\Controllers;

class Route extends Renderable {

    private string $UrlPath;

    public function __construct(string $urlPath, string $filePath, callable $callback = null) {

        parent::__construct($filePath, $callback);
        $this->UrlPath = $urlPath;
    }

    protected function GetPath(\App\App $app) : string {

        $fullPath = $app->ViewsPath(false)."/{$this->FilePath}.view.phtml";
        return $fullPath;
    }
}
