<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_AbstractRoute extends Rb_AdaptRoute
{
	static protected $_prefix = false;
	/*
	 * just make default value of xhtml=false
	 */
	static function _route($url, $xhtml = false, $ssl = null)
	{		
		$oldUrl = $url;
		
		if(Rb_Factory::getApplication()->isAdmin() == false
			&& JString::strpos($oldUrl, 'view=payment') !== false 
			&& Rb_Factory::getConfig()->https ){
				return parent::_($url, $xhtml, true);
		}
		
		return parent::_($url, $xhtml);
	}
}