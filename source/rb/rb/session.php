<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Session
{
	public $session 		= null;
	protected $namespace 	= 'rb';
	
	public function __construct($ns)
	{
		$this->session		= JFactory::getSession();
		$this->namespace	= $ns;
	}
	
	public function set($name, $value, $namespace = null)
	{
		if(!$namespace){
			$namespace = $this->namespace;
		}
		$this->session->set($name, $value, $namespace);
	}
	
	public function get($name, $default=null, $namespace = null)
	{
		if(!$namespace){
			$namespace = $this->namespace;
		}
		
		return $this->session->get($name, $default, $namespace);
	}
	
	public function clear($name, $namespace = null)
	{
		if(!$namespace){
			$namespace = $this->namespace;
		}
		return $this->session->clear($name, $namespace);
	}

	public function getId()
	{
		return $this->session->getId();
	}
}