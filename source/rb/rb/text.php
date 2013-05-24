<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


class Rb_Text extends JText
{
	public static function autoLoadJS($regex='/_JS_/')
	{
		$allStrings = Rb_Language::getStrings(JFactory::getLanguage());

		$strings = array();
		foreach($allStrings as $key=>$value){
			if(preg_match($regex, $key)){
				$strings[$key] = $value; 
			}
		}
		
		$document = JFactory::getDocument();
		$document->addScriptDeclaration(' var rb_strings = '.json_encode($strings).';');	
	}
}