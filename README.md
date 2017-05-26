Exercicio Programador Vox
========================

Este projeto foi feito utilizando o framework [**Symfony**][1] 3.2.8.
Foram utilizados os bundles:
  * FOSUserBundle v2.0: https://github.com/FriendsOfSymfony/FOSUserBundle
  * PetkoparaCrudGeneratorBundle v3.0.5: https://github.com/petkopara/PetkoparaCrudGeneratorBundle

Requisitos
--------------
PHP 7  
[**Composer**][4]  
MariaDB  

Intruçoes
--------------

* Depois de clonado o repositorio, acessar o diretorio e executar o comando:

    composer install

* Criar o database com o nome escolhido durante a configuraçao dos parametros:

    database_host (127.0.0.1):  
    database_port (null):  
    **database_name (symfony): vox**  
    database_user (root):  
    database_password (null):  
    mailer_transport (smtp):  
    mailer_host (127.0.0.1):  
    mailer_user (null):  
    mailer_password (null):  
    locale (en):  
    secret (ThisTokenIsNotSoSecretChangeIt):  

* Atualizar o schema

    php app/console doctrine:schema:update --force  

* Iniciar o servidor e acessar a aplicaçao
    php bin/console server:run  
    localhost:8000  

Obrigado.

[1]:  https://symfony.com/doc/3.2/setup.html
[2]:  https://github.com/FriendsOfSymfony/FOSUserBundle
[3]:  https://github.com/petkopara/PetkoparaCrudGeneratorBundle
[4]:  https://getcomposer.org/download/
