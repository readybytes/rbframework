<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Attributes extends JObject
{	
	public $optKey 		= 'value';
	public $optText 	= 'text';
			 	 
	public $idtag 		= false;
	public $class	 	= false;
	public $size		= false;
			 
	public $readonly	= false;
	public $disabled	= false;
	public $multiple	= false;
			 
	public $scripts		= false;
	public $translate 	= false;
	
	/**
	 * Magic method to convert the attributes to a string 
	 */
	public function __toString()
	{
		$attr = '';
		
		if ($this->class){
			$attr .= ' class="'. (string) $this->class . '"';	
		}	

		// To avoid user's confusion, readonly="true" should imply disabled="true".
		if ($this->readonly || $this->disabled)
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->size ? ' size="' . (int) $this->size. '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';

		// Initialize JavaScript field attributes.
		if($this->scripts){
			$attr .= isset($this->scripts->onchange) ? ' onchange="' . (string) $this->script->onchange . '"' : '';
		}

		return $attr;
	}
	
	public function toArray()
	{
		return $this->getProperties();
	}
	
	
}