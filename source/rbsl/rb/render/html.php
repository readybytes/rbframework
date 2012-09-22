<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_RenderHtml extends Rb_Render
{
	protected function _injectScripts(Rb_View $view)
	{
		// load html assets required
		$view->loadTemplate('assets');
		
		//Get dynamic java script
		$jsScript	=	$view->getDynamicJavaScript();
		if($jsScript){
			$document	=& Rb_Factory::getDocument();
			$document->addScriptDeclaration($jsScript);
		}
		
	}
	
	protected function _render(Rb_View $view, $html, $options)
	{
		$this->_injectScripts($view);
		// inject security tokens and echo string
		echo $this->_injectTokens($html);
		return true;
	}
	
}