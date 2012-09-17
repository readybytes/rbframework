<?php
/**
* @copyright	Copyright (C) 2009 - 2009 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
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

/**
 * All Migration Plugins should extend this class
 * Event names should start always as
 * onMigration
 * @author shyam
 */
abstract class XiPluginMigration extends XiPlugin
{
	const RECORD_PROCESSING_TIME = 0.1; // 20 records per second

	// require variable to be redfined in every class
	protected $_location    = __FILE__;
	protected $_title   	= '';
	
	// component, whom we are migrating, MUST be unique
	protected $_component	= '';
	
	/**
	 * @var XiPluginMigrationHelper
	 */
	protected $_helper	=null;
	
	
	
	protected function _initalize(Array $options= array())
	{
		$this->_helper = XiPluginMigrationHelper::getInstance();
	}
	
	/**
	 * Plugin is available :
	 * If current plugin can be used ir-respective
	 * of conditions
	 */
	public function _isAvailable(Array $options= array())
	{
		return JFolder::exists(JPATH_SITE.DS.'components'.DS.'com_'.$this->_component);
	}
	
	// Display HTML button to start migration 
	public function onPayplansDisplayMigrationAction()
	{
		$location = PAYPLANS_JVERSION_15 ? $this->_title : $this->_title .'/'. $this->_title ; 
		return array(
				'key'	=>$this->_component, 
				'title'	=> XiText::_('PLG_PAYPLANS_'.JString::strtoupper($this->_title)),
				'icon'  => '../plugins/'.$this->_type.'/'.$location.'/icon.png'
	  	); 
	} 
	
	final public function onPayplansPreMigration($pluginKey)
	{
		if($this->_component != $pluginKey){
			return false;
		}
		
		// in this case show error message
		if($this->_isAvailable() ===false){
			return false;
		}
		
		// setup next function to null, so someone tries
		$this->_helper->clear('next_function');
		$this->_helper->clear('processing_counter');
		$this->_helper->clear('next_function_offset');
		
		return $this->_preMigration();
	}
	
	final public function onPayplansDoMigration($pluginKey)
	{
		if($this->_component != $pluginKey){
			return false;
		}
		
		// Notify to PayPlans System about migration status
		define('PAYPLANS_MIGRATION_START',true);
		
		return $this->_doMigration();
	}
	
	final public function onPayplansPostMigration($pluginKey){
		if($this->_component != $pluginKey){
			return false;
		}
		
		return $this->_postMigration();
	}
	
	/*Implement these only */
	protected function _preMigration(){
		$records = $this->_estimateRecords();
		
		$this->_assign('record_count', $records);
		$this->_assign('time_estimate',  $records*self::RECORD_PROCESSING_TIME);
		
		return $this->_render('pre');
	}
	

	protected function _doMigration()
	{
		$count 	   = $this->_helper->read('processing_counter', 0);
		$next_func = $this->_helper->read('next_function', false);
		$next_func_offset = $this->_helper->read('next_function_offset',0);
		$limit		= $this->_helper->getExecutionTime() / self::RECORD_PROCESSING_TIME;
		
		if($next_func){
			// process 
			$count += $this->$next_func($limit, $next_func_offset);
			$this->_updateMigrationStatus($count);
			return false;
		}

		// just before starting migration
		PayplansHelperEvent::trigger('onPayplansStartMigration');
		
		$this->_helper->write('next_function','_migrateTables');
		$this->_assign('helper', $this->_helper);
		$this->_assign('record_count', $this->_helper->read('record_count',0));
		return $this->_render('migration');
	}
	
	
	protected function _postMigration(){
		return $this->_render('post');
	}
	
	
	protected function _updateMigrationStatus($count)
	{
		//send ajaxed response
		$ajax = XiFactory::getAjaxResponse();
		$message = $this->_helper->read('message', '');
		$total 	 = $this->_helper->read('record_count');
		$this->_helper->write('processing_counter', $count);
		$progress = 0;
		if($total){
			$progress = round(100 * $count / $total, 2);
		}
		
		$ajax->addAssign('pp-migrate-progress-count', 'innerHtml', $count);
		$ajax->addAssign('pp-migrate-progress-message', 'innerHtml', $message);
		//$ajax->addScriptCall('xi.dashboard.updateMigration', $this->_component, $progress);
		$ajax->addScriptCall('payplans.admin.migrate.update', $this->_component, $progress);
		
		$ajax->sendResponse();
	}
	
	protected function _scheduleNextFunction($nextFunc, XiQuery $query=null, $offset=0, $count=0)
	{
		if(!$query){
			$this->_helper->write('next_function',$nextFunc);
			$this->_helper->write('next_function_offset',0);
			return;
		}
		
		// find total records.
		$totalRecords = (int) $query->clear('select')->clear('limit')->clear('order')
									->select(' COUNT(*)')->dbLoadQuery()->loadResult();
	
		$this->_helper->write('next_function_offset',$offset+$count);
		
		// setup next functions, if we have processed all records
		if($count + $offset >= $totalRecords){
			$this->_helper->write('next_function',$nextFunc);
			$this->_helper->write('next_function_offset',0);
		}
		return;
	}
	
	
	protected function _MigrationComplete()
	{
		//send ajaxed response
		$ajax = XiFactory::getAjaxResponse();
		$ajax->addScriptCall('xi.dashboard.postMigration', $this->_component);		
		$ajax->sendResponse();
	}
	
	protected function _getPaymentMapper()
	{
		static $temp = array();
		// Very IMP
		// Ensure AdminPay App is there
		XiSetup::getInstance('adminpay')->doApply();
		
		//load apps refreshed
		XiFactory::cleanStaticCache(true);
		$allAppsInstance = PayplansHelperApp::getAvailableApps('payment');
		XiFactory::cleanStaticCache(false);
		
		$paymentAppMapper = array();
		foreach($allAppsInstance as $id => $app){
			// add it to mapping list
			if(in_array($app->getType(),$this->_appMapper)){
				$temp[$app->getType()] = $app;				
			}
		}

		//create missing apps
		// $migrate_app, Payment gatway of other-subscription system 
		foreach($this->_appMapper as $migrate_app => $payplansApp ){
			if(isset($temp[$payplansApp])){
				$paymentAppMapper[$migrate_app] = $temp[$payplansApp];
				continue; 
			}
			//create app 
			$app = PayplansApp::getInstance(0, $payplansApp);
			$app->setTitle($payplansApp);
			$app->setParam('applyAll',true); // make it a core app
			$app->save(); 
			$paymentAppMapper[$migrate_app] = $temp[$payplansApp]  = PayplansApp::getInstance($app->getId());
		}
		
		return $paymentAppMapper;
	}
	
	// 
	protected function _handleError()
	{
	
	}
}


class XiPluginMigrationHelper
{
	/**
	 * store seesion data
	 * @var XiSession
	 */
	protected $_store		=null;
	
	public $_queue			= array();
	public $start_time		= null;
	public $max_exec_time 	= null;
	 
	const  MAX_EXEC_TIME	= 15;
	const  BIAS				= 0.80;
	
	/**
	 * 	Copied From :  
	 *  Akeeba Kickstart 3.1.2 - The server-side archive extraction wizard
	 *  Copyright (C) 2008-2010  Nicholas K. Dionysopoulos / AkeebaBackup.com
	 *  
     *	Modified by Team JoomlaXi
	 * 	Enter description here ...
	 */
	protected function __construct()
	{
		//setup session storage
		$this->_store = XiFactory::getSession();
		

		// Get PHP's maximum execution time (our upper limit)
		$this->max_exec_time = self::MAX_EXEC_TIME;
		if(@function_exists('ini_get')){
			$this->max_exec_time = @ini_get("maximum_execution_time");
		}
		
		// If we have no time limit, set a hard limit of about 15 seconds
		// (safe for Apache and IIS timeouts, verbose enough for users)
		if ( (!is_numeric($this->max_exec_time)) || ($this->max_exec_time== 0)) {
			$this->max_exec_time = self::MAX_EXEC_TIME;
		}

		// Apply bias
		$this->max_exec_time = ($this->max_exec_time - 10) * self::BIAS;
	}
	
	/**
	 * @return XiPluginMigrationHelper
	 */
	public static function getInstance()
	{
		static $instance = null;
		
		if($instance === null){
			$instance = new XiPluginMigrationHelper();	
		}
		
		return $instance;
	}
	
	public function getExecutionTime()
	{
		return $this->max_exec_time ;
	}

	public function write($key, $data=null)
	{
		$this->_store->set($key,$data,__CLASS__);
		return $this->_store;
	}
	
	public function read($key, $default=null)
	{
		return $this->_store->get($key, $default, __CLASS__);
	}
	
	public function clear($name)
	{
		return $this->_store->clear($name, __CLASS__);
	}
}