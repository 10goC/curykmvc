<?php
/** Comlei Mvc Framework */

namespace Mvc;

use Mvc\Mail\Transport\PHPMailer\Transport;

/** Base class for sending emails */
class Mail 
{
	/**
	 * The current controller
	 * @var Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * Email settings
	 * @var array
	 */
	protected $config;
	
	/**
	 * Email transport
	 * @var Transport
	 */
	protected $transport;
	
	/**
	 * The HTML formatted body
	 * @var string
	 */
	protected $htmlBody;
	
	/**
	 * The plain text body
	 * @var string
	 */
	protected $textBody;
	
	/**
	 * Default email address to send messages
	 * @var string
	 */
	public $defaultFrom;
	
	/**
	 * Default name to use when sending messages
	 * @var string
	 */
	public $defaultFromName;
	
	/**
	 * Default email address to receive messages
	 * @var string
	 */
	public $defaultTo;
	
	/**
	 * Default subject for emails
	 * @var string
	 */
	public $defaultSubject;
	
	/**
	 * Initialize configurations
	 * @return array
	 */
	protected function initConfig()
	{
		if ($this->config) return $this->config;
		$notificationsConfig = $this->controller->getApplication()->getConfig()->notifications->toArray();
		$smtp = $notificationsConfig['smtp'];
		$mail = $notificationsConfig['mail'];
		
		$this->defaultFrom     = $mail['defaultFrom'];
		$this->defaultFromName = $mail['defaultFromName'];
		$this->defaultTo       = $mail['defaultTo'];
		$this->defaultSubject  = $mail['defaultSubject'];
		
		$this->transport       = new Transport(true);
		$this->transport->CharSet = $mail['charset'];
		if ($smtp['host']) {
			$this->transport->IsSMTP();
			$this->transport->Host = $smtp['host'];
		}
		// else $this->transport->IsSendmail();
		if ($smtp['sender']) $this->transport->Sender = $smtp['sender'];
		if ($smtp['username'] && $smtp['password']) {
			$this->transport->SMTPAuth = true;
			$this->transport->Username = $smtp['username'];
			$this->transport->Password = $smtp['password'];
		}
		if ($smtp['secure']) $this->transport->SMTPSecure = $smtp['secure'];
		if ($smtp['port']) $this->transport->Port = $smtp['port'];
	}
	
	/**
	 * Setup email variables
	 * @param Controller $controller
	 * @param array $args
	 * @return \Mvc\Mail
	 */
	public function __construct(Controller $controller, array $args)
	{
		$this->controller = $controller;
		$this->initConfig();
		if (!empty($args['htmlBody'])) $this->htmlBody = utf8_decode($args['htmlBody']);
		if (!empty($args['textBody'])) $this->textBody = utf8_decode($args['textBody']);
		$from      = empty($args['from'])      ? utf8_decode($this->defaultFrom)     : utf8_decode($args['from']);
		$fromName  = empty($args['fromName'])  ? utf8_decode($this->defaultFromName) : utf8_decode($args['fromName']);
		$recipient = empty($args['recipient']) ? utf8_decode($this->defaultTo)       : utf8_decode($args['recipient']);
		$subject   = empty($args['subject'])   ? utf8_decode($this->defaultSubject)  : utf8_decode($args['subject']);
	
		$this->transport->setFrom($from, $fromName);
		$this->transport->addAddress($recipient);
		$this->transport->Subject = $subject;
		return $this;
	}
	
	/**
	 * Send the message
	 * @return boolean
	 */
	public function send()
	{
		if ($this->htmlBody) {
			$this->transport->Body = $this->htmlBody;
			$this->transport->isHTML(true);
			if ($this->textBody) {
				$this->transport->AltBody = $this->textBody;
			}
		} else {
			$this->transport->Body = $this->textBody;
			$this->transport->isHTML(false);
		}
		return $this->transport->Send();
		try {
			$this->transport->Send();
		} catch (Exception $e) {
			return false;
		} 
		return true;
	}
	
}

