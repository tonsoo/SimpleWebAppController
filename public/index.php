<?php

register_shutdown_function(function() {
    if(!error_get_last()){
        return;
    } ?>
    <style>
        @keyframes fundo{
            0%{background-position: calc(100vw + 51px) calc(0vw + 51px / 2), 100vw calc(0vw + 51px), 100vw calc(0vw + -1px);}
            50%{background-position: calc(150vw + 51px) calc(50vw + 51px / 2), 150vw calc(50vw + 51px), 150vw calc(50vw + -1px);}
            1000%{background-position: calc(200vw + 51px) calc(100vw + 51px / 2), 200vw calc(100vw + 51px), 200vw calc(100vw + -1px);}
            51px calc(51px / 2), 0 51px, 0 0
        }

        .container{width: 100%;height: 100%;position: fixed;left: 0;top: 0;display: grid;place-items: center;background:conic-gradient(from 135deg,#9c9df0 90deg,#0000 0) 51px calc(51px/2),conic-gradient(from 135deg,#fff3e5 90deg,#0000 0),conic-gradient(from 135deg at 50% 0,#9c9df0 90deg,#0000 0) #fff3e5;background-size: 102px 51px;animation: fundo 270s linear infinite alternate;}
        .container .fundo{width: 400px;max-width: 90%;background: #fff;border-radius: 7px;box-shadow: 0 0 5px 0 #676767;box-sizing: border-box;padding: 25px 20px}
        .container .fundo img{width: 100px;aspect-ratio: 1/1;margin: 20px auto 25px;object-fit: contain;display: block;}
        .container .fundo h1,
        .container .fundo h2,
        .container .fundo h3{color: #292929;text-align: center;}
        .container .fundo h2,
        .container .fundo h3{line-height: 27px !important;font-weight: 600 !important;font: 20px sans-serif;}
        .container .fundo h1{font: 30px sans-serif;font-weight: 900;margin: 0 0 40px;text-decoration: underline;line-height: 37px;}
        .container .fundo h2{margin-bottom: 20px;}

        @media (prefers-reduced-motion) {
            .container{animation-duration: 800s;}
        }
    </style>
    <div class="container">
        <div class="fundo">
            <img src="<?= str_replace($_SERVER['DOCUMENT_ROOT'], '', pathinfo($_SERVER['SCRIPT_FILENAME'])['dirname']) ?>/img/default-error.svg" alt="Erro de servidor">
            <h1>Erro: 500</h1>
            <h2>Houve um erro ao processar a requisição!</h2>
            <h3>Volte mais tarde.</h3>
        </div>
    </div>
    <?php
});

use App\App;
use App\Database\Connection;

require '../Classes/autoload.php';

$app = new App();

$app->Bind('/', 'Home');
$app->Bind('/home-2', 'Home');
$app->Bind('/page-2', 'Page-2');

$app->BindHTTPResponse(501, '/501', 'Response', function($HTTP_CODE) {
    echo '<h1>Not implemented I guess</h1>';
});

$app->BindHTTPResponse(500, '/500', 'Response', function($HTTP_CODE) {
    register_shutdown_function(function(){
        echo 'error?';
    });
});

$app->Mount();
// $app->Redirect('teste');

// $Conn = new Connection('../Settings/database.ini');