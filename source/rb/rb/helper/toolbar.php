<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_AbstractHelperToolbar extends JToolBarHelper
{
}

/*
* @deprecated  1.1 Use JToolBarHelper instead.
*/
class Rb_HelperToolbar extends Rb_AbstractHelperToolbar
{
	
	public static function openPopup($task, $icon = '', $iconOver = '', $alt = 'PLG_SYSTEM_RBSL_TOOLBAR_NEW')
	{
		$bar = JToolBar::getInstance('toolbar');
		$bar->appendButton('Standard', 'new', $alt, $task, false, false );
	}
	
	static function addSubMenu($menu, $selMenu,$comName)
	{
		$selected 	= ($menu==$selMenu);
		$link 		= "index.php?option=".$comName."&view=$menu";
		$title 		= JText::_(strtoupper($comName).'_SUBMENU_'.strtoupper($menu));
		JSubMenuHelper::addEntry($title,$link, $selected);
	}

}