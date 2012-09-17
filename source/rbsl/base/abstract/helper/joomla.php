<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class XiAbstractHelperJoomlaBase
{}

// Include the Joomla Version Specific class, which will ad XiAbstractHelperToolbar class automatically
XiError::assert(class_exists('XiAbstractJ'.PAYPLANS_JVERSION_FAMILY.'HelperJoomla',true), XiError::ERROR);