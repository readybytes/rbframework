<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/

if(defined('_JEXEC')===false) die('Restricted access' );

defined('JPATH_PLATFORM') or die;

/**
 * @author Gaurav Jain
 * @deprecated Since 1.1 Use Rb_HelperTemplate::loadMedia() instead
 */
abstract class Rb_HtmlNvd3
{
	/**
	 * @deprecated
	 */
	public static function load($attribs = array())
	{
		static $loaded = false;
		
		if($loaded === false){
			$loaded = true;
			Rb_Html::stylesheet('plg_system_rbsl/nvd3/nv.d3.min.css', $attribs);
			
			Rb_Html::script('plg_system_rbsl/nvd3/d3.min.js', $attribs);
			Rb_Html::script('plg_system_rbsl/nvd3/nv.d3.min.js', $attribs);
		}
	}
}
