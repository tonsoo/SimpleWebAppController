<?php

register_shutdown_function(function() {
    if(!error_get_last()){
        return;
    }
    $error = error_get_last();
    require __DIR__.'/error.phtml';
});

use App\App;
use App\Database\Connection;

// ini_set('display_errors', '1');
// ini_set('display_startup_errors', '1');
// error_reporting(E_ALL);

require '../Classes/autoload.php';

$app = new App("Application name");

# Criação de novas rotas/paginas
$app->Bind('/', 'YourPageFileName');
$app->Bind('/second-page', 'YourSecondPage');
$app->Bind('/repeat-first-page', 'YourPageFileName');

# Routes with Int values #
$app->Bind('/integer/(capture)', 'UrlCapture/Integers');
$app->Bind('/integer/(capture)/remain=*', 'UrlCapture/Integers');

# Routes with Double values #
$app->Bind('/double/+(capture)', 'UrlCapture/Doubles');
$app->Bind('/double-format/+(capture):2', 'UrlCapture/Doubles');

# Routes with String values #
$app->Bind('/string/{capture}', 'UrlCapture/Strings');
$app->Bind('/substring/1/{capture}:2', 'UrlCapture/Strings');
$app->Bind('/substring/2/{capture}:2:4', 'UrlCapture/Strings');

# Routes with Char values/Single characters values #
$app->Bind('/char/+{capture}', 'UrlCapture/Chars');

# Routes with Any value #
$app->Bind('/any/[capture]', 'UrlCapture/Any');

# Criação de uma rota que sera usada como 
$app->BindHTTPResponse(404, '/pagina-da-rota', 'YourErrorRoute', function($HTTP_CODE, $HTTP_REQUEST, $HTTP_HEADERS) {
    echo "You can call functions here!<br>";
    echo "Varibles available: <br>";
    echo "HTTP_CODE(int): {$HTTP_CODE}<br>";
    echo "HTTP_REQUEST(string): {$HTTP_REQUEST}<br>";
    echo "HTTP_HEADERS(array): ";print_r($HTTP_HEADERS);echo "<br>";
});

$app->HTTPCallback(404, function($HTTP_CODE, $HTTP_REQUEST, $HTTP_HEADERS) {
    echo "Multiple callbacks for and HTTP code {$HTTP_REQUEST}";
});

# Criação de uma função de callback com relação direta ao erro "501"
$app->HTTPCallback(501, function($HTTP_CODE, $HTTP_REQUEST, $HTTP_HEADERS) {
    echo "Error 501 in {$HTTP_REQUEST}";
});

# Execução da aplicaçao com as configurações feitas
$app->Mount();
