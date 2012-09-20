<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();


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
		echo Rb_Text::_('COM_PAYPLANS_NO_TASK_PROVIDED');
		return false;
	}

	function display()
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
		$post	=	JRequest::get('post');
		//Currently not required
		//$post   = $this->_filterPost($post);

		$msgType	=	'message';
		$itemId 	= $this->_getId();

		if($this->_save($post, $itemId)===false){
			$this->setMessage($this->getError());
			$msgType	=	'error';
		}
		else
			$this->setMessage(Rb_Text::_('COM_PAYPLANS_ITEM_SAVED_SUCCESSFULLY'));

		//perform redirection
		$redirect  = "index.php?option=com_{$this->_component}&view={$this->getName()}";

		if(JRequest::getVar('task')==='apply'){
			$table		=	$this->getModel()->getTable();
			$keyName	=	$table->getKeyName();
			$redirect  .= "&task=edit&id={$table->$keyName}";
		}

	   if(JRequest::getVar('task')==='savenew'){
			$redirect  .= "&task=new";
		}
		
		$redirect = Rb_Route::_($redirect);
		$this->setRedirect( $redirect , $this->getMessage(), $msgType);
		return false;
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
		return Rb_Lib::getInstance($this->getName(), $itemId, $type)
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
		$message 		= Rb_Text::_('COM_PAYPLANS_ITEMS_DELETED');


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

		$item = Rb_Lib::getInstance($this->getName(), $itemId, null)
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
		$message 		= Rb_Text::_('COM_PAYPLANS_ITEMS_COPIED');
		
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
			$this->setMessage(Rb_Text::_('COM_PAYPLANS_ITEM_ORDERED_SUCCESSFULLY'));

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
		$this->message 		= Rb_Text::_('COM_PAYPLANS_ITEMS_REORDERED');

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
	 * Implement enable/multienable methods
	 */
	public function dobool()
	{
		$task	= JRequest::getVar('task',	'enable');

		//setup error message, if no mapping exists
		if(array_key_exists($task, $this->_boolMap)===false)
		{
			$this->setRedirect(null, Rb_Text::_('COM_PAYPLANS_NO_MAPPING_FOUND_FOR_CURRENT_ACTION'), 'error');
			return false;
		}


		//find and trigger the call
		$mapping	= $this->_boolMap[$task];
		$switch		= $mapping['switch'];
		$column		= $mapping['column'];
		$value		= $mapping['value'];

		if($this->_doBool($column, $value, $switch)===false)
			$this->setMessage($this->getError());

		//redirect now
		$this->setRedirect();
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
		$this->message 		= Rb_Text::_('COM_PAYPLANS_ITEMS_REORDERED');

		$task	= JRequest::getVar('task',	'enable');

		//setup error message, if no mapping exists
		if(array_key_exists($task, $this->_boolMap)===false)
		{
			$offpattern = '/^switchOff/';
			$onpattern = '/^switchOn/';
			if(!preg_match($onpattern, $task) && !preg_match($offpattern, $task)){
				$this->setRedirect(null, Rb_Text::_('COM_PAYPLANS_NO_MAPPING_FOUND_FOR_CURRENT_ACTION'), 'error');
				return false;
			}
			else{
				if(preg_match($onpattern, $task)){
					$switch		= false;
					//$columninfo = str_split($task,strlen('switchOn'));
					$columninfo = explode('switchOn',$task);
					$column		= array_key_exists(1,$columninfo) ? $columninfo[1] : '';
					$value		= 1;
				}
				else if(preg_match($offpattern, $task)){
					$switch		= false;
					//RBFW_TODO : Convert it to str_replace, so that code can be somewaht clean from magic numbers
					//$columninfo = str_split($task,strlen('switchOff'));
					$columninfo = explode('switchOff',$task);
					$column		= array_key_exists(1,$columninfo) ? $columninfo[1] : '';
					$value		= 0;
				}
			}

		}
		else{
			$mapping	= $this->_boolMap[$task];
			$switch		= $mapping['switch'];
			$column		= $mapping['column'];
			$value		= $mapping['value'];
		}

		$cids 	= JRequest::getVar('cid', array (0), 'post', 'array');

		foreach ($cids as $cid)
		{
			if($this->_doBool($column, $value, $switch, $cid)===false)
				$errMsg .= $this->getError();
		}

		//if we have error messages
		if(empty($errMsg)===false)
		{
			$this->message = $this->errMsg;
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
		Rb_Error::assert($event,Rb_Text::_('COM_PAYPLANS_ERROR_PAYPLANS_UNKNOWN_EVENT_TRIGGER_REQUESTED'));

		$args = $this->_getArgs();

		//args must be an array
		return PayplansHelperEvent::trigger($event, $args);
	}
	
    //check if user's session is expired then redirect him to login page
	public function _checkSessionExpiry()
	{
		 // if session of the user is expired 
	     $id = Rb_Factory::getUser()->get('id');
         // if user is newly registered
	     $reg_id = Rb_Factory::getSession()->get('REGISTRATION_NEW_USER_ID', 0);
	     if(!$reg_id && !$id){
	           $this->setRedirect(Rb_Route::_("index.php?option=".PAYPLANS_COM_USER."&view=login"),Rb_Text::_('COM_PAYPLANS_SESSION_EXPIRED_LOGIN_AGAIN'));
	           return false;
	      }
	     return true;
	}
}
