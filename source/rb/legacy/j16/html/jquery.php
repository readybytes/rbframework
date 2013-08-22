<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @license		GNU/GPL, see LICENSE.php
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

if(defined('_JEXEC')===false) die('Restricted access' );

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for jQuery JavaScript behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlJquery
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the jQuery JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery is included for easier debugging.
	 *
	 * @param   boolean  $noConflict  True to load jQuery in noConflict mode [optional]
	 * @param   mixed    $debug       Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function framework($noConflict = true, $debug = null)
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

		Rb_Html::script('jui/js/jquery.js', false, true, false, false, $debug);

		// Check if we are loading in noConflict
		if ($noConflict)
		{
			Rb_Html::script('jui/js/jquery-noconflict.js', false, true, false, false, false);
		}

		self::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Method to load the jQuery UI JavaScript framework into the document head
	 *
	 * If debugging mode is on an uncompressed version of jQuery UI is included for easier debugging.
	 *
	 * @param   array  $components  The jQuery UI components to load [optional]
	 * @param   mixed  $debug       Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function ui(array $components = array('core'), $debug = null)
	{
		// Set an array containing the supported jQuery UI components handled by this method
		$supported = array('core', 'sortable');

		// Include jQuery
		self::framework();

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		// Load each of the requested components
		foreach ($components as $component)
		{
			// Only attempt to load the component if it's supported in core and hasn't already been loaded
			if (in_array($component, $supported) && empty(self::$loaded[__METHOD__][$component]))
			{
				Rb_Html::script('jui/js/jquery.ui.' . $component . '.js', false, true, false, false, $debug);
				self::$loaded[__METHOD__][$component] = true;
			}
		}

		return;
	}
}
