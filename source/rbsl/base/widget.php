<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Rb_Framework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

/**
 * Widget base class
 * @author Shyam
 *
 */
class Rb_Widget
{
	protected $_id 		= null;
	public function id($id=null)
	{
		// get 
		if($id === null){
			// ensure js compatibility
			return Rb_HelperUtils::jsCompatibleId($this->_id);
		}
		
		// set
		$this->_id = $id;
		return $this;		
	}
	
	protected $_type	=  null;
	public function type($type=null)
	{
		// get
		if($type===null){
			return $this->_type;
		}
		
		//set
		$this->_type=$type;
		return $this;
	}

	public $_html	  =  null;
	public function html($html=null)
	{
		//get
		if($html===null){
			return $this->_html;
		}
		
		//set
		$this->_html=$html;
		return $this;
	}
	
	// Store options for rendering of chart
	protected $_options = array(
		'width'	 => 300,
		'title'  => 'Default'
	);
	
	
	public function setOption($key, $value)
	{	
		$this->options[$key] = $value;
		return $this;
	}
	
	public function getOption($key, $default=null)
	{
		if(isset($this->options[$key])){
			return $this->options[$key];
		}
		
		return $default;
	}
	
	public function title($title=null)
	{
		// get
		if($title===null){
			return $this->getOption('title');
		}
		
		//set
		$this->setOption('title', $title);
		return $this;
	}
	
	protected $_actionJavaScript = null;

	public function actionJavaScript($js=null)
	{
		// get
		if($js===null){
			return $this->_actionJavaScript;
		}
		
		//set
		$this->_actionJavaScript =  $js;
		return $this;
	}
	/**
	 * Do some javascript stuff which needs to be done
	 * before widget-drawing function is called
	 * e.g. loading some js library
	 * @return string
	 * @since  1.3
	 */
	protected function _renderInit()
	{
		// want to do some stuff when widget is drawn on page
		return '';
	}
	
	// output javascript, to set widget options
	protected function _renderOption()
	{
		return 'var options = '.Rb_HelperUtils::fixJSONDates(json_encode($this->_options)).';';
	}

	
	// show the html output where widget will be drawn
	protected function _renderCanvas()
	{
		ob_start();
		?>
			<div class="pp-dashboard-widget<?php echo $this->getOption('class_suffix','');?> <?php echo $this->getOption('style_class','');?>" id="<?php echo $this->_id ;?>" style="width: <?php echo $this->getOption('width','100%');?>">
				<?php if($this->getOption('title','') != '') : ?>
					<div class="pp-title">
						<?php echo $this->getOption('title');?>
					</div>
				<?php endif; ?>
				<div class="pp-content">
				<?php echo $this->html();?>
				</div>
			</div>
		 <?php 
	      $output = ob_get_contents();
	      ob_get_clean();
	      return $output;	
	}
	
	
	// output javascript which will draw the chart
	protected function _renderAction()
	{
		// do some stuff when widget is drawn on page
		$script = $this->actionJavaScript();
		if($script !== null){
			return $script;
		}
		
		// default a empty string
		return '';
	}

	
	/**
	 * No need to override it
	 * @return string
	 */
	final protected function _render()
	{
		$funcTitle = 'payplans_widget_draw_'.$this->id();
		ob_start();
		?>	
		
		<?php echo $this->_renderInit(); ?>
		
		// define function 
      	function <?php echo $funcTitle; ?>() {
	      	<?php echo $this->_renderOption(); ?>
	      	<?php echo $this->_renderAction(); ?>
      	}
      	// add to queue
      	payplans_widget_queue.push(<?php echo $funcTitle; ?>);      	
	  <?php 
      $output = ob_get_contents();
      ob_get_clean();
      return $output;
	}

	// The function which should be called from module/component or any other part
	// out side library 
	public function draw()
	{
      //add script to document
      Rb_Factory::getDocument()->addScriptDeclaration($this->_render());
      
      // render canvas which should be echo'ed
      return $this->_renderCanvas();
	}
	
	static function _initWidget()
	{      
      // add script to document
      Rb_Factory::getDocument()->addScriptDeclaration(
      	'
      	// global queue to hold widgets
      	var payplans_widget_queue = [];
			
      	// Set a callback to run all widget drawing when page is loaded
      	xi.jQuery(document).ready(function(){
      			// Remove and execute all items in the array
				while(payplans_widget_queue.length > 0) {
				    (payplans_widget_queue.shift())();   
				}
      		}
      	);
      	'
      );
	}
}

// load widget loader scripts
Rb_Widget::_initWidget();