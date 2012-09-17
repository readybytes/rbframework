<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiSession
{
	public $session = null;
	
	public function __construct()
	{
		$this->session = JFactory::getSession();
	}
	
	public function set($name, $value, $namespace = 'payplans')
	{
		$this->session->set($name, $value, $namespace);
	}
	
	public function get($name, $default=null, $namespace = 'payplans')
	{
		return $this->session->get($name, $default, $namespace);
	}
	
	public function clear($name, $namespace = 'payplans')
	{
		return $this->session->clear($name, $namespace);
	}

	public function getId()
	{
		return $this->session->getId();
	}
}