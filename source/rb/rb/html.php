<?php
/**
* @copyright	Copyright (C) 2009 - 2011 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_Html extends Rb_AdaptHtml
{	
	public static function stylesheet($file, $attribs = array(), $relative = true, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		// We don't know file is loaded or not by joomla so we passed 3rd parameter as true 
		// and we get only file paths & load manually by us.
		return parent::stylesheet($file, $attribs, $relative, $path_only, $detect_browser, $detect_debug);
		
	}
		
	public static function script($file, $framework = false, $relative = true, $path_only = false, $detect_browser = true, $detect_debug = true)
	{
		// We don't know file is loaded or not by joomla so we passed 3rd parameter as true 
		// and we get only file paths & load manually by us.
		return parent::script($file, $framework, $relative, $path_only, $detect_browser, $detect_debug);
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
						JText::alt($label, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $label)), 
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