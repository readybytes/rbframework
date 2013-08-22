<?php
/**
* @copyright		Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license			GNU/GPL, see LICENSE.php
* @package			RB-Framework
* @subpackage		Backend
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class Rb_PackageScriptEcommerce
{
	function install()
	{
		return $this->execute_sql(dirname(__FILE__).'/install.sql');		
	}

	function execute_sql($sqlFile)
	{
		$db = JFactory::getDBO();
		$buffer = file_get_contents($sqlFile);

		// Graceful exit and rollback if read not successful
		if ($buffer === false)
		{
			JLog::add(JText::_('JLIB_INSTALLER_ERROR_SQL_READBUFFER'), JLog::WARNING, 'jerror');

			return false;
		}

		// Create an array of queries from the sql file
		$queries = JDatabase::splitSql($buffer);

		if (count($queries) == 0)
		{
			// No queries to process
			return 0;
		}

		// Process each query in the $queries array (split out of sql file).
		foreach ($queries as $query)
		{
			$query = trim($query);

			if ($query != '' && $query{0} != '#')
			{
				$db->setQuery($query);

				if (!$db->execute())
				{
					JLog::add(JText::sprintf('JLIB_INSTALLER_ERROR_SQL_ERROR', $db->stderr(true)), JLog::WARNING, 'jerror');

					return false;
				}
			}
		}
		return true;
	}
}
