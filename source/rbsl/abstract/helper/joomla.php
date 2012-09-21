<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AbstractHelperJoomlaBase
{}

// Include the Joomla Version Specific class, which will ad Rb_AbstractHelperToolbar class automatically
Rb_Error::assert(class_exists('Rb_AbstractJ'.PAYPLANS_JVERSION_FAMILY.'HelperJoomla',true), Rb_Error::ERROR);