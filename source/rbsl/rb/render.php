<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


class Rb_Render
{
	public $headerFooters = true;
	
	public function __construct()
	{
		$this->headerFooters = JRequest::getBool('headerFooter', $this->headerFooters);
	}
	
	final public function render(Rb_View $view, $data, $options)
	{
		Rb_Error::assert($this);
		
		ob_start();
		if($this->headerFooters){
			echo '
				<div id="payplans" class="payplans-warp">
					<div class="payplans">
						<div class="pp-component clearfix">
				';
			echo $data['header'];
		}
		
		echo $data['output'];
		
		if($this->headerFooters){
			echo $data['footer'];
			echo '</div></div></div>';
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		return $this->_render($view, $html, $options);
	}
	
	protected function _render(Rb_View $view, $html, $options)
	{
		echo $html;
		return true;
	}
	
	static function getRenderer()
	{
		$format	= JString::ucfirst(JString::strtolower(JRequest::getCmd('format',	'html')));
		if(JRequest::getBool('isAjax',false)!==false){
			$format	= 'Ajax';
		}

		// IMP : we do not use format, because it creates problem in JDocument
		if(JRequest::getBool('isJSON',false) !== false){
			$format	= 'Json';
		}

		$className = 'Rb_Render'.$format; 
		if(class_exists($className, true)===false){
			return Rb_Error::raiseError("Class $className not found");
		}
		
		return new $className();
	}
	
	protected function _injectTokens($output)
	{
		// Form security via token injection
		if(JString::stristr($output, "</form>")){
			$output = preg_replace('#</form>#', PayplansHtml::_('form.token').'</form>', $output);
		}
		
		return $output;
	}
}