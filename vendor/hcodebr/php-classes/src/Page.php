<?php

//Especicar onde a classe do namespace está.
namespace Hcode;


//Utilizando classes de outro namespace
//Ex: Quando chamarmos o new Tpl se trata do namespace Rain
use Rain\Tpl;

//Classe
class Page {


	private $tpl; //Outras classes não terão acesso
	private $optins = [];
	private $defaults =
	[
		"header"=>true,
		"footer"=>true,
		"data"=>[]
	];

	/* Método Mágico _construct
	 * O template precisa de uma pasta para pegar os 
	 * arquivos em HTML e uma pasta para os caches
	 * $_SERVER["DOCUMENT_ROOT"]-Utilizamos esta 
	 * variável de ambiente para que a procura ocorra 
	 * apartir do diretório "ROOT" do servidor.
	 */
	
	/*
	 * function __construct()
	 * As variáveis elas vem de acordo com a rota
	 * chamada no Slim. É necessário criar algumas 
	 * opções nos parâmetros.
	 * $opts = array() - Caso não seja passado nada a 
	 * variável $opts trará um array vazio.
	 * $tpl_dir -> Caso não ocorra nenhuma ação por 
	 * padrão será "/views/"
	 */

	public function __construct($opts = array(), $tpl_dir = "/views/"){
		/* 
		 * Mesclando dois array, um array sobrescreve o 
		 * outro, o último sempre vai sobrescrever os 
		 * anteriores. O que tiver sido recebido no 
		 * __construct vai sobrescrever o $defaults
		 ***PageAdmin, array_merge mesclará o Page +
		 * PageAdmin, ou seja iniciando com true e 
		 * concluindo com false, sendo assim na class 
		 * PageAdmin o header e footer não aparecerão.
		 */
		$this->options = array_merge($this->defaults, $opts);

		$config = array(
			"tpl_dir"	=> $_SERVER["DOCUMENT_ROOT"]."/$tpl_dir/",
			"cache_dir"	=> $_SERVER["DOCUMENT_ROOT"]."/views/cache/",
			"debug"		=> false
		);

		Tpl::configure( $config );

		/*
		 * Para que este método possa ser acessado por 
		 * outros métodos o tpl é colocado como um 
		 * atributo da class
		 */
		$this->tpl = new Tpl;

		//A variável data é onde os dados são guardados
		$this->setData($this->options["data"]);

		/* 
		 * Desenhando o template header na tela
		 * (Arquivo precisa estar na pasta /views/)
		 * Se "header" for idêntico a true desenhe o 
		 * tamplate header na tela.
		 */
		if ($this->options["header"] === true)$this->tpl->draw("header");

	}

	//Método para o foreach
	private function setData($data = array())
	{
		foreach ($data as $key => $value) {
			$this->tpl->assign($key, $value);
		}
	}



	//Método para desenhar o conteúdo na tela
	//$name - nome do template
	//$data - variáveis que serão passadas por padrão é um array vazio
	//$returnHTML - retornar HTML, ou outra coisa
	public function setTpl($name, $data = array(), $returnHTML = false)
	{
		//Devido termos o método setData (foreach) não é necessário descrever todo ele apenas chamamos o método setData
		$this->setData($data);

		//Chamando o conteúdo ($name) do template
		return $this->tpl->draw($name, $returnHTML);
		}


	//Método Mágico _destruct
	public function __destruct(){

		/* 
		 * Desenhando o template footer na tela
		 * (Arquivo precisa estar na pasta /views/)
		 * Se "footer" for idêntico a true desenhe o 
		 * tamplate header na tela.
		 */
		if ($this->options["footer"] === true)$this->tpl->draw("footer");
	}

}

?>