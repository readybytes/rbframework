<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_AbstractJ16HelperToolbar extends Rb_AbstractHelperToolbarBase
{
	public static function save()
	{
		parent::_save();
	}
	
	public static function apply()
	{
		parent::_apply();
	}
	
	public static function savenew()
	{
		parent::_savenew();
	}
	
	public static function cancel($task = 'cancel', $alt = 'Close')
	{
		parent::_cancel();
	}
    
	public static function delete($list='true', $alt='')
	{
		parent::_delete($list);
	}
	
	public function deleteRecord($list='false', $alt='')
	{
		parent::_deleteRecord($list);
	}
	
	public function searchpayplans()
	{
		parent::searchpayplans();
	}
}

class Rb_AbstractHelperToolbar extends Rb_AbstractJ16HelperToolbar{}