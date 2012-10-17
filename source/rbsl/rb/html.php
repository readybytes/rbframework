<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

class Rb_Html extends JHtml
{	
	public static function _($key)
	{
		list($key, $prefix, $file, $func) = self::extract($key);
		if(strtolower($prefix) == 'wehrmhtml'){
			$className = $prefix . ucfirst($file);
			class_exists($className, true);
		}
		
		$args = func_get_args();
		return call_user_func_array(array('JHtml', '_'), $args);
	}
	
	public static function stylesheet($file, $attribs = array(), $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		if(JFile::exists($file)){
			$file = Rb_HelperTemplate::mediaURI($file,false, false);
		}elseif(JFile::exists(RB_PATH_MEDIA.'/'.$file)){
			$file = Rb_HelperTemplate::mediaURI(RB_PATH_MEDIA,true, false).$file;
		}
		
		return parent::script($file, $attribs, $relative, $path_only, $detect_browser, $detect_debug);
	}

	public static function script($file, $framework = false, $relative = false, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		if(JFile::exists($file)){
			$file = Rb_HelperTemplate::mediaURI($file,false, false);
		}elseif(JFile::exists(RB_PATH_MEDIA.'/'.$file)){
			$file = Rb_HelperTemplate::mediaURI(RB_PATH_MEDIA,true, true).$file;
		}
		
		return parent::script($file, $framework, $relative, $path_only, $detect_browser, $detect_debug);
	}

	static function buildOptions($data, $attributes = null)
	{
		// Initialize variables.
		$options = array();

		foreach ($data as $value => $label)
		{
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