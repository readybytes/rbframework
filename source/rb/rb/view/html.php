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
	/*
	| If set to false, then view should generate and set meta data
	*/
	protected $auto_generate_metadata = true;

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
			$document	= Rb_Factory::getDocument();
			$document->addScriptDeclaration($jsScript);
		}
	}
	
	protected function generateMetadata()
	{	
        $app = Rb_Factory::getApplication();
                
		if($app->isAdmin()){
			return true;
		}
		
		$title 		= null;
		$keywords 	= null;
		$description= null; 
		$robots		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$params		= Rb_Factory::getApplication()->getParams();
		$menu 	= Rb_Factory::getApplication()->getMenu()->getActive();
		
		$title	= $params->get('page_title', $app->getCfg('sitename'));
		if(empty($title) && $menu && $menu->title){
			$title = $menu->title ;
		}
		
		if ($params->get('menu-meta_description'))		{
			$description = $params->get('menu-meta_description');
		}

		if ($params->get('menu-meta_keywords'))		{
			$keywords= $params->get('menu-meta_keywords');
		}

		if ($params->get('robots'))		{
			$robots = $params->get('robots');
		}

		Rb_HelperJoomla::addDocumentMetadata($title, $keywords, $description, $robots);
	}
	
	public function render($output, $options)
	{	
		// for html pages, genertae meta data.
		if($this->auto_generate_metadata){
			$this->generateMetadata();
		}
				
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