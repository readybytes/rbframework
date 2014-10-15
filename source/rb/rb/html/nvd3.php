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
			Rb_Html::stylesheet('rb/nvd3/nv.d3.css', $attribs);
			
			Rb_Html::script('rb/nvd3/d3.v2.js', $attribs);
			Rb_Html::script('rb/nvd3/nv.d3.js', $attribs);
		}
	}
}
