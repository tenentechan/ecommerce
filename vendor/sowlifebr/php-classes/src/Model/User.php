<?php 

namespace Sowlife\Model;

use \Sowlife\Model;
use \Sowlife\DB\Sql;

class User extends Model {

	const SESSION = "User";

	protected $fields = [
		"iduser", "idperson", "deslogin", "despassword", "inadmin", "dtergister"
	];
	//metodo para receber o login e a senha
	public static function login($login, $password):User
	{
		//acessando o banco de dados
		$db = new Sql();

		//query para buscar o usuario no banco para validar o login e senha
		$results = $db->select("SELECT * FROM tb_users WHERE deslogin = :LOGIN", array(
			":LOGIN"=>$login
		));

		//Verificando se encontrou o login e senha
		if (count($results) === 0) {
			throw new \Exception("Não foi possível fazer login.");
		}
		
		//recebendo o resultado
		$data = $results[0];

		//verificando a senha
		if (password_verify($password, $data["despassword"])) {

			$user = new User();
			$user->setData($data);

			$_SESSION[User::SESSION] = $user->getValues();

			return $user;

		} else {

			throw new \Exception("Não foi possível fazer login.");

		}

	}

	public static function logout()
	{

		$_SESSION[User::SESSION] = NULL;

	}

	public static function verifyLogin($inadmin = true)
	{

		if (
			!isset($_SESSION[User::SESSION])
			|| 
			!$_SESSION[User::SESSION]
			||
			!(int)$_SESSION[User::SESSION]["iduser"] > 0
			||
			(bool)$_SESSION[User::SESSION]["iduser"] !== $inadmin
		) {
			
			header("Location: /admin/login");
			exit;

		}

	}

}

 ?>