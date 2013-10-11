<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die('Restricted access' );

class Rb_HelperTemplate
{
	static $_setupScriptsEnv = false;
	public static function loadSetupEnv()
	{
		if(self::$_setupScriptsEnv === true){
			return true;
		}

		$vars  = new stdClass();
		
		$vars->url = new stdClass();
		$vars->url->base = JURI::base();
		$vars->url->root = JURI::root();
		$vars->url->base_without_scheme = JURI::base(true);
		$vars->request = new stdClass();
		$vars->request->option 	= JRequest::getVar('option','');
		$vars->request->view 	= JRequest::getVar('view','');
		$vars->request->task 	= JRequest::getVar('task','');
		
//		$vars->time = new stdClass();
//		$vars->time->timzone 	= Rb_HelperJoomla::getUserTimeZone();
//		$vars->time->offset 	= Rb_HelperJoomla::getUserTimeZone()* 60;
		
		
		ob_start();
		?>
		var rb_vars = <?php echo json_encode($vars); ?>;
		
		if(typeof(Joomla) === "undefined") {
	      var Joomla = {};
		}

		<?php
		$script = ob_get_contents();
		ob_end_clean();
		Rb_Factory::getDocument()->addScriptDeclaration($script);
		return self::$_setupScriptsEnv = true;
	}
	
	static $_setupScriptsLoaded = false;
	public static function loadSetupScripts($jquery=true, $jquery_ui=true)
	{
		if(self::$_setupScriptsLoaded === true){
			return true;
		}
		
		Rb_Html::_('jquery.framework');
		Rb_Html::_('jquery.ui');
		Rb_html::_('bootstrap.framework');	// Load bootstrap.min.js
		
		// Load RB Script (Maintain Order) then other scripts
		Rb_Html::script('rb/rb.core.js');
		Rb_Html::script('rb/rb.lib.js');
		Rb_Html::script('rb/rb.validation.js');

		return self::$_setupScriptsLoaded = true;
	}
	
	
	public static function mediaURI($path, $append=true, $root=true)
	{
		$path	= str_ireplace(Rb_HelperJoomla::getRootPath(), '', $path);
		$path 	= substr($path, 1);//separator can't be compared as hardcoded string, it could be '/' or '\'
		
		// replace all DS to URL-slash
		$path	= JPath::clean($path, '/');
		
		// no need to add root url
		if(strpos($path, 'http') === 0) {
			return $path;
		}
		
		// prepend URL-root, and append slash
		return ($root ? JURI::root() : '').$path.($append ? '/' : '');
	}
	
	public static function partial($layout='default', $args=array())
	{
		$app 				= Rb_Factory::getApplication();
    	$pTemplate			= 'default'; 			// RBFW_TODO : the template being used
    	$pDefaultTemplate 	= 'default'; 			// default template
		$jTemplate 			= $app->getTemplate(); 	// joomla template

        // get the template and default paths for the layout
        static $paths = null;

        if($paths === null)
        {
        	$paths = array();
        
        	$joomlaTemplatePath = JPATH_THEMES.'/'.$jTemplate.'/html'.'/'.PAYPLANS_COMPONENT_NAME;
			if($app->isAdmin()){
				$payplanTemplatePath = PAYPLANS_PATH_TEMPLATE_ADMIN;
			}
			else{
				$payplanTemplatePath =  PAYPLANS_PATH_TEMPLATE;
			}

			// joomla template override
        	$paths[] = $joomlaTemplatePath.'/_partials';
			$paths[] = $payplanTemplatePath.'/'.$pTemplate.'/_partials';
			$paths[] = $payplanTemplatePath.'/'.$pDefaultTemplate.'/_partials';
			// default to frontend partials
			$paths[] = PAYPLANS_PATH_TEMPLATE.'/'.$pDefaultTemplate.'/_partials';
        }
        
        //find the path and return
        jimport('joomla.filesystem.path');
        $template = JPath::find($paths, $layout.'.php');
        
		if ($template == false) {
			return JError::raiseError( 500, "Layout $file [$template] not found");
		}
		
        // setup args and render 
        extract((array)$args,  EXTR_OVERWRITE);
		
		ob_start();
		include $template;
		$output = ob_get_contents();
		ob_end_clean();

		return $output;
        
	}
}
