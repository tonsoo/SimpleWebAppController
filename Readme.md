# SimpleWebAppController

Uma mini framework para controle de aplicações web

## 🛠 Como executar

Verifique-se que você possue o PHP com versão >= 7.4

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

Defina a criação de uma nova rota usando:
```shell
$app->Bind('/<url_da_rota>', '<Nome_da_View>');
```

É possivel recurar os valores digitados pelo usuario:
```shell
# Numeros Inteiros:
$app->Bind('/<url_da_rota>/(<nome_variavel_inteira>)', '<Nome_da_View>');

# Numeros Reais (doubles)
$app->Bind('/<url_da_rota>/+(<nome_variavel_real>)', '<Nome_da_View>');
$app->Bind('/<url_da_rota>/+(<nome_variavel_real_4_casas_decimais>):4', '<Nome_da_View>');

# Strings
$app->Bind('/<url_da_rota>/{<nome_variavel_string>}', '<Nome_da_View>');
$app->Bind('/<url_da_rota>/{<nome_variavel_string_a_partir_indice_2>}:2', '<Nome_da_View>');
$app->Bind('/<url_da_rota>/{<nome_variavel_string_a_partir_indice_2_com_4_caracteres>}:2:4', '<Nome_da_View>');

# Chars
$app->Bind('/<url_da_rota>/+{<nome_variavel_char>}', '<Nome_da_View>');

# Continuação da url
$app->Bind('/<url_da_rota>/<nome_da_variavel=>*', '<Nome_da_View>');
```

Crie sua view em "Classes/Views/<Nome_da_View>.view.phtml"

Por fim acesse http://localhost:8080 em seu navegador e faça seus testes

