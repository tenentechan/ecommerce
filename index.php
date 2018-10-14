<?php 
session_start();
require_once("vendor/autoload.php");

//nome das classes que vou usar do verndor
use \Slim\Slim;
use \Sowlife\Page;
use \Sowlife\PageAdmin;
use \Sowlife\Model\User;

$app = new Slim();

$app->config('debug', true);

//Rotas para serem usadas no site
$app->get('/', function() {
    
	$page = new Page();

	$page->setTpl("index");

});

//Rotas para serem usadas no admin
$app->get('/admin', function() {

	User::verifyLogin();
    
	$page = new PageAdmin();

	$page->setTpl("index");

});

//Rotas para serem usadas no login
$app->get('/admin/login', function() {
    
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false

	]);

	$page->setTpl("login");

});

//Rotas para acessar o login enviar formulario
$app->post('/admin/login', function() {
    
    User::login($_POST["login"], $_POST["password"]);

	header("Location: /admin");
	exit;

	});

$app->get('/admin/logout', function() {

	User::logout();

	header("Location: /admin/login");
	exit;
});

	

$app->run();

 ?>