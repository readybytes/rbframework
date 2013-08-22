<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );


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
	
	protected function _prepareDocument()
	{	
		if(Rb_Factory::getApplication()->isAdmin()){
			return true;
		}
		
		$app		= Rb_Factory::getApplication();
		$params 	= $app->getParams();
		$document 	= Rb_Factory::getDocument();		
		$menus		= $app->getMenu();
		$title		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu){
			$params->def('page_heading', $params->def('page_title', $menu->title));
		}
		
		$title = $params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Rb_Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Rb_Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
		$document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}
	}
	
	public function render($output, $options)
	{	
		// for html pages, genertae meta data.
		$this->_prepareDocument();
				
		ob_start();
		echo '<div id="'.$this->_component->getNameSmall().'" class="rb-wrap '.$this->_component->getNameSmall().'-wrap">
				<div class="'.$this->_component->getNameSmall().'">
					<div class="'.$this->_component->getPrefixCss().'-component clearfix">
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