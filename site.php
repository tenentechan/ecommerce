<?php

use \Hcode\Page;
use \Hcode\Model\Product;
use \Hcode\Model\Category;
use \Hcode\Model\Cart;
use \Hcode\Model\Address;
use \Hcode\Model\User;
use \Hcode\Model\Order;
use \Hcode\Model\OrderStatus;


//::::::::::::Route:principal
$app->get('/', function(){
	$products = Product::listAll(); //Listando os produtos do BD
	$page = new Page(); //Chama the method __construct and view result screen header
	$page->setTpl("index", [ //Adicionar arquivo que tem contéudo
		'products'=>Product::checkList($products)
	]);
	// Limpa a memória Chama o arquivo footer
});

//::::::::::::Route: Categories
$app->get("/categories/:idcategory", function($idcategory){
	/* 
	 * Em qual página estamos para chamarmos a correta;
	 * Verificar se foi passado o dado na URL, porém
	 * no primeiro acesso não teremos o número.
	 * Se nada fora passado será a página 1;
	 */
	$page = (isset($_GET['page'])) ? (int)$_GET['page'] : 1;
	$category = new Category();
	$category->get((int)$idcategory);
	$pagination = $category->getProductsPage($page);
	$pages = [];
	/*
	 * Enquanto $i for menor ou igual a $pagination['pages'] faca
	 * array_push
	 * 'link': Para qual caminho será encaminhado o usuário;
	 * /categories/;
	 * idcategory:$category->getidcategory();
	 * ?page=: recebendo o dado via GET conforme acima;
	 * $i: Para qual página.
	 */
	for ($i=1; $i <= $pagination['pages']; $i++) { 
    array_push($pages, [
        'link'=>'/categories/'.$category->getidcategory().'?page='.$i,
	'page'=>$i
    ]);
	}
/* 
 * Enquanto $i for menor ou igual a $pagination['pages'] faca
 * array_push
 * 'link': Para qual caminho será encaminhado o usuário;
 * /categories/;
 * idcategory: $category->getidcategory();
 * ?page=: recebendo o dado via GET conforme acima;
 * $i: Para qual página.
 */

	$page = new Page();
	$page->setTpl("category", [
		'category'=>$category->getValues(),
		'products'=>$pagination["data"],
		'pages'=>$pages
	]);
});

//::::::::::::Route URL
$app->get("/products/:desurl", function($desurl){
	$product = new Product();
	$product->getFromURL($desurl);
	$page = new Page;
	$page->setTpl("product-detail", [
		'product'=>$product->getValues(),
		/* 
		 * Mostrar a qual categoria o produto pertence. 
		 * Template usuario/categorias
		 */
		'categories'=>$product->getCategories()
	]);
});


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::::::: CART ::::::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: Cart 
$app->get("/cart", function(){
	$cart = Cart::getFromSession();
	$page = new Page();
	$page->setTpl("cart", [ //Passando as informações do Carrinho
		'cart'=>$cart->getValues(),
		'product'=>$cart->getProducts(),
		'error'=>Cart::getMsgError(),
	]);
});

//::::::::::::Route: Add Product
$app->get("/cart/:idproduct/add", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	/* 
	 * Recuperando o carrinho da sessao;
	 * Este metodo pega o carrinho da session ou cria um novo.
	 */
	$cart = Cart::getFromSession();
	$cart->addProduct($product);// Add product to cart
	header("Location: /cart");
	exit;
});

//::::::::::::Route: Remove One Product
$app->get("/cart/:idproduct/minus", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	/* 
	 * Recuperando o carrinho da sessao;
	 * Este metodo pega o carrinho da session ou cria um novo.
	 */
	$cart = Cart::getFromSession();
	$cart->removeProduct($product); //Remove product to cart
	header("Location: /cart");
	exit;
});

//::::::::::::Route: Remove All Product
$app->get("/cart/:idproduct/remove", function($idproduct){
	$product = new Product();
	$product->get((int)$idproduct);
	/* 
	 * Recuperando o carrinho da sessao;
	 * Este metodo pega o carrinho da session ou cria um novo.
	 */
	$cart = Cart::getFromSession();
	/* 
	 * Remove product to cart, por padrao "removeProduct recebe o 
	 * parametro $all como false" por este motivo eh preciso passar
	 * o parametro aqui como false para remover todos os products.
	 */
	$cart->removeProduct($product, true);
	header("Location: /cart");
	exit;
});

 //::::::::::::Route calcular frete
$app->post("/cart/freight", function(){
	$cart = Cart::getFromSession();//Pegando o carrinho que está na sessão
	/* 
	 * Method para receber o CEP 
	 * zipcode: nome contido na tag do arquivo html
	 * de onde os dados serão repassados.
	 */
	$cart->setFreight($_POST['zipcode']);
	header("Location: /cart");
	exit;
});

//::::::::::::Route: - Checkout, Acesso apos o login de usuario
$app->get("/checkout", function(){
	User::verifyLogin(false);//Validar login do usuario
	$address = new Address();//Carregar endereco	
	$cart = Cart::getFromSession();//Pegar carrinho da sessao
	if (!isset($_GET['zipcode'])) {
		$_GET['zipcode'] = $cart->getdeszipcode();
	}
	if (isset($_GET['zipcode'])) { //O cep foi enviado?
		$address->loadFromCEP($_GET['zipcode']);//Carrega cep
		$cart->setdeszipcode($_GET['zipcode']);
		$cart->save();//Salvar cart
		$cart->getCalculateTotal();//Caso tenha alterado o cep o valor do frete pode ter alterado
	}
	if (!$address->getdesaddress()) $address->setdesaddress('');
	if (!$address->getdesnumber()) $address->setdesnumber('');
	if (!$address->getdescomplement()) $address->setdescomplement('');
	if (!$address->getdesdistrict()) $address->setdesdistrict('');
	if (!$address->getdescity()) $address->setdescity('');
	if (!$address->getdesstate()) $address->setdesstate('');
	if (!$address->getdescountry()) $address->setdescountry('');
	if (!$address->getdeszipcode()) $address->setdeszipcode('');

	$page = new Page();
	$page->setTpl("checkout", [
		'cart'		=>$cart->getValues(),//Carregar Cart
		'address'	=>$address->getValues(),//Carregar Address
		'products'	=>$cart->getProducts(),//Carregar Product
		'error'		=>Address::getMsgError(),

	]);
});

//::::::::::::Route: - Save Address
$app->post("/checkout", function(){
	User::verifyLogin(false);//Autenticar usuario

	//Validar dados do formulario
	if (!isset($_POST['zipcode']) || $_POST['zipcode'] === '') {
		Address::setMsgError("Informe o CEP.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['desaddress']) || $_POST['desaddress'] === '') {
		Address::setMsgError("Informe o endereço.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['desdistrict']) || $_POST['desdistrict'] === '') {
		Address::setMsgError("Informe o bairro.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['descity']) || $_POST['descity'] === '') {
		Address::setMsgError("Informe a cidade.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['desstate']) || $_POST['desstate'] === '') {
		Address::setMsgError("Informe o estado.");
		header('Location: /checkout');
		exit;
	}
	if (!isset($_POST['descountry']) || $_POST['descountry'] === '') {
		Address::setMsgError("Informe o país.");
		header('Location: /checkout');
		exit;
	}

	$user = User::getFromSession();//Carregar usuario da session
	$address = new Address();//Criar novo Address
	$_POST['deszipcode'] = $_POST['zipcode'];//Converter zipcode/ deszipcode
	$_POST['idperson'] = $user->getidperson();
	$address->setData($_POST); //Receber dados via POST do formulario
	$address->save();
	$cart = Cart::getFromSession();//Carregar Cart da session
	$totals = $cart->getCalculateTotal(); //Calc Tot Cart
	$order = new Order();
	$order->setData([
		'idcart'=>$cart->getidcart(),
		'idaddress'=>$address->getidaddress(),
		'iduser'=>$user->getiduser(),
		'idstatus'=>OrderStatus::EM_ABERTO,
		'vltotal'=>$cart->getvltotal()
	]);
	$order->save(); //Salvar pedido / gerar id p/ proxima rota

	header("Location: /order/".$order->getidorder());
	exit;
});


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//::::::::::::::::::::::::LOGIN USER::::::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: - login usuario
$app->get("/login", function()
{
	$page = new Page();
	$page->setTpl("login", [
		'error'=>User::getError(),
		'errorRegister'=>User::getErrorRegister(),//Mostrar error na tela
		 //Ao digitar incorretamente nao precisa digitar novamente
		'registerValues'=>(isset($_SESSION['registerValues'])) ? 
		$_SESSION['registerValues'] : ['name'=>'', 'email'=>'', 'phone'=>'']
	]);
});

//::::::::::::Route: - Tela de login do usuário
$app->post("/login", function(){
	try {
		User::login($_POST['login'], $_POST['password']);
	} catch(Exception $e) {
		User::setError($e->getMessage());
	}
	header("Location: /checkout");
	exit;
});

//::::::::::::Route: - Logout cliente
$app->get("/logout", function(){
	User::logout();
	header("Location: /login");
	exit;
});

//::::::::::::Route: - Criar a Conta
$app->post("/register", function(){
	/* 
	 * Para que os dados preenchidos no formulario em caso de erro nao
	 * nao sejam perdidos, ou seja, nao precise o usuario digitar tudo
	 * novamente.
	 */
	$_SESSION['registerValues'] = $_POST;
	/* 
	 * Verificando se os dados digitados pelo usuario estao corretos
	 * 1. Se $_POST do name, email ou phone nao for definido 
	 * 2. Ou for igual a vazio
	 * 3. Direciona novamente para a tela de login.
	 */
	if (!isset($_POST['name']) || $_POST['name'] == '') {
		User::setErrorRegister("Preencha o seu nome.");
		header("Location: /login");
		exit;
	}
	if (!isset($_POST['email']) || $_POST['email'] == '') {
		User::setErrorRegister("Preencha o seu e-mail.");
		header("Location: /login");
		exit;
	}
	if (!isset($_POST['password']) || $_POST['password'] == '') {
		User::setErrorRegister("Preencha a senha.");
		header("Location: /login");
		exit;
	}
	if (User::checkLoginExist($_POST['email']) === true) {
		User::setErrorRegister("Este endereço de e-mail já está sendo usado por outro usuário.");
		header("Location: /login");
		exit;
	}

	$user = new User(); //Recebendo dados do usuário
	$user->setData([
		'inadmin'=>0, //Forcar o cadastro ser de um usuario 0(zero)
		'deslogin'=>$_POST['email'],
		'desperson'=>$_POST['name'],
		'desemail'=>$_POST['email'],
		'despassword'=>$_POST['password'],
		'nrphone'=>$_POST['phone']
	]);
	$user->save();//Salvar o usuario
	User::login($_POST['email'], $_POST['password']);//Autenticar o usuario
	header('Location: /checkout');
	exit;
});

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::FORGOT USER::::::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: Forgot User
$app->get("/forgot", function(){
	$page = new Page();
	$page->setTpl("forgot");
});

//::::::::::::Route: method para encaminhar via post o formulario
$app->post("/forgot", function(){
	$user = User::getForgot($_POST["email"], false);
	header("Location: /forgot/sent");//Trazer tela "Sucesso"
	exit;
});
 
//::::::::::::Route: sent
$app->get("/forgot/sent", function(){
	$page = new Page();
	$page->setTpl("forgot-sent");
});

//::::::::::::Route: reset
$app->get("/forgot/reset", function(){
	$user = User::validForgotDecrypt($_GET["code"]);//Validar e recuperar
	$page = new Page();
	$page->setTpl("forgot-reset", array(//Chamar variaveis do template
		"name"=>$user["desperson"],
		"code"=>$_GET["code"]
	));
});

//::::::::::::Route: Validando a nova senha
$app->post("/forgot/reset", function(){
    $forgot     = User::validForgotDecrypt($_POST["code"]);
    User::setForgotUsed($forgot["idrecovery"]);
    $user       = new User();
    $user       ->get((int)$forgot["iduser"]);
    //Creates a password hash
    $password   =  password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => 12]);
    //atributo no name no input de HTML = password
    $user       ->setPassword($password);
    $page = new Page();
    $page       ->setTpl("forgot-reset-success");
});


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::PROFILE USER:::::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: Profile - Perfil Usuario
$app->get("/profile", function(){
	User::verifyLogin(false);//Verificar login do usuario
	$user = User::getFromSession();//Recuperar usuario da SESION
	$page = new Page();//Template da pagina
	$page->setTpl("profile", [
		'user'=>$user->getValues(),
		'profileMsg'=>User::getSuccess(),
		'profileError'=>User::getError()
	]);
});

//::::::::::::Route: Save date user
$app->post("/profile", function(){
	User::verifyLogin(false);//Verificar login do usuario

	//Tratando erros possiveis
	if(!isset($_POST['desperson']) || $_POST['desperson'] === '') {
		User::setError("Prencha o seu nome.");
		header('Location: /profile');
		exit;
	}
	if(!isset($_POST['desemail']) || $_POST['desemail'] === '') {
		User::setError("Prencha o seu email.");
		header('Location: /profile');
		exit;
	}

	$user = User::getFromSession();//Recuperar usuario da SESION

	//Se entrar neste "if" significa que o email foi alterado
	//O email digitado eh diferente do email existente
	if ($_POST['desemail'] !== $user->getdesemail()){
		//O novo email ja existe?
		if (User::checkLoginExist($_POST['desemail']) === true) {
			User::setError("Este email ja existe.");
			header('Location: /profile');
			exit;
		}
	}

	/* 
	 * Para evitar um inject, caso o usuario mal intencionado descubra
	 * que a diferenca entre usuario e administrar esta em:
	 * 0(zero) ou 1(um), e tente sobrescrevelo diretamente via site,
	 * devemos forcar para que mesmo ocorrendo esta alteracao para 1 
	 * ou na tentativa de alteracao da senha pegaremos os dados
	 * do Banco de Dados e nao o digitado pelo usuario.
	 */
	$_POST['inadmin'] 	  = $user->getinadmin();
	$_POST['despassword'] = $user->getdespassword();
	$_POST['deslogin'] 	  = $_POST['desemail'];
	$user->setData($_POST);
	$user->update();//Alterar dados do usuario


	User::setSuccess("Dados alterados com sucesso!");

	header('Location: /profile');//Retornar para pagina profile
	exit;
});


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::::: ORDER :::::::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route:
$app->get("/order/:idorder", function($idorder){
	User::verifyLogin(false);//Validar usuario
	$order = new Order(); //Carregar Order
	$order->get((int)$idorder);

	$page = new Page();//Carregar pagina
	$page->setTpl("payment", [ //Carregar pagina payment
		'order'=>$order->getValues()
	]);
});


//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//::::::::::::::::::::::BOLETO PAYMENT ITAU:::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: Gerar Boleto Itau
$app->get("/boleto/:idorder", function($idorder){

	User::verifyLogin(false); //Validar usuario
	$order = new Order; //Carregar pedido
	$order->get((int)$idorder); //Carregar Order pelo ID

	// DADOS DO BOLETO PARA O SEU CLIENTE
	$dias_de_prazo_para_pagamento = 10;
	$taxa_boleto = 5.00;
	$data_venc = date("d/m/Y", time() + ($dias_de_prazo_para_pagamento * 86400));  // Prazo de X dias OU informe data: "13/04/2006"; 
	$valor_cobrado = formatPrice($order->getvltotal()); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal
	$valor_cobrado = str_replace(".", "", $valor_cobrado);
	$valor_cobrado = str_replace(",", ".",$valor_cobrado);
	$valor_boleto=number_format($valor_cobrado+$taxa_boleto, 2, ',', '');

	$dadosboleto["nosso_numero"] = $order->getidorder();  // Nosso numero - REGRA: Máximo de 8 caracteres!
	$dadosboleto["numero_documento"] = $order->getidorder();	// Num do pedido ou nosso numero
	$dadosboleto["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA
	$dadosboleto["data_documento"] = date("d/m/Y"); // Data de emissão do Boleto
	$dadosboleto["data_processamento"] = date("d/m/Y"); // Data de processamento do boleto (opcional)
	$dadosboleto["valor_boleto"] = $valor_boleto; 	// Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE
	$dadosboleto["sacado"] = $order->getdesperson();
	$dadosboleto["endereco1"] = 
	$order->getdesaddress() . " " . 
	$order->getdesdistrict();
	$dadosboleto["endereco2"] = 
	$order->getdescity() . " - " . 
	$order->getdesstate() . " - " . 
	$order->getdescountry() . " -  CEP: " . 
	$order->getdeszipcode();

	// INFORMACOES PARA O CLIENTE
	$dadosboleto["demonstrativo1"] = "Pagamento de Compra na Loja Teste";
	$dadosboleto["demonstrativo2"] = "Taxa bancária - R$ 0,00";
	$dadosboleto["demonstrativo3"] = "";
	$dadosboleto["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";
	$dadosboleto["instrucoes2"] = "- Receber até 10 dias após o vencimento";
	$dadosboleto["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: teste@teste.com.br";
	$dadosboleto["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE
	$dadosboleto["quantidade"] = "";
	$dadosboleto["valor_unitario"] = "";
	$dadosboleto["aceite"] = "";		
	$dadosboleto["especie"] = "R$";
	$dadosboleto["especie_doc"] = "";


	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //


	// DADOS DA SUA CONTA - ITAÚ
	$dadosboleto["agencia"] = "1690"; // Num da agencia, sem digito
	$dadosboleto["conta"] = "48781";	// Num da conta, sem digito
	$dadosboleto["conta_dv"] = "2"; 	// Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ
	$dadosboleto["carteira"] = "175";  // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS
	$dadosboleto["identificacao"] = "teste";
	$dadosboleto["cpf_cnpj"] = "00.000.000/0000-00";
	$dadosboleto["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";
	$dadosboleto["cidade_uf"] = "Goiânia - GO";
	$dadosboleto["cedente"] = "TESTE  LTDA - ME";

	// NÃO ALTERAR!

	$path = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
	"res" . DIRECTORY_SEPARATOR . 
	"boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once($path . "funcoes_itau.php");
	require_once($path . "layout_itau.php");
});

//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
//:::::::::::::::::::::::::PROFILE ORDER::::::::::::::::::::::::::::
//::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

//::::::::::::Route: Visualizar pedido
$app->get("/profile/orders", function(){
	User::verifyLogin(false); //Validar usuario
	$page = new Page(); //Carregar Pagina
	$user = User::getFromSession(); //Carregar User Session
	$page->setTpl("profile-orders", [//Carregar Template
		'orders'=>$user->getOrders()
	]);
});

//::::::::::::Route: Detalhes do produto
$app->get("/profile/orders/:idorder", function($idorder){
	User::verifyLogin(false); //Validar usuario
	$order = new Order(); //Carregar Pedido
	$order->get((int)$idorder);
	$cart = new Cart(); //Carregar carrinho deste pedido
	$cart->get((int)$order->getidcart());
	$cart->getCalculateTotal(); //Carregar Dados Totais
	$page = new Page(); //Carregar Pagina
	$user = User::getFromSession(); //Carregar User Session
	$page->setTpl("profile-orders-detail", [//Carregar Template
		'order'=>$order->getValues(),
		'cart'=>$cart->getValues(),
		'products'=>$cart->getProducts()
	]);
});

//::::::::::::Route: Alterar Senha
$app->get("/profile/change-password", function(){
	User::verifyLogin(false); //Autenticar usuario
	$page = new Page();//Carregar pagina
	$page->setTpl("profile-change-password", [//Carregar Template
		'changePassError'=>User::getError(),
		'changePassSuccess'=>User::getSuccess()
	]);
});

//::::::::::::Route: Alterar Senha - Receber Formulário
$app->post("/profile/change-password", function(){
	User::verifyLogin(false); //Autenticar usuario

	//Se current_pass nao existir OU estiver vazio
	if (!isset($_POST['current_pass']) || $_POST['current_pass'] === '') {
		User::setError("Digite a senha atual.");
		header("Location: /profile/change-password");
		exit;
	}

	if (!isset($_POST['new_pass']) || $_POST['new_pass'] === '') {
		User::setError("Digite a nova senha.");
		header("Location: /profile/change-password");
		exit;
	}

	if (!isset($_POST['new_pass_confirm']) || $_POST['new_pass_confirm'] === '') {
		User::setError("Confirme a nova senha.");
		header("Location: /profile/change-password");
		exit;
	}

	//A senha digitada atualmente eh diferente da nova
	if ($_POST['current_pass'] === $_POST['new_pass']) {
		User::setError("A nova senha deve ser diferente da atual.");
		header("Location: /profile/change-password");
		exit;
	}

	$user = User::getFromSession();

	//A senha esta correta?
	//Se senha digita eh igual a senha do Banco de Dados
	if(!password_verify($_POST['current_pass'], $user->getdespassword())){
		User::setError("Senha inválida.");
		header("Location: /profile/change-password");
		exit;		
	}

	$user->setdespassword($_POST['new_pass']);
	$user->update(); //O hash ja ocorre na function
	User::setSuccess("Senha alterada com sucesso!");
	header("Location: /profile/change-password");
	exit;

});



?>