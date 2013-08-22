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
 * Utility class for form related behaviors
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.0
 */
abstract class JHtmlFormbehavior
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.0
	 */
	protected static $loaded = array();

	/**
	 * Method to load the Chosen JavaScript framework and supporting CSS into the document head
	 *
	 * If debugging mode is on an uncompressed version of Chosen is included for easier debugging.
	 *
	 * @param   string  $selector  Class for Chosen elements.
	 * @param   mixed   $debug     Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function chosen($selector = '.advandedSelect', $debug = null)
	{
		if (isset(self::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		JHtml::_('script', JURI::root().'media/rb/jui/js/chosen.jquery.min.js', false, true, false, false, $debug);
		JHtml::_('stylesheet', JURI::root().'media/rb/jui/css/chosen.css', false, true);
		JFactory::getDocument()->addScriptDeclaration("
				rb.jQuery(document).ready(function (){
					rb.jQuery('.rb-wrap').find('" . $selector . "').chosen({
						disable_search_threshold : 10,
						allow_single_deselect : true
					});
				});
			"
		);

		self::$loaded[__METHOD__][$selector] = true;

		return;
	}
}
