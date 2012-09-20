<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

jimport( 'joomla.application.component.view' );

abstract class Rb_AbstractView extends JView
{
	
	protected $_model 			= null; // Will be set by controller
	public    $_component		= RB_COMPONENT_NAME;
	protected $_tpl 			= null;
	public 	  $options 			= array('domObject'=>'xiWindowContent','domProperty'=>'innerHTML');

	function __construct($config = array())
	{		
		// setup rendering system
		$this->_renderer = Rb_Render::getRenderer();

		parent::__construct($config);
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
			if (!preg_match('/View(.*)/i', get_class($this), $r)) {
				JError::raiseError (500, "Rb_View::getName() : Can't get or parse class name.");
			}
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
		Rb_Error::assert(preg_match('/(.*)View/i', get_class($this), $r), Rb_Text::sprintf('COM_PAYPLANS_ERROR_XIVIEW_GETPREFIX_CANT_GET_OR_PARSE_CLASSNAME', get_class($this)), Rb_Error::ERROR);


		$this->_prefix  =  JString::strtolower($r[1]);
		return $this->_prefix;
	}

	function getModel()
	{
		return $this->_model;
	}

	function setModel($model)
	{
		 $this->_model = $model;
		 return $this;
	}

	function setTpl($tpl=null)
	{
		 $this->_tpl = $tpl;
		 return $this;
	}

	function getTpl($tpl=null)
	{
		 return $this->_tpl;
	}
	
	function setTask($task=null)
	{
		 $this->_task = $task;
		 return $this;
	}

	function getTask($task=null)
	{
		 return $this->_task;
	}
	/*
	 * This function will be called from Controller
	 * after doTask, so it will be called after
	 * controller task have been completed
	 *
	 * So that there is one point contact for
	 * handling anything in view
	 */
	function showTask($task='',$tpl=null)
	{
 		//generate the data in view for template
		if(empty($task) || $task=='default' || method_exists($this, $task)==false ){
			$task='display';
		}

		//set are you in admin or site
		$app = Rb_Factory::getApplication();
		$this->_isAdmin    = $app->isAdmin();
		$this->setTask($task);
		$this->setTpl($tpl);

		// collect task specific data,
		// if some error, do not display the page and simply return
		// RBFW_TODO : controller will handle the error
		if(false === $this->$task()){
			return false;
		}
		//set the model state in view variable
		$this->_basicFormSetup();

		// Trigger event before we load templates
		$args	= array(&$this, &$task);
		// get data from diffreent apps on respective positions
		$pluginResult = PayplansHelperEvent::trigger('onPayplansViewBeforeRender',$args, '', $this);
		$pluginResult = $this->_filterPluginResult($pluginResult);
		
		// now get html from different plugins and views
		$olddata = $this->get('plugin_result');
		$pluginResult = $this->_mergePluginsData($pluginResult, $olddata);
		
		$this->assign('plugin_result', $pluginResult);

		//load the template file
		$output = $this->loadTemplate($this->getTpl());
		if (Rb_Error::isError($output)) {
			return $output;
		}

		//post template rendering load trigger
		$args	= array(&$this, &$task, &$output);
		$result =  PayplansHelperEvent::trigger('onPayplansViewAfterRender', $args, '', $this);

		$this->_prepareDocument();
		
		//render output
		return $this->_render($output);
	}
	
	public function _mergePluginsData($pluginResult, $olddata)
	{
		if(!isset($olddata)){
			return $pluginResult;
		}
			
		if(count($pluginResult) > 1){
			foreach($pluginResult as $key=>$html){
				if(isset($olddata[$key])){
				    $pluginResult[$key] = $olddata[$key].$pluginResult[$key];
					//IMP:- unset values of $olddata after assigning values into $pluginResults
				    unset($olddata[$key]);
				}
			}
			//IMP:- if any data exists on any $key of $oldata 
			// but not on $pluginResult then add those values in $pluginResult
			$pluginResult = array_merge($pluginResult, $olddata);
		}
		else{
			foreach ($olddata as $key=>$html){
				$pluginResult[$key] = $olddata[$key];
			}
		}
		
		return $pluginResult;
	}

	protected function _prepareDocument()
	{
		if(PAYPLANS_JVERSION_FAMILY === '15'){
			return true;
		}
		
		if(Rb_Factory::getApplication()->isAdmin()){
			return true;
		}
		$app		= Rb_Factory::getApplication();
		$params 	= $app->getParams();
		$document 	= Rb_Factory::getDocument();		
		$menus		= $app->getMenu();
		$title		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu){
			$params->def('page_heading', $params->def('page_title', $menu->title));
		}
		
		$title = $params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = Rb_Text::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = Rb_Text::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
		$document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}
	}
	
	protected function _filterPluginResult($pluginResult)
	{
		$result = array('default' => '');
		foreach($pluginResult as $pluginHtml){
						
			// ignore empty, true and false
			if(is_bool($pluginHtml) || isset($pluginHtml)==false || empty($pluginHtml)==true){
				continue;
			}

			$position = 'default';
			$html	  = '';
			// want to set on position
			if(is_array($pluginHtml)){
				foreach ($pluginHtml as $position => $html){			
					// if string, then need to display on certain position
					if(is_string($position)){		
						if(isset($result[$position])==false){
							$result[$position] = '';
						}
						$result[$position] .= $html;
					}
					
					// if nothing specified then echo on default position
					if(is_numeric($position)){
						$result['default'] .= $html ; 
					}
				}
			}else{
				// no position mentioned, display it on default
				$result['default'] .= $pluginHtml;
			}
		}
		
		return $result;
	}

	//Calls actual rendered object, to render output
	protected function _render($output)
	{
		// club data and send to renderer
		$data ['header'] = $this->_showHeader();
		$data ['output'] = $output;
		$data ['footer'] = $this->_showFooter();

		return $this->_renderer->render($this, $data, $this->options);
	}
	
	protected function _showHeader()
	{
		// add admin toolbar
		if($this->_isAdmin){
			$this->_adminToolbar();
			//$this->_adminSubmenu();
		}
		return '';
	}

	protected function _showFooter()
	{
		// avoid ajax request
		if(PAYPLANS_AJAX_REQUEST == true || JRequest::getVar('tmpl')=='component'){
			return '';
		}
		
		//always shown in admin
		if(Rb_Factory::getApplication()->isAdmin()==true){
			return $this->_showAdminFooter();
		}
		
		//do not show in premium kits
		if((defined('PAYPLANS_PREMIUM_BUILD') && PAYPLANS_PREMIUM_BUILD)){
				return '';
		}
		
		return '<p align="center">'.Rb_Text::_('COM_PAYPLANS_POWERED_BY') .'<a id="payplansPowerdBy" href="http://www.jpayplans.com" target="_blank" >PayPlans</a></p>';
	}

	protected function _showAdminFooter()
	{
		ob_start()?>
       
        <div class="powered-by">
	       <?php echo Rb_Text::_('COM_PAYPLANS_POWERED_BY') .'<a id="payplans-powered-by" href="http://www.jpayplans.com" target="_blank" >PayPlans</a><sup>TM</sup>';?>
		   <?php echo ' | '.Rb_Text::_('COM_PAYPLANS_FOOTER_VERSION').' <strong>'.PAYPLANS_VERSION .'</strong> | '. Rb_Text::_('COM_PAYPLANS_FOOTER_BUILD').PAYPLANS_REVISION; ?>	  	
        	<?php echo '<br />'
        		.Rb_Text::_('COM_PAYPLANS_FOOTER_MESSAGE')
        		.'<a href="http://bit.ly/lrECHY">'.Rb_Text::_('COM_PAYPLANS_FOOTER_MESSAGE_LINK').'</a>'
        		.Rb_Text::_('COM_PAYPLANS_FOOTER_MESSAGE_JED'); 
        	?>
	    </div>
		<?php 
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}
	
	protected function _adminToolbar()
	{
		$this->_adminToolbarTitle();

		if($this->getTask() == 'edit' || $this->getTask() == 'new')
			$this->_adminEditToolbar();
		else
			$this->_adminGridToolbar();
	}

	protected function _adminToolbarTitle()
	{
		// Set the titlebar text
		Rb_HelperToolbar::title(Rb_Text::_('COM_PAYPLANS_SM_'.JString::strtoupper($this->getName())), "xi-".$this->getName().".png");
	}

	protected function _adminGridToolbar()
	{
		Rb_HelperToolbar::addNewX('new');
		Rb_HelperToolbar::editListX();
		Rb_HelperToolbar::customX( 'copy', 'copy.png', 'copy_f2.png', 'COM_PAYPLANS_TOOLBAR_COPY', true );
		Rb_HelperToolbar::divider();
		Rb_HelperToolbar::publish();
		Rb_HelperToolbar::unpublish();
		Rb_HelperToolbar::divider();
		Rb_HelperToolbar::delete();
		Rb_HelperToolbar::divider();
		Rb_HelperToolbar::searchpayplans();

	}

	protected function _adminEditToolbar()
	{   
	    $model = $this->getModel();
		Rb_HelperToolbar::apply();
		Rb_HelperToolbar::save();
		Rb_HelperToolbar::savenew();
		Rb_HelperToolbar::cancel();
		Rb_HelperToolbar::divider();
	  	//don't display delete button when creating new instance of object 
	    if($model->getId() != null){
		   Rb_HelperToolbar::deleteRecord();
		 }
		
	}

	
	static $_submenus = array('dashboard', 'config', 'plan','app', 'subscription',
							  'invoice', 'transaction','user', 'log');
	static function addSubmenus($menu=null)
	{
		if($menu !== null){
			self::$_submenus[] = $menu;
		}	
		return self::$_submenus;
	}

	protected static $_subMenuRenderingDone = false;
	public function _adminSubmenu($selMenu = 'dashboard')
	{
		$selMenu	= JString::strtolower(JRequest::getVar('view',$selMenu));

		// add menu for group if config option is enable
		if(!in_array('group', self::$_submenus)
			&& isset(Rb_Factory::getConfig()->useGroupsForPlan) 
			&& Rb_Factory::getConfig()->useGroupsForPlan){
			array_splice(self::$_submenus, 2, 0, "group");
		}
	
		foreach(self::$_submenus as $menu){
			Rb_HelperToolbar::addSubMenu($menu,$selMenu);
		}
		return $this;
	}


	protected function _basicFormSetup()
	{
		//setup the action URL
		$url 	= 'index.php?option=com_payplans&view='.$this->getName();
		$task	= JRequest::getVar('task');
		if($task){
			$url .= '&task='.$task;
		}
		
		$this->assign('uri', Rb_Route::_($url));

		//setup state
		$this->assign( 'state', $this->getModel()->getState());

		//setup record id, if any
		$this->assign( 'record_id', $this->getModel()->getId());

		//also pass model if required
		$this->assign( 'model', $this->getModel());
	}
	
	public function get($tpl=null)
	{
		// for templates data is assigned to tplVars
		if(isset($this->_tplVars[$tpl])){
			return $this->_tplVars[$tpl];
		}
		
		return parent::get($tpl);
	}
	
	public function assign($key, $value)
	{
		$this->_tplVars[$key] = $value;
	}
	
	function loadTemplate( $tpl = null, $args = null, $layout=null)
	{
		if($args === null){
			$args= $this->_tplVars;
		}
		
		//create the template file name based on the layout
		if($layout===null){
			$layout = $this->_layout;
		}
		
		$file = isset($tpl) ? $layout.'_'.$tpl : $layout;
		$file = preg_replace('/[^A-Z0-9_\.-]/i', '', $file);

		// load the template script
		$paths = $this->_getTemplatePath($file);
		//find the path
    	jimport('joomla.filesystem.path');
    	$template = JPath::find($paths, $file.'.php');

		if ($template == false) {
			return JError::raiseError( 500, "Layout $file [$template] not found");
		}
		
		// unset so as not to introduce into template scope
		if (isset($args['this'])) {
			unset($args['this']);
		}
		
		unset($tpl);
		unset($file);
		

		// Support tmpl vars
        unset($args['this']);
        unset($args['_tplVars']);
        extract((array)$args,  EXTR_OVERWRITE);
		
		ob_start();
		include $template;
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
	}
	
	protected function _getTemplatePath($layout = 'default')
    {
    	$app 		= Rb_Factory::getApplication();
    	$view 		= JString::strtolower($this->getName());
    	$pTemplate	= 'default'; 			// RBFW_TODO : the template being used
    	$pDefaultTemplate = 'default'; 		// default template
		$jTemplate 	= $app->getTemplate(); 	// joomla template

        // get the template and default paths for the layout
        static $paths = null;

        if($paths === null)
        {
        	$paths = array();
        
        	$joomlaTemplatePath = JPATH_THEMES.'/'.$jTemplate.'/html'.'/'.constant(JString::strtoupper($this->_component).'_COMPONENT_NAME');
			if($app->isAdmin()){
				$payplanTemplatePath = PAYPLANS_PATH_TEMPLATE_ADMIN;
			}
			else{
				$payplanTemplatePath =  PAYPLANS_PATH_TEMPLATE;
			}

			// joomla template override
        	$paths[] = $joomlaTemplatePath.'/'.$view;
            $paths[] = $joomlaTemplatePath;
        	$paths[] = $joomlaTemplatePath.'/_partials';
        	
			// selected template path
			$paths[] = $payplanTemplatePath.'/'.$pTemplate.'/'.$view;
			$paths[] = $payplanTemplatePath.'/'.$pTemplate;
			$paths[] = $payplanTemplatePath.'/'.$pTemplate.'/_partials';

			// default template path			
			$paths[] = $payplanTemplatePath.'/'.$pDefaultTemplate.'/'.$view;
			$paths[] = $payplanTemplatePath.'/'.$pDefaultTemplate;
			$paths[] = $payplanTemplatePath.'/'.$pDefaultTemplate.'/_partials';

			// finally default partials
			$paths[] = PAYPLANS_PATH_TEMPLATE.'/'.$pDefaultTemplate.'/_partials';
        }
        
        return $paths;
    }

    function addPathToView($templatePaths)
    {
		foreach($templatePaths as $tmpl){
			$this->addTemplatePath($tmpl);
		}
    }
}
