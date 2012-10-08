<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_ViewHtml extends Rb_View
{
	protected function _injectTokens($output)
	{
		// Form security via token injection
		if(JString::stristr($output, "</form>")){
			$output = preg_replace('#</form>#', Rb_Html::_('form.token').'</form>', $output);
		}
		
		return $output;
	}
	
	protected function _injectScripts()
	{
		// load html assets required
		$this->loadTemplate('assets');
		
		//Get dynamic java script
		$jsScript	=	$this->getDynamicJavaScript();
		if($jsScript){
			$document	=& Rb_Factory::getDocument();
			$document->addScriptDeclaration($jsScript);
		}
	}
	
	public function render($output, $options)
	{	
		ob_start();
		echo '<div id="{$this->_component}" class="{$this->_component}-warp">
				<div class="{$this->_component}">
					<div class="pp-component clearfix">
			 ';
		echo $this->_showHeader();
		echo $output;
		
		echo $this->_showFooter();
		echo '</div></div></div>';
		$html = ob_get_contents();
		ob_end_clean();
		
		$this->_injectScripts();
		echo $this->_injectTokens($html);
		return true;
	}
}