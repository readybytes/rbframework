<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

abstract class Rb_AbstractController extends Rb_AdaptController
{
	protected	$_prefix	= '';

	/** 
	 * @var Rb_Extension
	 */
	public		$_component	= '';
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
		
		// setup extension naming convention
		$this->_component =	Rb_Extension::getInstance($this->_component);

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
	
	/**
	 * Set _boolMap variable where you can map relationship b/w task and table column 
	 * @param String $task
	 * @param String $column 		=> table column name
	 * @param unknown_type $value 	=> Table column value
	 * @param boolen type $switch 	=> need to switch or not 
	 */
	public function setBoolMap($task, $column, $value, $switch = false) {
		$this->_boolMap[$task] = Array('column' => $column,'value'=> $value, 'switch'=> $switch);
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

		$this->_prefix  =  strtolower($r[1]);
		return $this->_prefix;
	}

	/*
	 * Returns a string telling where are you.
	 */
	public function getContext()
	{
		return strtolower($this->_component->getNameSmall().'.'.$this->getName());
	}

	/*
	 * Get the model from Factory
	 * @return Rb_Model
	 */
	public function getModel($name = '', $prefix = '', $config = array())
	{
		if (empty($name)) {
			$name 	= $this->getName();
		}

		//prefix contain admin and site at end
		//remove admin or site , b'coz
		//IMP : Model and Tables are shared b/w Site and Admin.
		if (empty($prefix)){
			$prefix = $this->_component->getPrefixClass();
		}
		$model	= Rb_Factory::getInstance($name,'Model', $prefix);

		if (!$model) {
			$this->setError(Rb_Text::_('NOT_ABLE_TO_GET_INSTANCE_OF_MODEL'.' : '.$this->getName()));
		}

		return $model;
	}

	/**
	 * @return Rb_View
	 */
	public function getView($name='', $format='', $prefix = '', $config = array())
	{
		if(empty($name)){
			$name 	= $this->getName();
		}
		
		if(empty($prefix)){
			$prefix = $this->getPrefix();
		}
		
		if(empty($format)){
			$format	= RB_REQUEST_DOCUMENT_FORMAT;
		}

		//get Instance from Factory
		$view = Rb_Factory::getInstance($name, 'View', $prefix);	

		return $view;
	}

	/*
	 * A default setup for redirection
	 */
	public function setRedirect($url=null, $msg = null, $type = 'message')
	{
		if($url===null){
			$url = Rb_Route::_("index.php?option={$this->_component->getNameCom()}&view={$this->getName()}");
		}
		parent::setRedirect($url,$msg,$type);
	}

	function execute($task)
	{
		// RBFW_TODO : Check for token
		
		//populate model state first
		$this->_populateModelState();
		
		// set th original task
		$this->setTask($task);

		// find if its a boolean task
		if(preg_match('/^switchOff/i', $task) || preg_match('/^switchOn/i', $task)) {
			$task = strtolower($task);
			if(preg_match('/^switchoff/i', $task)) {
				$column = JString::str_ireplace('switchoff', '', $task, 1);
				$value  = 0;
			}
			else {
				$column = JString::str_ireplace('switchon', '', $task, 1);
				$value  = 1 ;
			}
			$this->setBoolMap($task, $column, $value);
			$this->registerTask( $task, 	'multidobool');
		}

		//trigger before
		$args	= array(&$this, &$task, $this->getName());
		$result = Rb_HelperPlugin::trigger('on'.$this->_component->getPrefixClass().'ControllerBeforeExecute',$args);

		//let the task execute in controller
		//if task have failed, simply return and do not go to view
		$executeResult= parent::execute($task);

		//trigger after
		$args	= array(&$this, &$task, $this->getName(), &$executeResult);
		$result = Rb_HelperPlugin::trigger('on'.$this->_component->getPrefixClass().'ControllerAfterExecute', $args);
		
		if($executeResult===false){
			return false;
		}
		
		//for testing purpose, do not call view
		if(defined('RB_UNIT_TEST_MODE')){ return true; }

		// now handle output part centrally
		// instansiate view and let them process
		$viewLayout	= JRequest::getCmd( 'layout', 'default' );

		//create view
		$view = $this->getView();
		$model = $this->getModel();
		$view->setModel($model);

		// Set the layout
		$view->setLayout($viewLayout);

		// Call the view, It will call function equal to the resolved-task-name
		$view->showTask($this->getdoTask(), $this->_tpl);
		return true;
	}

	//Implement common authorization system over here
	public function authorize( $task )
	{
		// default allowed
		$access = true;
		
		// V. Imp. Security Measures, 
		// From frontend, function explicitly defined in frontend-controller are allowed
		if(Rb_Factory::getApplication()->isAdmin()==false){
			$access = in_array($task, Rb_HelperUtils::getMethodsDefinedByClass(get_class($this)));
		}
		
		//RBFW_TODO : Check 2.5 ACL Rules
		
		return $access;
	}

	/**
	 * This function ensure that record under modification
	 * is properly identified at all levels.
	 * @return int
	 */
	public function _getId()
	{	
		//Id's can come in three ways
		//0. comname_form[id] or comname_form[entity_id]  
		//1: id in url
		//2: enitityname_id in post
		//3: cids in post(always)
		// we will only support ONE id here, to get multiple IDs, respective function will collect cids
		$post = JRequest::getVar("{$this->_component->getNameSmall()}_form", null);
		if( isset($post["{$this->getName()}_id"])  || isset($post['id']) ){
			$entId = $post["{$this->getName()}_id"];
			if($entId !== null){ 
				return $entId;
			}
			
			$entId = $post['id'];
			if($entId !== null){ 
				return $entId;
			}
		}

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

		$context = $model->getContext();

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

abstract class Rb_Controller extends Rb_AbstractController
{
	protected 	$_defaultTask = 'display';

	function __construct($options = array())
	{
		parent::__construct($options);

		// Setup notask as default task to prevent meaningless errors
		$this->registerDefaultTask($this->_defaultTask);
	}

	/**
	 * Just a placeholder, rather then error of function not found
	 * it will display notask provided
	 */
	function notask()
	{
		echo Rb_Text::_('PLG_SYSTEM_RBSL_NO_TASK_PROVIDED');
		return false;
	}

	/**
	 * (non-PHPdoc)
	 * @see libraries/legacy/controller/JControllerLegacy::display()
	 * @param $cachable : dummy parameters for removing strict standard issue
	 * @param $urlparams: dummy parameters for removing strict standard issue
	 */
	function display($cachable = false, $urlparams = array())
	{
		return $this->_display();
	}

	public function _display()
	{
		//no decision required from controller
		return true;
	}

	public function view()
	{
		//If user can access data
		if($this->_view())
		{
			$this->setTemplate('view');
			return;
		}
		else
			$this->display();
	}

	public function _view()
	{}

	/**
	 * Checks if an item is checked out, and if so, redirects to layout for viewing item
	 * Otherwise, displays a form for editing item
	 *
	 * @return void
	 */
	public function edit()
	{
		$itemId = null;
		$userId = Rb_Factory::getUser()->id;

		//set editing template
		$this->setTemplate('edit');

		//if it was a new task, simply return true
		// as we cannot checkout non-existing record
		if($this->getTask() ==='new' || $this->getTask() === 'newItem')
			return true;

		//user want to edit record
		if($this->_edit($itemId, $userId)===false){
			//RBFW_TODO : enqueue message that item is already checkedout
			$this->setRedirect(null,$this->getError());
			return false;
		}

		return true;
	}

	public function _edit($itemId=null, $userId=null)
	{
		//get the model
		$model 		= $this->getModel();

		//find the user, if nothing mentioned
		if($userId	=== null)
			$userId = Rb_Factory::getUser()->id;

		//if Item Id is given then set to model
		if($itemId !== null)
			$this->getModel()->setState('id',$itemId);

		return true;
	}

	/**
	 * Cancels current operation and returns list layout
	 * - If item is checked out, checkin it
	 */
	public function close()
	{
        //try to checkin
		if($this->_close()===false)
			$this->setMessage($this->getError());

		//setup redirection
		$this->setRedirect();
		//as we need redirection
		return false;
	}

	public function _close($itemId=null, $userId=null)
	{
		//get the model
		$model 		= $this->getModel();

		//Reset state b'coz we need to list all records
		//Itemid need to be collected for proper check-in

		$oldItemId = $this->getModel()->setState('id',null);
		//Do it : only if itemid was not given
		if($itemId===null)
			$itemId = $oldItemId;

		return true;
	}

	public function save()
	{
		//RBFW_TODO : verify form token
		//try to save
		$post = Rb_Factory::getApplication()->input->post->get($this->_component->getNameSmall().'_form', array(), 'array');
		//Currently not required
		//$post   = $this->_filterPost($post);

		$msgType	=	'message';
		$itemId 	= $this->_getId();

		$entity = $this->_save($post, $itemId) ;
		
		if (!$entity) {
			$this->setMessage($this->getError());
			$msgType	=	'error';
		}
		else {
			$this->setMessage(Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEM_SAVED_SUCCESSFULLY'));
		}

		//perform redirection
		$redirect  = "index.php?option={$this->_component->getNameCom()}&view={$this->getName()}";

		// We use Table key name to work in both case with or without lib
		if(JRequest::getVar('task')==='apply' && $msgType != 'error') {
			$table    	=  $this->getModel()->getTable();
      		$keyName  	=  $table->getKeyName();
     		$redirect  .= "&task=edit&id={$table->$keyName}"; 
		}

	   if(JRequest::getVar('task')==='savenew' && $msgType != 'error') {
			$redirect  .= "&task=new";
		}
		
		$redirect = Rb_Route::_($redirect);
		$this->setRedirect( $redirect , $this->getMessage(), $msgType);
		
		if($msgType	==	'error') {
			return false;
		}
		
		return $entity;
	}

//	/**
//	 * This function filters variable for forms
//	 */
//	public function _filterPost($post)
//	{
//		$returnData = array();
//		//we need to remove the prefix
//		foreach($post as $key=>$data){
//			$r = null;
//			if (!preg_match('/'.RB_FORM_VARIABLE_PREFIX.'(.*)/i', $key, $r))
//				continue;
//
//			// xi_order_0 -> data[order][0]
//			$index = $r[1];
//			$pos=strrpos($index,'_');
//
//			$count = substr($index,$pos+1);
//			$index = substr($index,0, $pos);
//			$returnData[$index][$count] = $data;
//		}
//		return $returnData;
//	}
	/**
	 * Saves an item (new or old)
	 */
	public function _save(array $data, $itemId=null, $type=null)
	{
		//create new lib instance
		return Rb_Lib::getInstance($this->_component->getPrefixClass(), $this->getName(), $itemId)
						->bind($data)
						->save();
	}

	/**
	 * Deletes record(s) and redirects to default layout
	 */
	function remove()
	{
		$errMsg				= '';
		$messagetype 	= 'message';
		$message 		= Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEMS_DELETED');


		//ensure model state is blank, so no mishappening :-)
        // to get ID in _remove function for deleting in edit screen
		//$this->getModel()->setState('id',null);

		$cids = JRequest::getVar('cid', array (0), 'request', 'array');
		foreach (@$cids as $cid)
		{
			if($this->_remove($cid)===false)
				$errMsg .= $this->getError();
		}

		if(empty($errMsg)===false){
			$message	=	$errMsg;
			$messagetype	=	'error';
		}

		$this->setRedirect(null,$message,$messagetype);
		return false;
	}

	function _remove($itemId=null, $userId=null)
	{
		//get the model
		$model 		= $this->getModel();
	    if($itemId === null || $itemId === 0){
			$itemId = $model->getId();
		}
		//find the user, if nothing mentioned
		if($userId	== null)
			$userId 	= Rb_Factory::getUser()->id;

		$item = Rb_Lib::getInstance($this->_component->getPrefixClass(), $this->getName(), $itemId, null)
				->delete();

		if(!$item){
			//we need to set error message
			$this->setError($model->getError());
			return false;
		}
		return true;
	}

	/**
	 * Copy record(s)
	 */
	public function copy($cids = array())
	{
		$errMsg				= '';
		$messagetype 	= 'message';
		$message 		= Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEMS_COPIED');
		
		$cids = JRequest::getVar('cid', $cids, 'request', 'array');
		foreach ($cids as $cid)
		{
			if($this->_copy($cid)===false)
				$errMsg .= $this->getError();
		}
		
		if(empty($errMsg)===false){
			$message		=	$errMsg;
			$messagetype	=	'error';
		}

		$this->setRedirect(null,$message,$messagetype);
		return false;
	}
	

	public function _copy()
	{
		$this->setError('IMPLEMENT_COPY_FUNCTION');
		return false;
	}
	
	/**
	 * Reorders a single item either up or down (based on arrow-click in list)
	 * and redirects to default layout
	 * @return void
	 */
	function order()
	{
		$task	= JRequest::getVar('task', 'orderdown', 'post');
		$change = ($task === 'orderup') ? -1 : 1;

		$cids 	= JRequest::getVar('cid', array (0), 'post', 'array');

		//try to order
		if($this->_order($change, $cids[0])===false)
			$this->setMessage($this->getError());
		else
			$this->setMessage(Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEM_ORDERED_SUCCESSFULLY'));

		//perform redirection
		$this->setRedirect();
		return false;
	}

	/**
	 * Reorders multiple items (based on form input from list)
	 * and redirects to default layout
	 * @return void
	 */
	function multiorder()
	{
		$errMsg				= '';
		$this->messagetype 	= 'notice';
		$this->message 		= Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEMS_REORDERED');

		//RBFW_TODO : User proper variable names
		$ordering 	= JRequest::getVar('ordering', array(0), 'post', 'array');
		$cids 		= JRequest::getVar('cid', array (0), 'post', 'array');

		foreach ($cids as $cid)
		{
			if($this->_order($ordering[$cid], $cid)===false)
				$errMsg .= $this->getError();
		}

		//if we have error messages
		if(empty($errMsg)===false)
		{
			$this->message = $this->errMsg;
			$this->messagetype = 'error';
		}

		//IMP : reorder items to fill in the blanks
		$this->getModel()->reorder();

		//redirect now
		$this->setRedirect();
		return false;
	}

	/*
	 * @return bool
	 */
	public function _order($change, $itemId=null)
	{
		//get the model
		$model 		= $this->getModel();

		//try to move
		if($model->order($itemId, $change) )
			return true;

		//we need to set error message
		$this->setError($model->getError());
		return false;
	}
	
	/*
	 * Update record
	 */
	public function update()
	{
		$name	= JRequest::getVar('name',	null);
		$value	= JRequest::getVar('value',	null);
		$itemId 	= $this->getModel()->getId();
		
		$data  = array($name => $value);
		if($this->_save($data, $itemId)===false){
			$this->setMessage($this->getError());
		}

		//redirect now
		$this->setRedirect();
		return false;

	}

	public function multidobool()
	{
		$errMsg				= '';
		$this->messagetype 	= 'notice';
		$this->message 		= Rb_Text::_($this->_component->getPrefixText().'PLG_SYSTEM_RBSL_ITEMS_REORDERED');

		$task	= strtolower(JRequest::getVar('task',	'enable'));

		$mapping	= $this->_boolMap[$task];
		$switch		= $mapping['switch'];
		$column		= $mapping['column'];
		$value		= $mapping['value'];

		$cids 	= JRequest::getVar('cid', array (0), 'post', 'array');

		foreach ($cids as $cid)
		{
			if($this->_doBool($column, $value, $switch, $cid)===false)
				$errMsg .= $this->getError();
		}

		//if we have error messages
		if(empty($errMsg)===false)
		{
			$this->message = $errMsg;
			$this->messagetype = 'error';
		}

		//redirect now
		$this->setRedirect();
		return false;
	}

	/**
	 * This function will modify the table boolean data
	 * @param $task = the related task : published
	 * @param $change = the value to change to, 1/0
	 * @param $switch = do we need to switch the value if field, default is false
	 * @param $itemId = The item to modify, if null, will be calculated from session
	 * @return bool
	 */
	public function _doBool($column, $change, $switch=false, $itemId=null)
	{
		//get the model
		$model 		= $this->getModel();

		//try to switch
		if($model->boolean($itemId, $column, $change, $switch)===true)
			return true;

		//we need to set error message
		$this->setError($model->getError());
		return false;
	}

	/**
	 * This function will collect the data, from JRequest 
	 * And only for those, whose name starts with 'args#' 
	 * # is the count 1,2,3
	 */
	public function _getArgs()
	{
		// collect params
		// RBFW_TODO : loop array till argument count
		$args = array(); 
		for($i=1 ; $i < 10 ; $i++){
			$arg = JRequest::getVar('arg'.$i,null);
			
			if($arg == null){
				break;
			}
			
			$args[]= rawurldecode($arg);
		}

		// if it is an ajax request, decode the args 
		// (all ajax args are json-encoded)
		if(JRequest::getBool('isAjax',	false)){
			foreach($args as $index => $arg){
				$args[$index] = json_decode($arg);
			}
		}
		
		// for system starting from 2.0
		$event_args = JRequest::getVar('event_args',null);
		if($event_args !== null){
			$args = $event_args;
		}
		
		return $args;
	}

	public function trigger($event=null,$args=null)
	{
		//RBFW_TODO:High : Event should be filtered
		$event 		= JRequest::getVar('event', $event);
		Rb_Error::assert($event,Rb_Text::_('PLG_SYSTEM_RBSL_ERROR_PAYPLANS_UNKNOWN_EVENT_TRIGGER_REQUESTED'));

		$args = $this->_getArgs();

		//args must be an array
		return Rb_HelperEvent::trigger($event, $args);
	}
}
