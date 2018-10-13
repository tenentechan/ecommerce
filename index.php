<?php 

require_once("vendor/autoload.php");

//nome das classes que vou usar do verndor
use \Slim\Slim;
use \Sowlife\Page;

$app = new Slim();

$app->config('debug', true);

//Rotas para serem usadas
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

$app->run();

 ?>