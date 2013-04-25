<?php

/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package 		RbEcommerce
* @subpackage	Front-end
* @contact		team@readybytes.in
*/

// no direct access
if(!defined( '_JEXEC' )){
	die( 'Restricted access' );
}

/** 
 * Processor Helper
 * @author Gaurav Jain
 */
class RbEcommerceHelperProcessor extends JObject
{
	/** 
	 * Holds list of avaialable processor
	 * @var Array
	 */
	private $__processor_list = array();
	
	/**
	 * Holds the instances of processors
	 * @var Array
	 */
	private $__instances = array();
	
	/**
	 * Load all RbEcommerce Processors by triggering an event
	 * 
	 * @return true
	 */
	public function load()
	{
		// load rbecommerceprocessor plugin
		$data = array();
		$type = 'rbecommerceprocessor';
		Rb_HelperPlugin::loadPlugins($type);
	}
	
	public function push($type, $data)
	{
		$this->__processor_list[$type] = $data;
	} 
	
	public function getInstance($name, $config = null)
	{
		if(isset($this->__instances[$name])){
			if($config != null){
				$this->__instances[$name]->setConfig($config);
			}
			
			return $this->__instances[$name];
		} 
		
		// load all processors			
		$this->load();
		
		if(!isset($this->__processor_list[$name])){
			// XITODO : raise exception
		}
		
		$classname 	=  $this->__processor_list[$name]['class'];
		$config 	= ($config === null) ? array() : $config;		
		
		$this->__instances[$name] = new $classname($config);
		
		return $this->__instances[$name];
	}
	
	/**
	 * Get list of all avaialable processor
	 * 
	 * @return array Array of all processors
	 */
	public function getList()
	{
		$this->load();
		
		return $this->__processor_list;
	}
}