<?php
namespace Mvc;

use Mvc\View\Helper\Nav\Menu;

class View
{
	/**
	 * The controller
	 * @var \Mvc\Controller
	 */
	protected $controller;
	
	/**
	 * The generated output
	 * @var string
	 */
	protected $content;
	
	public $messagesOuterTag = 'div';
	public $messagesOuterClass = 'messages';
	public $messagesInnerTag = 'p';
	public $messagesInnerClass = 'message';
	public $messagesSeparator = PHP_EOL;
	
	public function __construct(Controller $controller)
	{
		$this->controller = $controller;
	}
	
	/**
	 * Set content to output
	 * @param string $content
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}
	
	/**
	 * Generate output
	 * @param Controller $controller
	 */
	public function render(Controller $controller)
	{
		if($template = $controller->getTemplate()){
			$templateUrl = APPLICATION_PATH . "/view/$template.phtml";
			if(file_exists($templateUrl)){
				ob_start();
				include( $templateUrl );
				$this->content = ob_get_clean();
			}else{
				throw new \Exception("Template $template not found", 404);
			}
		}
		if($layout = $controller->getLayout()){
			$layoutUrl = APPLICATION_PATH . "/view/layout/$layout.phtml";
			if(file_exists($layoutUrl)){
				include( $layoutUrl );
			}else{
				throw new \Exception("Layout $layout not found", 404);
			}
		}else{
			echo $this->content;
		}
	}
	
	public function getController()
	{
		return $this->controller;
	}
	
	/**
	 * Return null for any inexistent variable
	 * @param string $var
	 * @return NULL
	 */
	public function __get($var)
	{
		return null;
	}
	
	public function serverUrl($url)
	{
		try {
			$baseUrl = $this->controller->getApplication()->getConfig()->baseUrl;
		} catch (\Exception $e) {
			$baseUrl = '';
		}
		return $baseUrl . '/' . trim($url, '/');
	}
	
	public function removeQs()
	{
		$request = str_replace('?'.$_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']);
		return $request;
	}
	
	public function navMenu($id)
	{
		$menu = new Menu($this, $id);
		return $menu;
	}
	
	public function renderMessages($messages)
	{
		$out = array();
		foreach($messages as $type => $message){
			$out[] = "<$this->messagesOuterTag class=\"$this->messagesOuterClass $type\">
			<$this->messagesInnerTag class=\"$this->messagesInnerClass\">".
			implode("</$this->messagesInnerTag>
					<$this->messagesInnerTag>", $message).
					"</$this->messagesInnerTag></$this->messagesOuterTag>";
		}
		return implode(PHP_EOL, $out);
	}
	
}