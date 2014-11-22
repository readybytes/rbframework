<?php
/**
 * @package     RbFW.Libraries
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Base class for rendering a display layout
 * loaded from from a layout file
 *
 * @package     RBFW.Libraries
 * @subpackage  Layout
 * @see         http://docs.joomla.org/Sharing_layouts_across_views_or_extensions_with_JLayout
 * @author		mManishTrivedi
 * @since       1.1
 */
class Rb_LayoutFile extends JLayoutFile
{
	/**
	 * Customize Path sequence 
	 *  
	 * @see libraries/cms/layout/JLayoutFile::refreshIncludePaths()
	 */
	protected function refreshIncludePaths()
	{
		// Reset includePaths
		$this->includePaths = array();

		// (1 - lower priority) Frontend base layouts
		$this->addIncludePaths(JPATH_ROOT . '/layouts');
		
		// (2) Received a custom path
		if (!is_null($this->basePath))
		{
			$this->addIncludePath(rtrim($this->basePath, DIRECTORY_SEPARATOR));
		}
		
		// (3) Standard Joomla! layouts overriden
		$this->addIncludePaths(JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts');

		// Component layouts & overrides if exist
		$component = $this->options->get('component', null);

		if (!empty($component))
		{
			// (4) Component path
			if ($this->options->get('client') == 0)
			{
				$this->addIncludePaths(JPATH_SITE . '/components/' . $component . '/layouts');
			}
			else
			{
				$this->addIncludePaths(JPATH_ADMINISTRATOR . '/components/' . $component . '/layouts');
			}

			// (5 - highest priority) Component template overrides path
			$this->addIncludePath(JPATH_THEMES . '/' . JFactory::getApplication()->getTemplate() . '/html/layouts/' . $component);
		}
	}
}
