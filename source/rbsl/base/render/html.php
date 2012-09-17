<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class XiRenderHtml extends XiRender
{
	protected function _injectScripts(XiView $view)
	{
		// load html assets required
		$view->loadTemplate('assets');
		
		//Get dynamic java script
		$jsScript	=	$view->getDynamicJavaScript();
		if($jsScript){
			$document	=& XiFactory::getDocument();
			$document->addScriptDeclaration($jsScript);
		}
		
	}
	
	protected function _render(XiView $view, $html, $options)
	{
		$this->_injectScripts($view);
		// inject security tokens and echo string
		echo $this->_injectTokens($html);
		return true;
	}
	
}