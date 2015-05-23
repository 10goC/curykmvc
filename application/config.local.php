<?php
return array(
	'db' => array(
		'host' => '',
		'user' => '',
		'pass' => '',
		'name' => '',
	),
	'basepath' => '/lib/mvc/trunk',
	'notifications' => array(
		'smtp' => array(
			'secure'   => 'ssl',
			'port'     => '465',
			'host'     => 'smtp.googlemail.com',
			'sender'   => 'curykdiego@gmail.com',
			'username' => 'curykdiego@gmail.com',
			'password' => '',
		),
		'mail' => array(
			'defaultFrom'     => '',
			'defaultFromName' => '',
			'defaultTo'       => 'curykdiego@gmail.com',
			'defaultSubject'  => 'System message',
			'charset'         => 'ISO-8859-1',
		),
	),
);