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
				
		// For backward compatibility should be #removed in 1.3
		self::loadMedia();

		return self::$_setupScriptsLoaded = true;
	}

	public static function loadMedia($list=array('bootstrap', 'jquery', 'rb'))
	{
		if(in_array('jquery', $list, false)){
			Rb_Html::_('jquery.framework');
			Rb_Html::_('jquery.ui');
		}
		
		if(in_array('bootstrap', $list, false)){
			Rb_Html::_('bootstrap.framework');	// Load bootstrap.min.js
			Rb_Html::_('bootstrap.loadCss'); // load bootstrap css
		}
		
		// Load RB Script (Maintain Order) then other scripts
		if(in_array('rb', $list, false)){
			Rb_Html::script('rb/rb.core.js');
			Rb_Html::script('rb/rb.lib.js');
			Rb_Html::script('rb/rb.validation.js');
		}
		
		if(in_array('angular', $list, false)){
			Rb_Html::script('angular/angular.js');
			Rb_Html::script('angular/ui-router.js');
		}
		
		if(in_array('font-awesome', $list, false)){
			Rb_Html::stylesheet('font-awesome/css/font-awesome.css');
		}
		
		if(in_array('nvd3', $list, false)){
			Rb_Html::script('nvd3/d3.v2.js');
			Rb_Html::script('nvd3/nv.d3.js');
			Rb_Html::stylesheet('nv.d3.css');
		}
		
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
}
