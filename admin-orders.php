<?php

use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;

/* 
 * Eh importante manter a rota /admin/orders abaixo das demais 
 * caso contrario ela pode sobrescrever as outras rotas.
 */

//Route: status
$app->get("/admin/orders/:idorder/status", function($idorder){
	User::verifyLogin(); //Validar Login Admin
	$order = new Order();//Carregar pedido
	$order->get((int)$idorder);
	$page = new PageAdmin();//Carregar Pagina Admin
	$page->setTpl("order-status", [//Carregar Template
		'order'=>$order->getValues(),
		'status'=>OrderStatus::listAll(),
		'msgSuccess'=>Order::getSuccess(),
		'msgError'=>Order::getError()	
	]);
});

//Route: Save Status
$app->post("/admin/orders/:idorder/status", function($idorder){
	User::verifyLogin(); //Validar Login Admin
	//Verificando se o Status/Pedido foi informado
	if (!isset($_POST['idstatus']) || !(int)$_POST['idstatus'] > 0) {
		Order::setError("Informe status atual.");
		header("Location: /admin/orders/".$idorder."/status");
		exit;
	}
	$order = new Order();//Carregar pedido
	$order->get((int)$idorder);
	$order->setidstatus((int)$_POST['idstatus']);//Modificar Status/Pedido
	$order->save(); //Salvar Status do Pedido
	Order::setSuccess("Status atualizado."); //Msg de Success
	header("Location: /admin/orders/".$idorder."/status");
	exit;
});

//Route: Deletar Pedido
$app->get("/admin/orders/:idorder/delete", function($idorder){
	User::verifyLogin(); //Validar Login admin
	$order = new Order(); //Carregar pedido
	$order->get((int)$idorder); //O pedido ainda existe no BD?
	$order->delete(); //Deletar pedido
	header("Location: /admin/orders");
	exit;
});

//Route: Details
$app->get("/admin/orders/:idorder", function($idorder){
	User::verifyLogin();//Validar Login admin
	$order = new Order();//Carregar pedido
	$order->get((int)$idorder);
	$cart = $order->getCart();//Carregar Carrinho
	$page = new pageAdmin();//Carregar Pagina Admin
	$page->setTpl("order", [//Carregar Template
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts(),
	]);
});

//Route: Listar pedidos Admin
$app->get("/admin/orders", function(){
	User::verifyLogin();
	$search = (isset($_GET['search'])) ? $_GET['search'] : "";
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	if ($search != '') {
		$pagination = Order::getPageSearch($search, $page);
	} else {
		$pagination = Order::getPage($page);
	}
	$pages = [];
	for ($x = 0; $x < $pagination['pages']; $x++)
	{
		array_push($pages, [
			'href'=>'/admin/orders?'.http_build_query([
				'page'=>$x+1,
				'search'=>$search
			]),
			'text'=>$x+1
		]);
	}
	$page = new PageAdmin();
	$page->setTpl("orders", [
		"orders"=>$pagination['data'],
		"search"=>$search,
		"pages"=>$pages
	]);
});



?>