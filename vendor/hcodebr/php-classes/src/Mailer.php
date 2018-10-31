<?php

/* Class responsavel por encaminhar o emails do forgot */

namespace Hcode;

use Rain\Tpl;

class mailer {

	const USERNAME 	= "@gmail.com";
	const PASSWORD 	= "<??>";
	const NAME_FROM = "Hcode Store";

	private $mail;

	public function __construct(
		$toAddress, //Endereco do destinatario
		$toName, 	//Qual o nome do destinatario
		$subject,	//Assunto
		$tplName,	//Nome do arquivo de Template que será encaminhado para o Rain\Tpl
		$data = array()		//Dados a serem repassados para o Template
	)
	{
		/* Criando o Template */
		$config = array(
			"tpl_dir"	=> $_SERVER["DOCUMENT_ROOT"]."/views/email/",
			"cache_dir"	=> $_SERVER["DOCUMENT_ROOT"]."/views/cache/",
			"debug"		=> false
		);
		Tpl::configure( $config );

		$tpl = new Tpl;

		/* Passando os dados para o Template */
		foreach ($data as $key => $value) {
			$tpl->assign($key, $value);
		}

		/* instanciado com true para que seja jogado os dados dentro da variavel para ser tratado mais abaixo */
		$html = $tpl->draw($tplName, true);

		/*
		 * Para que este método possa ser acessado por 
		 * outros métodos o tpl é colocado como um 
		 * atributo da class
		 */
		$this->tpl = new Tpl;



		/* O PHPMailer esta no escopo principal por este motivo eh preciso colocar a "/" contraBarra */
		$this->mail = new \PHPMailer;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		/*Este serve para:
		 * 0 = Quando estiver pronto
		 * 1 = Quanto estiver em teste
		 * 2 = Quando estiver desenvolvendo
		 */
		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		/* Aqui colocar os endereços de servidores
		 * de email (smtp). Para colocarmos mais de
		 * um basta acrescentarmos uma ", (vírgula)"
		 * e o endereço do servidor de email.
		 */
		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		/* Porta do gmail. */
		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;

		/* Definir de quem está remetendo o remente */
		//Set who the message is to be sent from
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

		/* Responder para: Podemos colocar como não responder como */
		//Set an alternative reply-to address
		//$mail->addReplyTo('replyto@example.com', 'First Last');

		/* Para quem queremos encaminhar o email.
		 * Para encaminharmos para mais de um endereço
		 * de email basta acrescentarmos uma
		 * ", (vírgula)" e passarmos o próximo email.
		 * $toAddress: Para quem queremos enviar
		 * $toName: Nome do Destinatario
		 */
		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		/* Assunto do email */
		//Set the subject line
		$this->mail->Subject = $subject;

		/* Pega o conteúdo de um arquivo, Qual o conteúdo
		 * HTML que ficará no corpo do email.
		 */
		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);

		/* Texto alternativo para o HTML acima.
		 * Ex: Supondo que o local não suporte html,
		 * a ação abaixo substituirá o HTML
		 */
		//Replace the plain text body with one created manually
		$this->mail->AltBody = 'This is a plain-text message body';

		/* Caso queiramos adicionar anexo */
		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');



		//Section 2: IMAP
		//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
		//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
		//You can use imap_getmailboxes($imapStream, '/imap/ssl') to get a list of available folders or labels, this can
		//be useful if you are trying to get this working on a non-Gmail IMAP server.
		function save_mail($mail)
		{
		    //You can change 'Sent Mail' to any other folder or tag
		    $path = "{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail";

		    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
		    $imapStream = imap_open($path, $mail->Username, $mail->Password);

		    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
		    imap_close($imapStream);

		    return $result;
		}

	}

	/* Encaminhar email no momento que desejar */
	public function send()
	{
		return $this->mail->send();
	}


}



?>