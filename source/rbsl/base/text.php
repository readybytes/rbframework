<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiText extends JText
{
	public static function autoLoadJS($regex='/_JS_/')
	{
		$allStrings = XiLanguage::getStrings(JFactory::getLanguage());

		$strings = array();
		foreach($allStrings as $key=>$value){
			if(preg_match($regex, $key)){
				$strings[$key] = $value; 
			}
		}
		
		 
		$document = JFactory::getDocument();
		$document->addScriptDeclaration(' var xi_strings = '.json_encode($strings).';');	
	}
}