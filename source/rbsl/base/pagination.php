<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport('joomla.html.pagination');

class XiPagination extends JPagination
{
	function __construct(XiModel &$model)
	{
		$limit = null;
		$limitstart = null;
		$this->initDefaultStates($model, $limit,$limitstart);
        return parent::__construct($model->getTotal(), $limitstart,$limit);
	}


	public function initDefaultStates(&$model, &$limit, &$limitstart)
	{
		$statePrefix		= XiHelperContext::getObjectContext($model);

		$app				= XiFactory::getApplication();
		$globalListLimit	= $app->getCfg('list_limit');

		// Get pagination request variables

		//limit should be used from global space
        $limit 		= $app->getUserStateFromRequest('global.list.limit', 'limit', $globalListLimit, 'int');

        //other states should be picked from model namespace
        $context = XiHelperContext::getObjectContext($model);
        $limitstart = $model->getState('limitstart');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        //set states in model
        $model->setState('limit', $limit);
        $model->setState('limitstart', $limitstart);
	}

}
