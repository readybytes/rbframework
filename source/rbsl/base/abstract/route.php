<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class XiAbstractRouteBase extends JRoute
{
	static protected $_prefix = false;
	/*
	 * just make default value of xhtml=false
	 */
	static function _route($url, $xhtml = false, $ssl = null)
	{		
		$oldUrl = $url;
		
		if(XiFactory::getApplication()->isAdmin() == false
			&& JString::strpos($oldUrl, 'view=payment') !== false 
			&& XiFactory::getConfig()->https ){
				return parent::_($url, $xhtml, true);
		}
		
		return parent::_($url, $xhtml);
	}
}


// Include the Joomla Version Specific class, which will ad XiAbstractRoute class automatically
XiError::assert(class_exists('XiAbstractJ'.PAYPLANS_JVERSION_FAMILY.'Route',true), XiError::ERROR);
