<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_AbstractRouteBase extends JRoute
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


// Include the Joomla Version Specific class, which will ad Rb_AbstractRoute class automatically
Rb_Error::assert(class_exists('Rb_AbstractJ'.PAYPLANS_JVERSION_FAMILY.'Route',true), Rb_Error::ERROR);
