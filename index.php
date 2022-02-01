<?php
//Controlar cache da aplicação, fazendo que tenha apenas 1 output, e otimizando recursos
ob_start();

require __DIR__ . "/vendor/autoload.php";

/**
 * Bootstrap
 */
use Source\Core\Session;
use CoffeeCode\Router\Router;

$session = new Session();
$route = new Router(url(), ":");

/**
 * Web Routes
 */
//ainda vai estar os controladores
$route->namespace("Source\App");
//rota, controlador, método
$route->get("/", "Web:home");
$route->get("/sobre", "Web:about");
$route->get("/termos", "Web:terms");

//VIEW BLOG
$route->group("/blog");
$route->get("/", "Web:blog");
$route->get("/p/{page}", "Web:blog");
//vai ser /blog e o /nome-artigo. Vai ser a url do nosso post
$route->get("/{uri}", "Web:blogPost");
//vai ser para fazer pesquisa
$route->post("/buscar", "Web:blogSearch");
$route->get("/buscar/{terms}/page", "Web:blogSearch");

//AUTH
//qd tiver trabalhando na raiz, tem que desagrupar
$route->group(null);
$route->get("/entrar", "Web:login");
$route->get("/recuperar", "Web:forget");
$route->get("/cadastrar", "Web:register");

//OPTIN
$route->get("/confirma", "Web:confirm");
$route->get("/obrigado", "Web:success");

//SERVICES
$route->get("/termos", "Web:terms");

/*
 * Error Routes
 */
//É interessante sempre declarar os namespace das rotas, para evitar bugs
$route->namespace("Source\App")->group("/ops");
//Cód de erro q vai ser levado para o controlador para usar na app
$route->get("/{errcode}", "Web:error");

/*
 * Route
 */
//executar as rotas
$route->dispatch();

/*
 * Error Redirect
 */
//vai controlar caso o dispatch não consiga entrar a rota funcional para o usuário
if ($route->error()){
    $route->redirect("/ops/{$route->error()}");
}




//Desarmazenar o cache e fazer o flush, dar o output
ob_end_flush();