<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

jimport('joomla.html.pagination');

class Rb_Pagination extends JPagination
{
	function __construct(Rb_Model &$model)
	{
		$limit = null;
		$limitstart = null;
		$this->initDefaultStates($model, $limit,$limitstart);
        return parent::__construct($model->getTotal(), $limitstart,$limit);
	}


	public function initDefaultStates(&$model, &$limit, &$limitstart)
	{
		$statePrefix		= $model->getContext();

		$app				= Rb_Factory::getApplication();
		$globalListLimit	= $app->getCfg('list_limit');

		// Get pagination request variables

		//limit should be used from global space
        $limit 		= $app->getUserStateFromRequest('global.list.limit', 'limit', $globalListLimit, 'int');

        //other states should be picked from model namespace
        $context = $model->getContext();
        $limitstart = $model->getState('limitstart');

        // In case limit has been changed, adjust it
        $limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

        //set states in model
        $model->setState('limit', $limit);
        $model->setState('limitstart', $limitstart);
	}

}
