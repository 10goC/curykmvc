<?php
return array(
	'libraries' => array(
		'Mvc',
		'Abm'
	),
	'site' => array(
		'title' => 'Site Title',
		'description' => 'Site Description',
	),
	'notifications' => array(
		'smtp' => array(
			'ssl' => '',
			'port' => '',
			'auth' => '',
			'host' => '',
			'username' => '',
			'password' => '',
		),
		'mail' => array(
			'defaultFrom'     => '',
			'defaultFromName' => '',
			'defaultTo'       => '',
			'defaultSubject'  => '',
		),
	),
);