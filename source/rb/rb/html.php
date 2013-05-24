<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Html extends JHtml
{	
	public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = true, $detect_browser = true, $detect_debug = true)
	{
		// We don't know file is loaded or not by joomla so we passed 3rd parameter as true 
		// and we get only file paths & load manually by us.
		$paths = parent::stylesheet($file, $attribs, $relative, $path_only, $detect_browser, $detect_debug);
		
		if(!$paths){
			$paths = self::_refinePath($file);  
		}
		
		if($paths){
			$paths =  is_array($paths) ? $paths : array($paths);
			$document = JFactory::getDocument();
			foreach ($paths as $include){
				$document->addStylesheet($include, 'text/css', null, $attribs);
			}
		}
		
}

	public function _refinePath($file)
	{
		if(JFile::exists($file)){
			return  Rb_HelperTemplate::mediaURI($file,false);
		}elseif(JFile::exists(RB_PATH_MEDIA.'/'.$file)){
			return Rb_HelperTemplate::mediaURI(RB_PATH_MEDIA.'/'.$file,false);
		}
		return false;
	}
		
	public static function script($file, $framework = false, $relative = true, $path_only = true, $detect_browser = true, $detect_debug = true)
	{
		// We don't know file is loaded or not by joomla so we passed 3rd parameter as true 
		// and we get only file paths & load manually by us.
		$paths = parent::script($file, $framework, $relative, $path_only, $detect_browser, $detect_debug);
		if(!$paths){
			$paths = self::_refinePath($file);  
		}
		
		if($paths){
			$paths =  is_array($paths) ? $paths : array($paths);
			$document = JFactory::getDocument();
			foreach ($paths as $include){
				$document->addScript($include);
			}
		}
	}

	/**
	 * 
	 * @param unknown_type $data
	 * @param unknown_type $attributes array('disabled', 'class', 'onclick')
	 */
	static function buildOptions($data, $attributes = null)
	{
		// Initialize variables.
		$options = array();

		foreach ($data as $value => $label)
		{
			if(is_object($label)){
				if(isset($label->title)){
					$label= $label->title;
				}
			}
			
			$option = isset($attributes[$value]) ? $attributes[$value] : null;
			
			// Create a new option object based on the <option /> element.
			// option($value, $text = '', $optKey = 'value', $optText = 'text', $disable = false)
			$tmp = JHtml::_(
						'select.option', (string) $value,
						Rb_Text::alt($label, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $label)), 
						'value', 'text',((string) @$option->disabled == 'true')
					);

			// Set some option attributes.
			$tmp->class = (string) @$option->class;

			// Set some JavaScript option attributes.
			$tmp->onclick = (string) @$option->onclick;

			// Add the option object to the result set.
			$options[] = $tmp;
		}
		reset($options);
	
		return $options;
	}


}