<?php
namespace Application\Controller;

use Mvc\Controller;

class IndexController extends Controller
{
	public function indexAction()
	{
		$lang = $this->getParam('lang', '');
		$chapters = [];
		$docs = [
			'start',
			'configuration',
			'db',
			// 'abm'
		];
		$titles = [
			'es_ES' => 'Documentación',
			''      => 'Documentation',
		];
		foreach ($docs as $chNum => $doc) {
			$chapter = file_get_contents(str_replace('//', '/', APPLICATION_PATH . "/view/docs/$lang/$doc.phtml"));
			$chapter = str_replace('{{chapter}}', $chNum +1, $chapter);
			$chapters[] = $chapter;
		}
		$this->view->title = $titles[$lang];
		$this->view->chapters = $chapters;
	}
}