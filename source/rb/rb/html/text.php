<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_HtmlText
{
	function filter($name, $view, Array $filters = array(), $prefix='filter_payplans')
	{
		$elementName  = $prefix.'_'.$view.'_'.$name;
		$elementValue = @array_shift($filters[$name]);
		
		$html  = '<input id="'.$elementName.'" ' 
						.'name="'.$elementName.'[]" ' 
						.'value="'.$elementValue.'" '
						.'size="25" />';
						
		return $html;
	}
}
