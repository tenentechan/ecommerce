<?php


namespace Hcode; //caminho do namespace Model


class Model
{
	/*
	 * $values: guardará todos os valores que teremos dentro 
	 * do objeto.
	 * Pra isto precisamos saber quando e qual method é 
	 * chamado. set ou get?
	 */
	private $values = [];

	/* 
	 * Identificando se o method é set ou get
	 * Se for um set vamos trazer a informação e retornar;
	 * Se for um get é preciso atribuir o valor da
	 * informação que foi passada.
	 */
	public function __call($name, $args)
	{
		/*
		 * substr: SubString
		 * $name: Variável get ou set
		 * 0: Conte a partir da posição zero
		 * 3: Traga 0, 1, 2, ou seja, conta o 0 (zero)
		 */
		$method = substr($name, 0, 3);

		/*
		 * substr: SubString
		 * $name: Variável get ou set
		 * 3: Conte a partir da posição 3
		 * strleng: Conte ate o fim
		 */
		$fieldName = substr($name, 3, strlen($name));
		
		switch ($method)
		{
			/*
			 * Caso seja get,
			 * retorne a variável $values com o que foi
			 * encontrado em $fieldName
			 */
			case "get": 
				return (isset($this->values[$fieldName])) ?
				$this->values[$fieldName] : NULL;
			break;

			/*
			 * Caso seja set,
			 * retorne a variável $values com o que foi
			 * encontrado em $fieldName e aplique $args[0] 
			 * na posição 0 (zero) primerio argumento, ou 
			 * seja, trará o valor:
			 * Ex: Se for trago idusuario o args será o 
			 * conteúdo de idusuario.
			 */
			case "set":
				$this->values[$fieldName] = $args[0];
			break;

		}
	}

	/*
	 * Este method é para setar tudo
	 * Tudo que for criado dinamicamente no PHP é preciso 
	 * colocar entre chaves.
	 * Nome do method 	=>>>
	 * "set" : esta variavel
	 * . 	 : concatena com o valor que tem na variável
	 * $key  :
	 * Parametros 		=>>>
	 * $value
	 * 
	 */
	public function setData($data = array())
	{
		foreach ($data as $key => $value)
		{
			$this->{"set".$key}($value);
		}
	}

	/* 
	 * Função para retornar o atributo.
	 * Não podemos acessar diretamente o atributo por se 
	 * tratar de private.
	 * Desta maneira conseguimos colocar os dados dentro da 
	 * SESSION
	 *
	 */
	public function getValues()
	{
	return $this->values;
	}


}

?>