<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();



abstract class Rb_AbstractController extends Rb_AdaptController
{
	protected	$_prefix	= '';
	//Absolute prefix contain component name , irrespective of site or admin
	protected	$_component	= RB_COMPONENT_NAME;
	protected	$_tpl		= null;

	//it stores relation between task and table column
	// _boolMap[TASKNAME]= array( TABLE COLUMN, CHANGE VALUE, SWITCH)
	protected 	$_boolMap	= array();
	protected	$_defaultOrderingDirection = 'ASC';
	
	/**
	 * Publish/Ordering functionality can be common on various forms
	 * If child class want to differ, then it can over-ride, savePublish or saveOrder
	 */
	function __construct($options = array())
	{
		parent::__construct();

		//init the controller
		$this->_addTaskMapping();
	}

	protected function _addTaskMapping()
	{
		//create a map for boolean task
		//this will help is automatic handling
		//RBFW_TODO : Move boolmap into global boolmap , so it can be changed at any place
		//for adding extra task without code change here
		//IMP : Never change publish like other bool concept
		//b'coz it require for task bar also
		$this->_boolMap['publish']  = array('column' => 'published','value'=>1, 'switch'=>false);
		$this->_boolMap['unpublish']= array('column' => 'published','value'=>0, 'switch'=>false);

		//Register generic tasks
		$this->registerTask( 'list', 		'display');

		$this->registerTask( 'new', 		'edit');
		$this->registerTask( 'apply', 		'save');
		$this->registerTask( 'cancel', 		'close');
		$this->registerTask( 'savenew', 	'save');
		$this->registerTask( 'delete', 		'remove');

		$this->registerTask( 'publish', 	'multidobool');
		$this->registerTask( 'unpublish', 	'multidobool');
		//$this->registerTask( 'switch', 		'dobool');

		$this->registerTask( 'saveorder', 	'multiorder');
		$this->registerTask( 'orderup', 	'order');
		$this->registerTask( 'orderdown', 	'order');
		$this->registerTask( 'release', 	'checkin');
	}

	/*
	 * We want to make error handling to common objects
	 * So we override the functions and direct them to work
	 * on a global error object
	 */
	public function getError($i = null, $toString = true )
	{
		$errObj	=	Rb_Factory::getErrorObject();
		return $errObj->getError($i, $toString);
	}

	public function setError($errMsg)
	{
		$errObj	=	Rb_Factory::getErrorObject();
		return $errObj->setError($errMsg);
	}



	/*
	 * We need to override joomla behaviour as they differ in
	 * Model and Controller Naming
	 * In Joomla -> JModelProducts, JProductsController
	 * In PayPlans	 -> PayplansModelProducts, PayplansControllerProducts
	 */
	function getName()
	{
		$name = $this->_name;

		if (empty( $name ))
		{
			$r = null;
			Rb_Error::assert(preg_match('/Controller(.*)/i', get_class($this), $r) , Rb_Text::sprintf('PLG_SYSTEM_RBSL_ERROR_XICONTROLLER_CANT_GET_OR_PARSE_CLASS_NAME', get_class($this)), Rb_Error::ERROR);

			$name = strtolower( $r[1] );
		}

		return $name;
	}

	/*
	 * Collect prefix auto-magically
	 */
	public function getPrefix()
	{
		if(isset($this->_prefix) && empty($this->_prefix)===false)
			return $this->_prefix;

		$r = null;
		Rb_Error::assert(preg_match('/(.*)Controller/i', get_class($this), $r), Rb_Text::sprintf('PLG_SYSTEM_RBSL_ERROR_CANT_GET_PARSE_CLASS_NAME',Rb_Controller::getName()), Rb_Error::ERROR);

		$this->_prefix  =  JString::strtolower($r[1]);
		return $this->_prefix;
	}

	/*
	 * Returns a string telling where are you.
	 */
	public function getContext($classType='controller')
	{
		return JString::strtolower($this->getPrefix().'.'.$classType.'.'.$this->getName());
	}

	/*
	 * Get the model from Factory
	 * @return Rb_Model
	 */
	public function getModel($name='')
	{
		if(empty($name))
			$name 	= $this->getName();

		//prefix contain admin and site at end
		//remove admin or site , b'coz
		//IMP : Model and Tables are shared b/w Site and Admin So prefix is Payplans Only
		$model	= Rb_Factory::getInstance($name,'Model', JString::ucfirst($this->_component));

		if(!$model)
			$this->setError(Rb_Text::_('NOT_ABLE_TO_GET_INSTANCE_OF_MODEL'.' : '.$this->getName()));

		return $model;
	}

	/**
	 * @return Rb_View
	 */
	public function getView($name='')
	{
		if(empty($name)){
			$name 	= $this->getName();
		}

		//get Instance from Factory
		$view	= 	Rb_Factory::getInstance($name,'View', $this->getPrefix());

		if(!$view){
			$this->setError(Rb_Text::_('NOT_ABLE_TO_GET_INSTANCE_OF_VIEW'.' :'.$this->getName()));
		}

		return $view;
	}

	/*
	 * A default setup for redirection
	 */
	public function setRedirect($url=null, $msg = null, $type = 'message')
	{
		if($url===null){
			$url = Rb_Route::_("index.php?option=com_{$this->_component}&view={$this->getName()}");
		}
		parent::setRedirect($url,$msg,$type);
	}

	function execute($task)
	{
		// RBFW_TODO : Check for token
		
		//populate user state first
		$this->_populateModelState();
		
		$this->setTask($task);

		$pattern = '/^switchOff/i';
		if(preg_match($pattern, $task))
			$this->registerTask( $task, 	'multidobool');

		$pattern = '/^switchOn/i';
		if(preg_match($pattern, $task))
			$this->registerTask( $task, 	'multidobool');

		//trigger before
		$args	= array(&$this, &$task);
		$result = PayplansHelperEvent::trigger('onPayplansControllerBeforeExecute',$args);

		//let the task execute in controller
		//if task have failed, simply return and do not go to view
		$executeResult= parent::execute($task);

		//trigger after
		$args	= array(&$this, &$task, &$executeResult);
		$result = PayplansHelperEvent::trigger('onPayplansControllerAfterExecute', $args, '', $this);
		
		if($executeResult===false){
			return false;
		}
		
		//for testing purpose
		if(defined('PAYPLANS_UNIT_TEST_MODE')) return true;

		// now handle output part centrally
		// instansiate view and let them process
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

		//create view
		$view = & $this->getView();
		$view->setModel($this->getModel());

		// Set the layout
		$view->setLayout($viewLayout);

		// Display the view
		$view->showTask($this->getdoTask(), $this->_tpl);
		return true;
	}

	//Implement common authorization system over here
	public function authorize( $task )
	{
		// V. Imp. Security Measures, only allow to call function which are explicitly 
		// defined for frontend controller
		if(Rb_Factory::getApplication()->isAdmin()==false){
			return in_array($task, PayplansHelperUtils::getMethodsDefinedByClass(get_class($this)));
		}
		
		return true;
	}

	/**
	 * This function ensure that record under modification
	 * is properly identified at all levels.
	 * @return int
	 */
	public function _getId()
	{
		/*
		 *   if $_requireKey is set then
		 *   get key from GET method
		 *   generate the id from Rb_Encryption
		 */
		if(isset($this->_requireKey) && $this->_requireKey){
			
			$entKey = JRequest::getVar("{$this->getName()}_key", null, '');
			if($entKey !== null)
				return (int)Rb_HelperUtils::getIdFromKey($entKey);

			return -1;
		}
		
		//Id's can come in three ways
		//1: id in url
		//2: enitityname_id in post
		//3: cids in post(always)
		// we will only support ONE id here, to get multiple IDs, respective function will collect cids

		$entId = JRequest::getVar("{$this->getName()}_id", null, '', 'int');
		if($entId !== null)
			return $entId;

		$uId	= JRequest::getVar('id', null , '', 'int');
		if($uId !== null)
			return $uId;

		$cids 	= JRequest::getVar('cid', null, 'post', 'array');
		if($cids !== null)
			return $cids[0];

		return -1;
	}

	public function setTemplate($tpl = null)
	{
		$this->_tpl = $tpl;
		return $this;
	}

	public function _populateModelState()
	{
		$app 	 = Rb_Factory::getApplication();
		$model 	 = $this->getModel();

		//model do not exist
		if(!$model) return true;

		$context = Rb_HelperContext::getObjectContext($model);

		// if ordering filed exist the sort with ordering, else with id
		$tableKeys = $model->getTable()->getProperties();
		if(array_key_exists('ordering', $tableKeys))
			$orderingField = 'ordering';
		else
			$orderingField = $model->getTable()->getKeyName();

		$filters = array();
        $filters['limit']  			 = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'int');
        $filters['filter_order']     = $app->getUserStateFromRequest($context.'.filter_order', 'filter_order', $orderingField, 'id');
        $filters['filter_order_Dir'] = $app->getUserStateFromRequest($context.'.filter_order_Dir', 'filter_order_Dir', $this->_defaultOrderingDirection , 'word');
        $filters['filter']			 = $app->getUserStateFromRequest($context.'.filter', 'filter', '', 'string');

        //start link does not redirect to the first page because offset is used as limitstart   
        $filters['limitstart'] 		 = JRequest::getVar('limitstart',0);
        //also support generic filters
        $model->_populateGenericFilters($filters);

        //care required for -1
        $id = $this->_getId();
        $filters["id"] = ($id === -1) ? null : $id ;

    	foreach($filters as $key=>$value)
			$model->setState( $key, $value );

  		return true;
	}
	
	public function getTpl()
	{
		return $this->_tpl;
	}
}
