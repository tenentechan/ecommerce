<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Product;

$app->get("/admin/products", function(){
	User::verifyLogin();//Autenticar login


    $search = (isset($_GET['search'])) ? $_GET['search'] : "";
	//Se for definido o page me traga page, se nao for definido me traga a 1 (primeira) pagina
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;

	/* Se usuario digitar no "search" traga search
	 * senao traga a primeira pagina
	 */
	if ($search != '') {
		$pagination = Product::getPageSearch($search, $page);

	} else {
	/* 
	 * Obs: Para deixar o usuario escolher a quantida de itens
	 * por pagina podemos passar como segundo paramento aqui.
	 */
	$pagination = Product::getPage($page);
	}

	$pages = [];

	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/products?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}


	$page = new PageAdmin();//Carregar Pagina Admin
	$page->setTpl("products",[ //Template
		"products"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});

//Route create products
$app->get("/admin/products/create", function(){
	User::verifyLogin();//Autenticar login
	$page = new PageAdmin();//Carregar Pagina Admin
	$page->setTpl("products-create");//Template
});

//Route save products
$app->post("/admin/products/create", function(){
	User::verifyLogin();//Autenticar login
	$product = new Product();//Carregar Pagina Produtos
	$product->setData($_POST);//Receber Produtos via POST
	$product->save();//Salvar Alteracoes
	header("Location: /admin/products");//Template
	exit;
});

//Route: Edit products
$app->get("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();//Autenticar login
	$product = new Product();//Carregar Pagina Produtos
	$product->get((int)$idproduct);
	$page = new PageAdmin();//Carregar Pagina Admin
	$page->setTpl("products-update", [//Template
		'product'=>$product->getValues()
	]);
});

//Route: Photo products
$app->post("/admin/products/:idproduct", function($idproduct){
	User::verifyLogin();//Autenticar login
	$product = new Product();//Carregar Pagina Produtos
	$product->get((int)$idproduct);//Carregar Pagina Produtos
	$product->setData($_POST);//Receber Produtos via POST
	$product->save();//Salvar Alteracoes
	$product->setPhoto($_FILES["file"]);
	header('Location: /admin/products');//Template
	exit;
});

//Route: Delete Products
$app->get("/admin/products/:idproduct/delete", function($idproduct){
	User::verifyLogin();//Autenticar login
	$product = new Product();//Carregar Pagina Produtos
	$product->get((int)$idproduct);
	$product->delete();//Deletar Produtos
	header(('Location: /admin/products'));//Template
	exit;
});



?>