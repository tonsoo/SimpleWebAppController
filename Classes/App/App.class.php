<?php

namespace App;

use App\Controllers\Route;
use App\Utils\Url;
use App\Exceptions;

class App {

    public const VIEWS_PATH = 'Classes'.DIRECTORY_SEPARATOR.'Views'.DIRECTORY_SEPARATOR;
    private string $Root;
    private string $DocumentRoot;

    private array $Errors = [];
    private array $Routes = [];

    private array $AppSettings;
    private bool $AutoCreateRoutes;

    public function __construct(string $title = 'New App', bool $autoCreateRoutes = false) {

        $this->DocumentRoot = App::DocumentRoot();
        $this->Root = App::Root();

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



    # START STATIC FUNCTIONS #
    public static function Root(bool $unixPath = true) : string {

        return Url::Diff(App::DocumentRoot($unixPath), pathinfo($_SERVER['SCRIPT_FILENAME'])['dirname'], $unixPath ? Url::Unix : Url::OS);
    }

    public static function DocumentRoot(bool $unixPath = true) : string {

        return $unixPath ? Url::ToUnix($_SERVER['DOCUMENT_ROOT']) : $_SERVER['DOCUMENT_ROOT'];
    }

    public static function ViewsPath(bool $unixPath = true) : string {

        $returnUrl = App::DocumentRoot($unixPath).'/'.App::VIEWS_PATH;
        return Url::ToOS($returnUrl);
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
        } catch (Exceptions\RouteNotFound $e){
            $this->DefineHTTPCode(404, $requestUrl, $e->getMessage());
        } catch (Exception $e){
            $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
        } catch (Error $e){
            echo 'errroooo';
            die();
            // $this->ExecuteHTTPCallback(500, $requestUrl, $e->getMessage());
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
        $this->HTTPCallback($code, function($HTTP_CODE, $HTTP_ROUTE, $HTTP_HEADERS){
            echo "HTTP_CODE: {$HTTP_CODE}<br>";
            echo "HTTP_ROUTE: {$HTTP_ROUTE}<br>";
            echo 'HTTP_HEADERS: ';
            print_r($HTTP_HEADERS);
            echo '<br>';
        }, $path);
        if(isset($this->Errors[$code])){
            $this->Errors[$code]['route'] = $path;
        }
    }
    # END ROUTING FUNCTIONS #


    # START HTTP RESPONSE CONTROLLER FUNCTIONS #
    public function HTTPCallback(int $code, callable $callback = null) : void {

        if(!$this->ValidHTTPReponseCode($code)){
            return;
        }

        if(!isset($this->Errors[$code])){
            $this->Errors[$code] = [];
        }

        $this->Errors[$code]['callback'] = $callback;
    }

    private function ValidHTTPReponseCode(int $code) : bool {

        $codeIsValid = preg_match('/^[1-5][0-9]{2}$/', $code);
        if(!$codeIsValid){
            trigger_error("Error code '{$code}' is invalid.", E_USER_WARNING);
        }
        return $codeIsValid;
    }

    private function FindHTTPCallback(string $path) : bool {
        
        $path = Url::InnerPath($path);
        foreach($this->Errors as $code => $error) {
            if($error['route'] === $path){
                $this->ExecuteHTTPCallback($code, $path);
                return true;
            }
        }

        return false;
    }

    private function ExecuteHTTPCallback(int $code, string $requestUrl, mixed ...$params) : void {

        if(!$this->ValidHTTPReponseCode($code)){
            $this->DefineHTTPCode(500);
            trigger_error('Could not execute HTTP callback', E_USER_WARNING);
            return;
        }

        $this->DefineHTTPCode($code, $requestUrl, $params);
    }

    public function DefineHTTPCode(int $code, string $requestUrl = '', mixed ...$params) : void {

        if(!$this->ValidHTTPReponseCode($code)){
            if($code === 500){
                # Avoid infinite redirects
                http_response_code(508);
                die('Fatal error');
            }
            $this->DefineHTTPCode(500);
            return;
        }

        http_response_code($code);

        $relatedRoute = $this->Errors[$code]['route'] ?? '';
        if($relatedRoute && $requestUrl !== $relatedRoute){
            App::Redirect($relatedRoute, $code);
        }

        $callback = $this->Errors[$code]['callback'] ?? null;
        if(!is_null($callback) && is_callable($callback)){
            call_user_func($callback, $code, $requestUrl, $params);
        }

        die();
    }

    public static function Redirect(string $url, int $code = null) : void {

        $url = Url::InnerPath($url);
        if(!is_null($code)){
            http_response_code($code);
        }
        $root = App::Root();
        $redirectPath = "/{$root}/{$url}";
        header("Location: {$redirectPath}");
        die();
    }
    # END HTTP RESPONSE CONTROLLER FUNCTIONS #
}
