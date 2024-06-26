<?php

register_shutdown_function(function() {
    if(!error_get_last()){
        return;
    }
    $error = error_get_last();
    require __DIR__.'/error.phtml';
});

use App\App;

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require '../Classes/autoload.php';

$app = new App();

# Definição de caminhos customizados
// $app->SetPath('new-public-folder', App::PATH_TYPE_PUBLIC);
// $app->SetPath('new-classes-folder', App::PATH_TYPE_CLASSES);
// $app->SetPath('new-views-folder', App::PATH_TYPE_VIEWS);
// $app->SetPath('new-reusables-folder', App::PATH_TYPE_REUSABLES);
// $app->SetPath('new-settings-folder', App::PATH_TYPE_SETTINGS);

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

# Routes with the remains url #
$app->Bind('/remain-url/capture=*', 'UrlCapture/Remain');

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

$app->AddComponent('Header', 'Header', \App\Controllers\Reusable::POSITION_BEFORE_CONTENT);
$app->AddComponent('Produto', 'Produto', \App\Controllers\Reusable::POSITION_AMONG_CONTENT);
$app->AddComponent('Test', './CustomPath/Test', \App\Controllers\Reusable::POSITION_AMONG_CONTENT);

# Execução da aplicaçao com as configurações feitas
$app->Mount('default.phtml');
