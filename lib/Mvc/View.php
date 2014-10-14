<?php
namespace Mvc;

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
			ob_start();
			include( APPLICATION_PATH . "/view/$template.phtml" );
			$this->content = ob_get_clean();
		}
		if($layout = $controller->getLayout()){
			include( APPLICATION_PATH . "/view/layout/$layout.phtml" );
		}else{
			echo $this->content;
		}
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
	
}