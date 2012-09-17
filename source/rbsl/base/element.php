<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Elements
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiElement extends JElement
{
	public static function hasAttrib($node, $attrib)
	{
		// for php 5.3 specific
		if(is_object($node->_attributes) && isset($node->_attributes->$attrib))
			return true;

		if(isset($node->_attributes[$attrib]))
			return true;

		return false;
	}
	
	public static function getAttrib($node, $attrib, $default = false)
	{
		if(!PAYPLANS_JVERSION_15){
			// For Joomla 1.7
			$attributes = (array)$node->attributes();
			if(isset($attributes['@attributes']) && isset($attributes['@attributes'][$attrib])){
				return $attributes['@attributes'][$attrib];
			}
		}
		
		// for php 5.3 specific
		if(is_object($node->_attributes) && isset($node->_attributes->$attrib))
			return $node->_attributes->$attrib;
		//for j2.5 and j1.5
		if(isset($node->_attributes[$attrib]))
			return $node->_attributes[$attrib];
			
		// defaults
		return $default;
	}
	
	//Collect all attributes
	public static function getAttributes($node)
	{
		// for php 5.3 specific
		if(is_object($node->_attributes))
			return (array)$node->_attributes;

		return $node->_attributes;
	}
	
	
}