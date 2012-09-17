<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiAbstractJ16Route extends XiAbstractRouteBase
{
	static public function _($url, $xhtml = false, $ssl = null)
	{
		return parent::_route($url, $xhtml, $ssl);
	}
}

class XiAbstractRoute extends XiAbstractJ16Route
{}