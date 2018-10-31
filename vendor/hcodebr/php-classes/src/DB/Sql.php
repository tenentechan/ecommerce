<?php 

namespace Hcode\DB;

class Sql {

	const HOSTNAME = "127.0.0.1"; 	//Conectando ao Banco de Dados (www.hcodecommerce.cm.br)
	const USERNAME = "root";		//Usuário do Bando de Dados
	const PASSWORD = "";		//Senha do Usuário
	const DBNAME = "db_ecommerce";	//Nome do Banco de Dados

	private $conn;

	public function __construct()
	{

		$this->conn = new \PDO(
			"mysql:dbname=".Sql::DBNAME.";host=".Sql::HOSTNAME, 
			Sql::USERNAME,
			Sql::PASSWORD
		);

	}

	private function setParams($statement, $parameters = array())
	{

		foreach ($parameters as $key => $value) {
			
			$this->bindParam($statement, $key, $value);

		}

	}

	private function bindParam($statement, $key, $value)
	{

		$statement->bindParam($key, $value);

	}
	//Função query apenas executa algo do BD
	public function query($rawQuery, $params = array())
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

	}
	//Função select - Executa e traz dados do BD
	public function select($rawQuery, $params = array()):array
	{

		$stmt = $this->conn->prepare($rawQuery);

		$this->setParams($stmt, $params);

		$stmt->execute();

		return $stmt->fetchAll(\PDO::FETCH_ASSOC); //Método que retorna os dados do BD

	}

}

 ?>