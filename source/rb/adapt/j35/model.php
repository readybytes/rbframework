<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport( 'joomla.application.component.model' );

class Rb_AdaptJ35Model extends JModelLegacy
{
	protected	$_name		= null;
}

class Rb_AdaptModel extends Rb_AdaptJ35Model
{}