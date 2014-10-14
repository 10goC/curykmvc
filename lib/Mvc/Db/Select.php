<?php
namespace Mvc\Db;

class Select
{
	protected $table;
	
	public function _construct(Table $table)
	{
		$this->table = $table;
	}
	
	public function assemble()
	{
		
	}
}