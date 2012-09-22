<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_AbstractHelperToolbar extends Rb_AdaptHelperToolbar
{
	public static function openPopup($task, $icon = '', $iconOver = '', $alt = 'PLG_SYSTEM_RBSL_TOOLBAR_NEW')
	{
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Standard', 'new', $alt, $task, false, false );
	}
	
	static function addSubMenu($menu, $selMenu,$comName='com_payplans')
	{
		$selected 	= ($menu==$selMenu);
		$link 		= "index.php?option=".$comName."&view=$menu";
		$title 		= Rb_Text::_('PLG_SYSTEM_RBSL_SM_'.JString::strtoupper($menu));
		JSubMenuHelper::addEntry($title,$link, $selected);
	}
	
    public static function _save()
	{
		JToolBarHelper::customX( 'save', 'save.png', 'save_f2.png', 'PLG_SYSTEM_RBSL_TOOLBAR_SAVE_CLOSE', false );
	}
	
	public static function _apply()
	{
		JToolBarHelper::customX( 'apply', 'apply.png', 'apply_f2.png', 'PLG_SYSTEM_RBSL_TOOLBAR_SAVE', false );
	}
	
	public static function _savenew()
	{
		JToolBarHelper::customX( 'savenew', 'savenew.png', 'savenew.png', 'PLG_SYSTEM_RBSL_TOOLBAR_SAVE_NEW', false );
	}
	
	public static function _delete($alt = 'Delete')
	{
		class_exists('JButtonRb_Delete', true);
		JToolBar::getInstance('toolbar')->appendButton('Rb_Delete', 'delete', 'Delete', 'remove', true, false );
	}
	
	public static function _deleteRecord($alt = 'Delete')
	{
		class_exists('JButtonRb_Delete', true);
		JToolBar::getInstance('toolbar')->appendButton('Rb_Delete', 'delete', 'Delete', 'remove', false, false );
	}
	
	public static function _cancel($task = 'cancel', $alt = 'PLG_SYSTEM_RBSL_TOOLBAR_CLOSE')
	{
		JToolBarHelper::cancel($task, $alt);
	}

	public function searchpayplans($task = '', $alt = '')
	{
		// load class
		class_exists('JButtonSearchpayplans', true);
		JToolBar::getInstance('toolbar')->appendButton( 'Searchpayplans', $alt);
	}
}