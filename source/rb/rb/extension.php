<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Extension
{	

	static public $cache = array();
	static function getInstance($name, $config= array())
	{
		// create
		if(!isset(self::$cache[strtolower($name)])){
			self::$cache[strtolower($name)] = new Rb_Extension($name, $config);
		}

		return self::$cache[strtolower($name)];
	}
	
	
	protected function __construct($name, $config= array())
	{
		if(count($config)>0){
			foreach($config as $key => $value){
				$this->$key = $value;
			}
		}
		
		if(!isset($this->prefix_class)){
			$this->prefix_class 	= ucfirst($name);
		}
		
		if(!isset($this->prefix_text)){
			$this->prefix_text		= 'COM_'.strtoupper($name);
		}
		
		if(!isset($this->name_caps)){
			$this->name_caps		= strtoupper($name);
		}
		
		if(!isset($this->name_small)){
			$this->name_small		= strtolower($name);
		}
		
		if(!isset($this->name_com)){
			$this->name_com			= 'com_'.strtolower($name);
		}
		
		if(!isset($this->name_css)){
			// get first two chars
			$this->name_css			= substr(strtolower($name),0,2);
		}
	}
	
	public function getNameCaps()
	{
		return $this->name_caps;
	}
	
	public function getNameSmall()
	{
		return $this->name_small;
	}
	
	public function getNameCom()
	{
		return $this->name_com;
	}
	
	public function getPrefixClass()
	{
		return $this->prefix_class;
	}
	
	public function getPrefixText()
	{
		return $this->prefix_text;
	}
	
	public function getPrefixCss()
	{
		return $this->prefix_css;
	}
}
