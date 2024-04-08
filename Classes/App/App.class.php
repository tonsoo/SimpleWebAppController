<?php

namespace App;

use App\Controllers\Route;
use App\Controllers\Reusable;
use App\Utils\Url;
use App\Utils\HTTP;
use App\Exceptions;

class App {
    public const PATH_TYPE_CLASSES = 100;
    public const PATH_TYPE_VIEWS = 101;
    public const PATH_TYPE_REUSABLES = 102;

    private string $ClassesPath;
    private string $ViewsPath;
    private string $ReusablesPath;

    private string $Root;
    private string $DocumentRoot;

    private array $Errors = [];
    private array $Routes = [];
    private array $Components = [];

    private array $AppSettings;
    private bool $AutoCreateRoutes;
    private bool $AutoCreateComponents;

    public function __construct(string $title = 'New App', bool $autoCreateRoutes = false, bool $autoCreateComponents = false) {

        $this->DocumentRoot = App::DocumentRoot();
        $this->Root = App::Root();

        $this->SetPath('Classes', App::PATH_TYPE_CLASSES);
        $this->SetPath('Classes/Views', App::PATH_TYPE_VIEWS);
        $this->SetPath('Classes/Components', App::PATH_TYPE_REUSABLES);

        $this->AutoCreateRoutes = $autoCreateRoutes;
        $this->AutoCreateComponents = $autoCreateComponents;

        $this->AppSettings = [
            'title' => $title,
            'document_root' => $this->DocumentRoot,
            'server_root' => $this->Root,
            'host' => $_SERVER['HTTP_HOST'],
            'auto_create_routes' => $this->AutoCreateRoutes
        ];
    }

    public function SetConfig(string $index, mixed $value) : void {

        $this->AppSettings[$index] = $value;
    }

    public function GetConfig(string $index) : mixed {

        if(!$this->AppSettings[$index]){
            return null;
        }

        return $this->AppSettings[$index];
    }


    # START CUSTOM PATHS #
    public function SetPath(string $path, int $pathType = App::PATH_TYPE_VIEWS) : void {

        $path = Url::InnerPath($path);
        switch($pathType){
            case App::PATH_TYPE_VIEWS:
                $this->ViewsPath = $path;
                break;
            case App::PATH_TYPE_REUSABLES:
                $this->ReusablesPath = $path;
                break;
            default:
                # TODO
                # Error/Warning
        }
    }
    # END CUSTOM PATHS #



    # START STATIC FUNCTIONS #
    public static function Root(bool $unixPath = true) : string {

        return Url::Diff(App::DocumentRoot($unixPath), pathinfo($_SERVER['SCRIPT_FILENAME'])['dirname'], $unixPath ? Url::Unix : Url::OS);
    }

    public function GetPath(int $pathType, bool $unixPath) : string {

        $returnPath = '';
        switch($pathType){
            case App::PATH_TYPE_CLASSES:
                $returnPath = "/{$this->ClassesPath}";
                break;
            case App::PATH_TYPE_VIEWS:
                $returnPath = "/{$this->ViewsPath}";
                break;
            case App::PATH_TYPE_REUSABLES:
                $returnPath = "/{$this->ReusablesPath}";
                break;
            default:
                # TODO
                # Throw an error
        }

        $returnPath = App::DocumentRoot($unixPath, true)."{$returnPath}";
        return $unixPath ? Url::ToUnix($returnPath) : Url::ToOS($returnPath);
    }

    public static function DocumentRoot(bool $unixPath = true) : string {

        return $unixPath ? Url::ToUnix($_SERVER['DOCUMENT_ROOT']) : Url::ToOS($_SERVER['DOCUMENT_ROOT']);
    }
    # END STATIC FUNCTIONS #



    # START ROUTING FUNCTIONS #
    public function Mount() : void {

        $requestUrl = Url::Diff($this->Root, $_SERVER['REQUEST_URI']);
        $requestUrlPath = Url::ToArray($requestUrl);

        $renderRoute = null;

        $routesData = [];
        foreach($this->Routes as $path => $route){
            $routeUrlPath = Url::ToArray($path);

            if(count($requestUrlPath) < count($routeUrlPath) || (count($routeUrlPath) < count($requestUrlPath) && !preg_match('/\*[\/]{0,1}$/', $path))){
                continue;
            }

            $routesData[$path] = [];

            $length = count($routeUrlPath);

            $i = 0;
            for($i = 0; $i < $length; $i++){

                $requestUrlValue = $requestUrlPath[$i];
                $routeUrlValue = $routeUrlPath[$i];

                if(strtolower($requestUrlValue) == strtolower($routeUrlValue)){
                    continue;
                }

                $matches = [];
                if(preg_match('/^\((.*?)\)$/', $routeUrlValue, $matches)){
                    # Get url Int value, (value) #

                    if(!is_numeric($requestUrlValue)){
                        break;
                    }

                    if(!is_int(+$requestUrlValue)){
                        break;
                    }

                    $routesData[$path][$matches[1]] = $requestUrlValue;
                } else if(preg_match('/^\+\((.*?)\)(\:([0-9]+)){0,1}$/', $routeUrlValue, $matches)){
                    # Get url Double value, +(value) #

                    if(!is_numeric($requestUrlValue)){
                        break;
                    }

                    $captureValue = $requestUrlValue;

                    $format = $matches[3] ?? -1;
                    if($format >= 0 && is_numeric($format)){
                        $captureValue = number_format($captureValue, $format);
                    }

                    $routesData[$path][$matches[1]] = $captureValue;
                } else if(preg_match('/^\{(.*?)\}(\:([0-9]+)){0,1}(\:([0-9]+)){0,1}$/', $routeUrlValue, $matches)){
                    # Get url String value, {value} #

                    $captureValue = $requestUrlValue;

                    $sliceStart = $matches[3] ?? -1;
                    $sliceLength = $matches[5] ?? -1;
                    if($sliceStart >= 0 && is_numeric($sliceStart)){
                        if($sliceLength > 0 && is_numeric($sliceLength)){
                            $captureValue = substr($captureValue, $sliceStart, $sliceLength);
                        } else {
                            $captureValue = substr($captureValue, $sliceStart);
                        }
                    }

                    $routesData[$path][$matches[1]] = $captureValue;
                } else if(preg_match('/^\+\{(.*?)\}$/', $routeUrlValue, $matches)){
                    # Get url Char value/Single character value, +{value} #

                    if(strlen($requestUrlValue) !== 1){
                        break;
                    }

                    $captureValue = $requestUrlValue;
                    $routesData[$path][$matches[1]] = $captureValue;
                } else if(preg_match('/^\[(.*?)\]$/', $routeUrlValue, $matches)){
                    # Get url Any value, [value] #

                    $captureValue = $requestUrlValue;
                    $routesData[$path][$matches[1]] = $captureValue;
                } else if(preg_match('/^((.*?)\=){0,1}\*$/', $routeUrlValue, $matches)){
                    # Get url Remain value, * #

                    $remainKey = $matches[2] ?? 'remain';
                    if(empty($remainKey)){
                        $remainKey = 'remain';
                    }
                    $remainValue = implode('/', array_slice($requestUrlPath, $i));

                    $routesData[$path][$remainKey] = $remainValue;
                } else {
                    break;
                }
            }

            if($i < $length && isset($routesData[$path])){
                unset($routesData[$path]);
            }
        }

        $routesFound = count($routesData);
        if($routesFound === 0){
            $this->ExecuteHTTPCallback(404, $requestUrl);
            return;
        }

        $routesPaths = array_keys($routesData);
        $routeRequested = $routesFound - 1;
        if(!isset($routesPaths[$routeRequested])){
            $this->ExecuteHTTPCallback(404, $requestUrl);
            return;
        }

        $routePath = $routesPaths[$routeRequested];
        if(!isset($this->Routes[$routePath])){
            $this->ExecuteHTTPCallback(404, $requestUrl);
            return;
        }

        $renderRoute = $this->Routes[$routePath];

        try{
            $this->RenderComponents(Reusable::POSITION_BEFORE_CONTENT);

            $renderRoute->Render($this, ...$routesData[$routePath]);
            $this->FindHTTPCallback($requestUrl);

            $this->RenderComponents(Reusable::POSITION_AFTER_CONTENT);
        } catch (Exceptions\RenderableNotFound $e){
            $this->DefineHTTPCode(404, $requestUrl, $e->getMessage());
        } catch (Exception $e){
            $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
        } catch (Error $e){
            $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
        }
    }

    private function RenderComponents(int $position = Reusable::POSITION_BEFORE_CONTENT) : void {

        try {
            foreach($this->Components as $component){
                if($component->Position === $position){
                    $component->Render($this);
                }
            }
        } catch (RenderableNotFound $e){
            echo 'Not found<br>';
        }
    }

    private function IncludeBind(string $path, string $filePath, callable $callback = null) : Route {

        $path = Url::InnerPath($path);
        $route = new Route($path, $filePath, $callback);
        $this->Routes[$path] = $route;

        return $route;
    }

    public function Bind(string $path, string $filePath, callable $callback = null) : void {

        $this->IncludeBind($path, $filePath, $callback);
    }

    public function BindHTTPResponse(int $code, string $path, string $filePath, callable $callback = null) : void {

        $route = $this->IncludeBind($path, $filePath);

        $path = Url::InnerPath($path);
        $this->HTTPCallback($code, $callback);
        if(isset($this->Errors[$code])){
            $this->Errors[$code]['route'] = $path;
        }
    }

    public function AddComponent(string $alias, string $filePath = null, int $contentPosition = Reusable::POSITION_AMONG_CONTENT, callable $callback = null) : void {

        if(is_null($filePath) && $this->AutoCreateComponents){
            $filePath = $alias;
        } else if (!$this->AutoCreateComponents && is_null($filePath)) {
            # TODO
            # Throw an error
            return;
        }

        $component = new Reusable($alias, $filePath, $contentPosition, $callback);
        $this->Components[$alias] = $component;
    }
    # END ROUTING FUNCTIONS #


    # START HTTP RESPONSE CONTROLLER FUNCTIONS #
    public function HTTPCallback(int $code, callable $callback = null) : void {

        if(!HTTP::ValidHTTPReponseCode($code)){
            return;
        }

        if(!isset($this->Errors[$code])){
            $this->Errors[$code] = [];
        }

        if(!isset($this->Errors['callback'])){
            $this->Errors['callback'] = [];
        }

        $this->Errors[$code]['callback'][] = $callback;
    }

    private function FindHTTPCallback(string $path) : bool {
        
        $path = Url::InnerPath($path);
        foreach($this->Errors as $code => $error) {
            if(isset($error['route']) && $error['route'] === $path){
                $this->ExecuteHTTPCallback($code, $path);
                return true;
            }
        }

        return false;
    }

    private function ExecuteHTTPCallback(int $code, string $requestUrl, mixed ...$params) : void {

        if(!HTTP::ValidHTTPReponseCode($code)){
            $this->DefineHTTPCode(500);
            trigger_error('Could not execute HTTP callback', E_USER_WARNING);
            return;
        }

        $this->DefineHTTPCode($code, $requestUrl, $params);
    }

    public function DefineHTTPCode(int $code, string $requestUrl = '', mixed ...$params) : void {

        HTTP::DefineHTTPCode($code, $requestUrl, function() use($code, $requestUrl, $params) {
            $relatedRoute = $this->Errors[$code]['route'] ?? '';
            if($relatedRoute && $requestUrl !== $relatedRoute){
                HTTP::Redirect($relatedRoute, $code);
            }

            $callback = $this->Errors[$code]['callback'] ?? [];
            foreach($callback as $call){
                if(!is_null($call) && is_callable($call)){
                    call_user_func($call, $code, $requestUrl, ...$params);
                }
            }
        }, ...$params);
    }
    # END HTTP RESPONSE CONTROLLER FUNCTIONS #
}
