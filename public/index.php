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

# Criação de uma rota que sera usada como 
$app->BindHTTPResponse(404, '/pagina-da-rota', 'YourErrorRoute', function($HTTP_CODE, $HTTP_REQUEST, $HTTP_HEADERS) {
    echo "You can call functions here!<br>";
    echo "Varibles available: <br>";
    echo "HTTP_CODE(int): {$HTTP_CODE}<br>";
    echo "HTTP_REQUEST(string): {$HTTP_REQUEST}<br>";
    echo "HTTP_HEADERS(array): ";print_r($HTTP_HEADERS);echo "<br>";
});

# Criação de uma função de callback com relação direta ao erro "501"
$app->HTTPCallback(501, function($HTTP_CODE, $HTTP_REQUEST, $HTTP_HEADERS) {
    echo "Error 501 in {$HTTP_REQUEST}";
});

# Execução da aplicaçao com as configurações feitas
$app->Mount();