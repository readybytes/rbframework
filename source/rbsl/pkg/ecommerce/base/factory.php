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
 * Factory
 * @author Gaurav Jain
 */
class RbEcommerceFactory extends Rb_Factory
{
	static function getInstance($name, $type='', $prefix='RbEcommerce', $refresh=false)
	{
		return parent::getInstance($name, $type, $prefix, $refresh);
	}
	
	static function getHelper($name = null, $type = 'Helper', $prefix = 'RbEcommerce')
	{
		static $helper = null;
		
		if($helper === null){						
			$path 	= RBECOMMERCE_PATH_CORE.'/helpers';
			$files 	= JFolder::files($path, '.php');
			$helper = new stdClass();
			
			foreach($files as $file){
				$file = preg_replace('#.php#', '', $file);
				$class_name 	= $prefix.$type.$file;
				$helper->$file 	= new $class_name();
			}
		}
		
		if($name != null){
			if(isset($helper->$name)){
				return $helper->$name;
			}
			
			//XITODO : raise error
		}
		
		return $helper;	
	} 
}
