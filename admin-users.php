<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;

//::::::::::::Route: Alter Password User Admin
$app->get("/admin/users/:iduser/password", function($iduser){
	User::verifyLogin(); //Validar Login
	$user = new User(); //Carregar Usuario
	$user->get((int)$iduser);
	$page = new PageAdmin(); //Carregar Pagina
	$page->setTpl("users-password", [//Template
		"user"=>$user->getValues(),
		"msgError"=>User::getError(),
		"msgSuccess"=>User::getSuccess()
	]);
});

//::::::::::::Route: Save Alteration Password User Admin
$app->post("/admin/users/:iduser/password", function($iduser){
	User::verifyLogin(); //Validar Login
	//despasswor (senha) nao foi definida(Digitada) OU esta VAZIA
	if (!isset($_POST['despassword']) || $_POST['despassword'] === '') {
		User::setError("Preencha a nova senha.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}
	if (!isset($_POST['despassword-confirm']) || $_POST['despassword-confirm'] === '') {
		User::setError("As senhas precisam ser iguais.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}
	//Validar se as senhas sao iguais
	if ($_POST['despassword'] !== $_POST['despassword-confirm']) {
		User::setError("Confirme corretamente as senhas.");
		header("Location: /admin/users/$iduser/password");
		exit;
	}
	$user = new User(); //Carregar Usuario
	$user->get((int)$iduser);
	//Alterando a senha/Salvando em Hash no Banco de Dados
	$user->setPassword(User::getPasswordHash($_POST['despassword']));
	User::setSuccess("Senha alterada com sucesso.");
	header("Location: /admin/users/$iduser/password");
	exit;
});


/*
 * Route Listar todos os usuarios
 * Para acessar esta tela é necessário estar logado
 */
$app->get("/admin/users", function(){
	/* Autenticacao
	 * Como nao passamos nenhum parametro o $inadmin 
	 * por padrao eh "true", vai verificar se eh um 
	 * usuario logado e se tem acesso ao administrativo.
	 */
	User::verifyLogin();//Validar login
	 //Validar - Carregar search
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	//Se for definido o page me traga page, se nao for definido me traga a 1 (primeira) pagina
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	/* Se usuario digitar no "search" traga search
	 * senao traga pagina
	 */
	if ($search != '') {
		$pagination = User::getPageSearch($search, $page);

	} else {
	/* 
	 * Obs: Para deixar o usuario escolher a quantida de itens
	 * por pagina podemos passar como segundo paramento aqui.
	 */
	$pagination = User::getPage($page);
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/users?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}

	$page = new PageAdmin();
	$page->setTpl("users", array(
		"users"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	));
});

/*
 * Route Create usuarios
 * Para acessar esta tela é necessário estar logado.
 * Esta route vai encaminhar os dados para uma outra
 * route e nesta é que os dados são salvos.
 */
$app->get("/admin/users/create", function(){
	/* Autenticacao
	 * Como nao passamos nenhum parametro o $inadmin 
	 * por padrao eh "true", vai verificar se eh um 
	 * usuario logado e se tem acesso ao administrativo.
	 */
	User::verifyLogin();
	$page = new PageAdmin();
	//Chama a pagina users-create
	$page->setTpl("users-create");
});

/* 
 * Route para deletar
 * É necessario que esta route fique acima da
 * /admin/users/:iduser, porque quando for executado
 * pode ser interpretado que:
 * /admin/users/:iduser e
 * /admin/users/:iduser/delete
 * são a mesma coisa, sendo assim chamamos primeiro o 
 * method delete e depois o update
 */
$app->get("/admin/users/:iduser/delete", function($iduser) {
	User::verifyLogin();	
	$user = new User();
	$user->get((int)$iduser);
	$user->delete();
	header("Location: /admin/users");
	exit;
});

/* 
 * Route Update (Alterar)
 * Para acessar esta tela é necessário estar logado
 * Passando no caminho :iduser ele trará a mesma
 * tela porem preenchida (boas praticas de programacao)
 * O valor recebido em :iduser sera repassado para
 * a variavel no parametro da function $iduser.
 */
$app->get("/admin/users/:iduser", function($iduser){
	/* Autenticacao
	 * Como nao passamos nenhum parametro o $inadmin 
	 * por padrao eh "true", vai verificar se eh um 
	 * usuario logado e se tem acesso ao administrativo.
	 */
	User::verifyLogin();
	$user = new User();
	$user->get((int)$iduser);
	$page = new PageAdmin();
	//Chama a pagina users-update
	$page->setTpl("users-update", array(
		"user"=>$user->getValues()
	));
});

/* 
 * Route para Salvar os dados criados na route
 * /admin/users/create.
 * Por ser uma route via POST ocorrerá uma inserção de 
 * dados no BD.
 */
$app->post("/admin/users/create", function(){
	/* Autenticacao
	 * Como nao passamos nenhum parametro o $inadmin 
	 * por padrao eh "true", vai verificar se eh um 
	 * usuario logado e se tem acesso ao administrativo.
	 */
	User::verifyLogin();
	//Criando um novo usuário
	$user       = new User();
	/*
	 * Antes de passarmos o POST para o setData
	 * vamo verificar;
	 * Se tiver sido definido o valor 		é 1
	 * Se não tiver sido definido o valor 	é 0
	 */
    $_POST["inadmin"] = 
    (isset($_POST["inadmin"])) ? 1 : 0;
    $_POST['despassword'] = 
    password_hash($_POST["despassword"], PASSWORD_DEFAULT, 
    	[
        	"cost"=>12
    	]);
    $user       ->setData($_POST);
    $user       ->save();
    /*
     * Retorna na página do caminho abaixo
     */
    header      ("Location: /admin/users");
    exit;
});

/*
 * Route para salvar a edicao do :iduser
 * Por ser uma route via POST ocorrerá uma inserção de 
 * dados no BD.
 */
$app->post("/admin/users/:iduser", function($iduser){
	/* Autenticacao
	 * Como nao passamos nenhum parametro o $inadmin 
	 * por padrao eh "true", vai verificar se eh um 
	 * usuario logado e se tem acesso ao administrativo.
	 */
	User::verifyLogin();
	/*
	 * Ex: se alterarmos apenas o nome... Internamente
	 * ocorrerá que todos os dados serão puxados e 
	 * todos os dados serão devolvidos, ou seja, mesmo
	 * se o dado não tiver sido alterado ele será 
	 * substituído por ele mesmo.
	 */
	$user = new User();
	/*
	 * Antes de passarmos o POST para o setData
	 * vamo verificar;
	 * Se tiver sido definido o valor 		é 1
	 * Se não tiver sido definido o valor 	é 0
	 */
	$_POST["inadmin"] = 
    (isset($_POST["inadmin"])) ? 1 : 0;

	/*Carregando os dados, colocando nos $values*/
	$user->get((int)$iduser);

	/**/
	$user->setData($_POST);

	/* method update */
	$user->update();

    /*
     * Retorna na página do caminho abaixo
     */
	header("Location: /admin/users");
	exit;
});



?>