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
 */
abstract class Rb_HtmlRbsl
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();


	public static function core($debug = null)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		Rb_Html::script('rbsl/rb.core.min.js', false, true, false, false, $debug);

		self::$loaded[__METHOD__] = true;

		return;
	}


	public static function lib($debug = null)
	{
		// Only load once
		if (!empty(self::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		Rb_Html::_('script', 'rbsl/rb.lib.min.js', false, true, false, false, $debug);


		self::$loaded[__METHOD__] = true;

		return;
	}
}
