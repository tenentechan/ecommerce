<?php 

require_once("vendor/autoload.php");

//nome das classes que vou usar do verndor
use \Slim\Slim;
use \Sowlife\Page;
use \Sowlife\PageAdmin;

$app = new Slim();

$app->config('debug', true);

//Rotas para serem usadas no site
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

//Rotas para serem usadas
$app->get('/admin', function() {
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

$app->run();

 ?>