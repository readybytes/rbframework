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
 */
abstract class Rb_HtmlNvd3
{
	public static function load($attribs = array())
	{
		static $loaded = false;
		
		if($loaded === false){
			$loaded = true;
			Rb_Html::stylesheet('nvd3/nv.d3.css', $attribs, false);
			
			Rb_Html::script('nvd3/d3.v2.js', $attribs, false);
			Rb_Html::script('nvd3/nv.d3.js', $attribs, false);
		}
	}
}
