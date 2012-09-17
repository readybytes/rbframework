<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

require_once JPATH_ADMINISTRATOR.DS."includes".DS."toolbar.php";

class XiAbstractHelperToolbarBase extends JToolBarHelper
{
	public static function openPopup($task, $icon = '', $iconOver = '', $alt = 'COM_PAYPLANS_TOOLBAR_NEW')
	{
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Standard', 'new', $alt, $task, false, false );
	}
	
	static function addSubMenu($menu, $selMenu,$comName='com_payplans')
	{
		$selected 	= ($menu==$selMenu);
		$link 		= "index.php?option=".$comName."&view=$menu";
		$title 		= XiText::_('COM_PAYPLANS_SM_'.JString::strtoupper($menu));
		JSubMenuHelper::addEntry($title,$link, $selected);
	}
	
    public static function _save()
	{
		JToolBarHelper::customX( 'save', 'save.png', 'save_f2.png', 'COM_PAYPLANS_TOOLBAR_SAVE_CLOSE', false );
	}
	
	public static function _apply()
	{
		JToolBarHelper::customX( 'apply', 'apply.png', 'apply_f2.png', 'COM_PAYPLANS_TOOLBAR_SAVE', false );
	}
	
	public static function _savenew()
	{
		JToolBarHelper::customX( 'savenew', 'savenew.png', 'savenew.png', 'COM_PAYPLANS_TOOLBAR_SAVE_NEW', false );
	}
	
	public static function _delete($alt = 'Delete')
	{
		class_exists('JButtonXiDelete', true);
		JToolBar::getInstance('toolbar')->appendButton('XiDelete', 'delete', 'Delete', 'remove', true, false );
	}
	
	public static function _deleteRecord($alt = 'Delete')
	{
		class_exists('JButtonXiDelete', true);
		JToolBar::getInstance('toolbar')->appendButton('XiDelete', 'delete', 'Delete', 'remove', false, false );
	}
	
	public static function _cancel($task = 'cancel', $alt = 'COM_PAYPLANS_TOOLBAR_CLOSE')
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


// Include the Joomla Version Specific class, which will ad XiAbstractHelperToolbar class automatically
XiError::assert(class_exists('XiAbstractJ'.PAYPLANS_JVERSION_FAMILY.'HelperToolbar',true), XiError::ERROR);