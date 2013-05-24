<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

abstract class Rb_Route extends JRoute
{
	static protected $_prefix = false;
	/*
	 * just make default value of xhtml=false
	 */
	
	public static function _($url, $xhtml = false, $ssl = null)
	{		
		return parent::_($url, $xhtml, $ssl);
	}
}