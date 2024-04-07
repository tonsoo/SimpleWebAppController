<?php

namespace App;

use App\Controllers\Route;
use App\Utils\Url;
use App\Utils\HTTP;
use App\Exceptions;

class App {
    public const PATH_TYPE_VIEWS = 101;

    private string $ViewsPath;

    private string $Root;
    private string $DocumentRoot;

    private array $Errors = [];
    private array $Routes = [];

    private array $AppSettings;
    private bool $AutoCreateRoutes;

    public function __construct(string $title = 'New App', bool $autoCreateRoutes = false) {

        $this->DocumentRoot = App::DocumentRoot();
        $this->Root = App::Root();

        $this->SetPath('Classes/Views', App::PATH_TYPE_VIEWS);

        $this->AutoCreateRoutes = $autoCreateRoutes;

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

    public static function DocumentRoot(bool $unixPath = true) : string {

        return $unixPath ? Url::ToUnix($_SERVER['DOCUMENT_ROOT']) : Url::ToOS($_SERVER['DOCUMENT_ROOT']);
    }

    public function ClassesPath(bool $unixPath = true) : string {

        $returnUrl = App::DocumentRoot($unixPath, true).'/'.$this->ClassesPath;
        return $unixPath ? Url::ToUnix($returnUrl) : Url::ToOS($returnUrl);
    }

    public function ViewsPath(bool $unixPath = true) : string {

        $returnUrl = App::DocumentRoot($unixPath, true)."/{$this->ViewsPath}";
        return $unixPath ? Url::ToUnix($returnUrl) : Url::ToOS($returnUrl);
    }
    # END STATIC FUNCTIONS #



    # START ROUTING FUNCTIONS #
    public function Mount() : void {

        $requestUrl = Url::Diff($this->Root, $_SERVER['REQUEST_URI']);

        if(!isset($this->Routes[$requestUrl])){
            $this->ExecuteHTTPCallback(404, $requestUrl);
            return;
        }

        $renderRoute = $this->Routes[$requestUrl];

        try{
            $renderRoute->Render($this);
            $this->FindHTTPCallback($requestUrl);
        } catch (Exceptions\RouteNotFound $e){
            $this->DefineHTTPCode(404, $requestUrl, $e->getMessage());
        } catch (Exception $e){
            $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
        } catch (Error $e){
            $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
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
