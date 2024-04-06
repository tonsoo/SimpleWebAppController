# SimpleWebAppController

Uma mini framework para controle de aplicações web

## 🛠 Como executar

Verifique-se que você possue o PHP com versão >= 8.4

Clone o repositorio para o diretorio desejado.

```shell
git clone https://github.com/tonsoo/SimpleWebAppController /caminho/desejado/
```

Inicie seu servidor local, exemplo utilizando o servidor local do php

```shell
php -t /caminho/para/public -S localhost:8080
```

## ⚙ Como configurar

Acesse o script "public/index.php", configure o nome da aplicação em:

```shell
$app = new App("<Your_Application_Name>");
```

Crie uma nova rota usando:
```shell
$app->Bind('/url-da-rota', 'NomeDaView');
```

E por fim crie sua view em "Classes/Views/<Nome_da_View>.view.phtml"