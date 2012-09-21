<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

/**
 * VERY IMP :
 * While adding functions into plugin, we should keep in mind
 * that all function not starting with _ (under-score), will be
 * added into plugins event functions. So while adding supportive
 * function, always start them with underscore
 */

abstract class Rb_PluginRegistration extends Rb_Plugin
{
	protected $_session;
	protected $_app;
	
	function __construct(& $subject, $config = array())
	{
		parent::__construct($subject, $config);
		
		$this->_session = Rb_Factory::getSession();
		$this->_app 	= Rb_Factory::getApplication();
	}
	
	/**
	 * check for 
	 *  Registration without plan
	 *  Should Start Registration
	 *  And Complete Registration
	 */
	function onAfterRoute()
	{
		// do not run in admin panel
		if($this->_app->isAdmin()){
  			return true;
		}
		
		$registrationType = Rb_Factory::getConfig()->registrationType;
			
		// this should be checked all time
		if($this->_isRegistrationUrl()){
			if(!$this->_getPlan()){
				// if its a direct registration without plan id
				$this->_doSelectPlan();
			}	
			// V IMP : this should not be checked if this is a auto registartion plugin instance
			elseif($this->_name !== 'auto' && $registrationType != $this->_name){				
				$plg = Rb_HelperPlugin::getPluginInstance('payplansregistration', $registrationType);
				if(is_object($plg) &&  is_a($plg, 'Rb_PluginRegistration')){		
					$plg->_doStartRegistration();
				}
			}
		}
		
		if($registrationType != $this->_name){
			return true;
		}
		
		// start registration
		if($this->_shouldStartRegistration()){
			$this->_doStartRegistration();
		}
		
		// complete registration
		if($this->_shouldCompleteRegistration()){
			$this->_doCompleteRegistration();
		}
				
		return true;
	}
	
	public function onUserAfterSave($user, $isnew, $success, $msg)
	{
		return $this->onAfterStoreUser($user, $isnew, $success, $msg);
	}
	
	// joomla 1.6 compatibility
	public function onUserBeforeSave($user, $isnew)
	{
		return $this->onBeforeStoreUser($user, $isnew);
	}
	/**
	* if new user user is registered then set user id in session for the registration
	*/
	function onBeforeStoreUser($user, $isnew)
	{
		if($isnew){
			$this->_session->set('REGISTRATION_NEW_USER', true);
		}

		return true;
	}
	
	/**
	* if new user user is registered then set user id in session for the registration
	*/
	function onAfterStoreUser($user, $isnew, $success, $msg)
	{
		if($this->_session->get('REGISTRATION_NEW_USER', false)){
			$this->_setUser($user['id']);
			$this->_session->set('REGISTRATION_NEW_USER', false);
		}

		return true;
	}
	
	// show the corresponding registartion
	public function onPayplansViewBeforeRender(Rb_View $view, $task)
	{
		if(!($view instanceof PayplanssiteViewPlan)){
			return true;
		}

		if($task != 'login'){
			return true; 
		}

		if(Rb_Factory::getConfig()->registrationType == $this->_name){
			return array('pp_plan_login_registration_position' => $this->_render('registration'));
		}
	}

	protected function _getVars($vars = array('option', 'view', 'task'))
	{
		$ret = array();
		foreach($vars as $value){
			$ret[$value] = JRequest::getVar($value, 'BLANK');
		}
		
		return $ret;
	}

	
	protected function _setUser($userId)
	{
		$this->_session->set('REGISTRATION_USER_ID', $userId);
		return true;
	}
	
	protected function _getUser($id = null)
	{
		return $this->_session->get('REGISTRATION_USER_ID', 0);
	}
	
	protected function _setPlan($planId)
	{
		$this->_session->set('REGISTRATION_PLAN_ID', $planId);
		return true;
	}
	
	protected function _getPlan()
	{
		$planId = JRequest::getVar('plan_id', 0);
		if($planId){
			return $planId;
		}
		
		return $this->_session->get('REGISTRATION_PLAN_ID', 0);
	}
	
	/**
	 * 1: payplansRegister<<registration type>> must be set in post
	 * 2: url must be option = com_payplans, view = plan, task = login
	 * 3: plan id must be set
	 */
	protected function _shouldStartRegistration()
	{
		$planId = $this->_getPlan();
		$vars = $this->_getVars();
		
		// check if registration button is clicked
		if(JRequest::getVar('payplansRegister'.JString::ucfirst(Rb_Factory::getConfig()->registrationType), false) === false){
			return false;
		}
		
		if($vars['option'] == 'com_payplans' && $vars['view'] == 'plan' && $vars['task'] == 'login'
			&& $planId){
				return true;
			}
			
		return false;
	}
	
	/**
	 * set plan in session
	 * redirect corresponding registration url
	 */
	protected function _doStartRegistration()
	{
		$planId = $this->_getPlan();

		$this->_setPlan($planId);
		$this->_app->redirect(Rb_Route::_($this->_registrationUrl));
		return true;
	}
	
	/**
	 * 1: user must be set in session
	 * 2: is this the registration completion URL
	 */
	protected function _shouldCompleteRegistration()
	{
		if($this->_getUser() && $this->_isRegistrationCompleteUrl()){
			return true;
		}
		
		return false;
	}
	
	/**
	 * get plan instance from plan_id
	 * subscibe the plan
	 * reset user and plan in session
	 * redirect to order confirmation page
	 */
	protected function _doCompleteRegistration()
	{		
		if(!$this->_getPlan()){
			// if plan is not selected then do not create order
			// do not do anything 
			$this->_setUser(0);
			return true;		
		}
		$userId		= $this->_getUser();
		$order 		= PayplansPlan::getInstance( $this->_getPlan())
								->subscribe($userId);
		$invoice 	= $order->createInvoice();
		$invoiceKey = $invoice->getKey();
						
		// get user id in REGISTRATION_NEW_USER_ID to check it during session expiration checking	
		$this->_session->set('REGISTRATION_NEW_USER_ID', $userId);				
		$this->_setUser(0);
		$this->_setPlan(0);
		
		// now redirect to confirm action
		$this->_app->redirect(Rb_Route::_("index.php?option=com_payplans&view=invoice&task=confirm&invoice_key=".$invoiceKey));
						
	}
	
	/**
	 * redirect to plan selection page
	 */
	protected function _doSelectPlan()
	{
		$this->_app->redirect(Rb_Route::_('index.php?option=com_payplans&view=plan&task=subscribe'));
	}
	
	function _sendActivationMail($user)
	{
		$db		=& JFactory::getDBO();

		$name 		= $user['name'];
		$email 		= $user['email'];
		$username 	= $user['username'];

		$sitename 		= Rb_Factory::getConfig()->sitename;
		$mailfrom 		= Rb_Factory::getConfig()->mailfrom;
		$fromname 		= Rb_Factory::getConfig()->fromname;
		$siteURL		= JURI::base();

		$subject 	= sprintf ( JText::_( 'Account details for' ), $name, $sitename);
		$subject 	= html_entity_decode($subject, ENT_QUOTES);
		
		$message = sprintf ( JText::_( 'SEND_MSG_ACTIVATE' ), $user['name'], $sitename, $siteURL.'index.php?option='.PAYPLANS_COM_USER.'&task=activate&activation='.$user['activation'], $siteURL, $user['username'], $user['password2']);

		$message = html_entity_decode($message, ENT_QUOTES);

		//get all super administrator
		$query = 'SELECT name, email, sendEmail' .
				' FROM #__users' .
				' WHERE LOWER( usertype ) = "super administrator"';
		$db->setQuery( $query );
		$rows = $db->loadObjectList();

		// Send email to user
		if ( ! $mailfrom  || ! $fromname ) {
			$fromname = $rows[0]->name;
			$mailfrom = $rows[0]->email;
		}

		JUtility::sendMail($mailfrom, $fromname, $email, $subject, $message);
	}
}
