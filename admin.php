<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

/*
 * Rota Admin:
 * link da administração '/admin', para dificultar
 * ataques hackers geralmente o nome do link é alterado
 */
$app->get('/admin', function()
{
	/*Method Static*/
	User::verifyLogin();
	//Chama the method __construct and view result screen header
	$page = new PageAdmin();
	//Aqui é adicionado o arquivo que possui contéudo
	$page->setTpl("index");
	//Limpa a memória
	//Chama o arquivo footer
});

/*
 * Route Login:
 * É necessário desabilitar a chamada do header e footer.
 * Quando a rota /admin/login é chamada já estamos no
 * administrativo. Porém nesta tela é necessário
 * apresentar apenas o index do "login", até mesmo
 * porque o header e footer só podem ser mostrados após
 * a autenticação.
 */
$app->get('/admin/login', function()
{
/*
 * Desabilitando header e footer com false
 * É necessário habilitar esta opção no PageAdmin
 */
$page = new pageAdmin
([
	"header"=>false,
	"footer"=>false
]);
$page->setTpl("login");
});

/* 
 * Tela de Login
 * O method é post, a página html por isso aqui
 * precisa ser post
 */
$app->post('/admin/login', function()
{
	/*
	 * Criado um method static: porque não temos
	 * nenhum dado.
	 * Receberemos através do post o login e senha.
	 */
	User::login($_POST["login"], $_POST["password"]);
	header("Location: /admin");//Retorna na página do caminho abaixo
	exit;
});

//Route: Logout
$app->get('/admin/logout', function()
{
	User::logout();//Criado method static para o logout
	header("Location: login");//Retorna na página do caminho abaixo
	exit;
});

//Route: Forgot
$app->get("/admin/forgot", function()
{
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot");
});

//Route: method para encminhar via post o formulario
$app->post("/admin/forgot", function()
{
	/*
	 * Esta maneira eh identificada no arquivo 
	 * forgot.html 
	 * em: action="/admin/forgot method="post"
	 * name="email"
	 */
	$user = User::getForgot($_POST["email"]);
	header("Location: /admin/forgot/sent");//Trazer tela "Sucesso"
	exit;
});
 
//Route: sent
$app->get("/admin/forgot/sent", function()
{
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	$page->setTpl("forgot-sent");
});

//Route: reset
$app->get("/admin/forgot/reset", function()
{
	$user = User::validForgotDecrypt($_GET["code"]);//Validar e recuperar
	$page = new PageAdmin([
		"header"=>false,
		"footer"=>false
	]);
	//Devemos chamar as variaveis do template
	$page->setTpl("forgot-reset", array(
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

//Route: Validando a nova senha
$app->post("/admin/forgot/reset", function()
{
    $forgot     = User::validForgotDecrypt($_POST["code"]);
    User::setForgotUsed($forgot["idrecovery"]);
    $user       = new User();
    $user       ->get((int)$forgot["iduser"]);
    //Creates a password hash
    $password   =  password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => 12]);
    //atributo no name no input de HTML = password
    $user       ->setPassword($password);
    $page       = new PageAdmin
    ([
        "header"=> false,   //Disable
        "footer"=> false    //Disable
    ]);
    $page       ->setTpl("forgot-reset-success");
});


?>