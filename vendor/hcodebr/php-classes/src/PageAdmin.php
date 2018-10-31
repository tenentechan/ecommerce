<?php

/* Class criada para que não haja conflito com a class
 * Page do usuário
 */
namespace Hcode;

/* class PageAdmin extends de Page, tudo que for public
 * e protect poderá ser acessado pela class PageAdmin
 */
class PageAdmin extends Page {

	/* Method magic. Este method é necessário para que
	 * seja construído o layout do ADMIN com os  
	 * templates que estão na pasta ADMIN e não na pasta
	 * SITE. Evitando conflito
	 */
	public function __construct($opts = array(), $tpl_dir = "/views/admin/")
	{
		/* É necessário fazer tudo que o method
		 * __construct da class Page faz, para não
		 * precisarmos tudo novamente utilizamos a regra
		 * abaixo. Passamo os parametros que estao no
		 * __construct, se não passarmos nada por padrao
		 * sera "/views/admin/"
		 */
		parent::__construct($opts, $tpl_dir);

	}




}



?>