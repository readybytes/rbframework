<?php
/**
 * @package    Joomla.Build
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set flag that this is a parent file.
const _JEXEC = 1;

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php'))
{
	require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES'))
{
	define('JPATH_BASE', dirname(__DIR__));
	require_once JPATH_BASE . '/includes/defines.php';
}

require_once JPATH_LIBRARIES . '/import.php';

require_once JPATH_LIBRARIES . '/cms.php';

/**
 * This script will fetch the update information for all extensions and store
 * them in the database, speeding up your administrator.
 *
 * @package  Joomla.Build
 * @since    3.0
 */
class GenerateCss extends JApplicationCli
{
	/**
	 * Entry point for the script
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function doExecute()
	{
		$location = dirname(__FILE__);
		$templates = array(
			'bootstrap.less' 			=> 	'bootstrap.css',
			'bootstrap-extended.less' => 	'bootstrap-extended.css',
			'bootstrap-rtl.less' 		=> 	'bootstrap-rtl.css',
			'responsive.less' 		=> 		'bootstrap-responsive.css'
		);

		if (!defined('FOF_INCLUDED'))
		{
			require_once JPATH_LIBRARIES . '/fof/include.php';
		}

		$less = new FOFLess;
		$less->setFormatter(new FOFLessFormatterJoomla);

		foreach ($templates as $source => $output)
		{
			try
			{
				$less->compileFile($location.'/rbless/'.$source, $location.'/css/'.$output);
			}
			catch (Exception $e)
			{
				echo $e->getMessage();
			}
		}
	}
}

JApplicationCli::getInstance('GenerateCss')->execute();
