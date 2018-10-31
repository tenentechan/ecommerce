<?php 

/* SESSION
 * Verificando se a nossa SESSION está rodando no
 * servidor web
 */
session_start();

/* composer
 * Composer vendor/autoload.php, traz todas as
 * dependências do composer que o vendor precisa
 */
require_once("vendor/autoload.php");

use \Slim\Slim; 			//Class Slim
use \Hcode\Page; 			//Class Page
use \Hcode\PageAdmin; 		//Class PageAdmin
use \Hcode\Model\User; 		//Class user
use \Hcode\Model\Category; 	//Class Category

$app = new \Slim\Slim();

//Mostra erros
$app->config('debug', true);

require_once("functions.php");
require_once("site.php");
require_once("admin.php");
require_once("admin-users.php");
require_once("admin-categories.php");
require_once("admin-products.php");
require_once("admin-orders.php");




//Após verificar que tudo está carregado o "run" roda
$app->run();

 ?>