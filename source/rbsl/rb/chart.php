<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		PayPlans
* @subpackage	Rb_Framework
* @contact 		shyam@readybytes.in
*/
if(defined('_JEXEC')===false) die();

abstract class Rb_Chart 
{
	const HEIGHT = 300;
	const WIDTH  = 700;
		
	// --------- Object Specific Part -------------
	/**
	 * store data to be displayed in chart
	 * @var Rb_Chartdata
	 */
	public $data	  =  null;
	
	public function __construct()
	{
		$this->data = new Rb_Chartdata();
	}
	
	// title will uniquely identify it
	protected $_title	  =  null;
	
	public function getTitle()
	{
		return $this->getOption('title');
	}
	
	public function setTitle($title)
	{
		$this->setOption('title', $title);
		return $this;
	}
	
	public function setId($id)
	{
		$this->_id = $id;
		return $this;		
	}
	public function getId()
	{
		// ensure js compatibility
		return Rb_HelperUtils::jsCompatibleId($this->_id);
	}
	
	// Store options for rendering of chart
	public $options = array(
		'height' => 700,
		'width'	 => 300,
		'wmode'  => "transparent"
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
	
	// Rendering functions
	// output javascript, to add data into google-chart-data-table 
	protected function _renderData()
	{
		ob_start();
		?>	
		// Create the data table.
		var data = new google.visualization.DataTable();
		<?php foreach($this->data->getCols() as $col) : ?>
			data.addColumn(
						'<?php echo htmlentities($col['type'],ENT_QUOTES, 'UTF-8');?>', 
						'<?php echo htmlentities($col['label'],ENT_QUOTES, 'UTF-8');?>',
						'<?php echo htmlentities($col['id'],ENT_QUOTES, 'UTF-8');?>'
					);
		<?php endforeach;?>
		
		data.addRows([
		<?php $count = 0;
		foreach($this->data->getRows() as $row) : ?>
			<?php echo ($count++ > 0) ? ',' : ''; ?> 			
			<?php echo json_encode(array_values($row)); ?>
		<?php endforeach;?>
		]);
		
	    <?php 
      	$output = ob_get_contents();
      	ob_get_clean();
      	
      	return Rb_HelperUtils::fixJSONDates($output);
	}
	
	// output javascript, to set chart options
	protected function _renderOption()
	{
		ob_start();
		?>
		// Set chart options
      	var options = <?php echo Rb_HelperUtils::fixJSONDates(json_encode($this->options)); ?>;
        <?php 
      	$output = ob_get_contents();
      	ob_get_clean();
      	
      	return $output;
	}

	
	// show the html output where drawing will be created
	protected function _renderCanvas()
	{
		echo '<div id="'.$this->getId().'" style="clear:left; width: '.$this->getOption('width').'px; height: '.$this->getOption('height').'px;"> </div>';	
	}
	
	
	// output javascript which will draw the chart
	protected function _renderDraw()
	{
		ob_start(); ?>

      	var wrap = new google.visualization.ChartWrapper();
      	wrap.setChartType('<?php echo $this->_name; ?>');
      	wrap.setContainerId('<?php echo $this->getId();?>');
      	wrap.setOptions(options);
      	wrap.setDataTable(data);
      	wrap.draw();

      	<?php 
      	$output = ob_get_contents();
      	ob_get_clean();
      	
      	return $output;
	}
	
	protected function _initChart()
	{
		//load basic google library
		Rb_Factory::chartInit();
	}
	
	// The function which should be called from module/component or any other part
	// out side library 
	public function draw()
	{
		ob_start();
		$funcTitle = 'chartDraw'.$this->getId();
		?>	
		
		<?php echo $this->_initChart(); ?>
		
		// Set a callback to run when the Google Visualization API is loaded.
      	google.setOnLoadCallback(<?php echo $funcTitle; ?>);
      
	      // Callback that creates and populates a data table, 
    	  // instantiates the pie chart, passes in the data and
	      // draws it.
      	
      	function <?php echo $funcTitle; ?>() {
	      	<?php echo $this->_renderData(); ?>
	      	<?php echo $this->_renderOption(); ?>
	      	<?php echo $this->_renderDraw(); ?>
      	}
      	
	  <?php 
      $output = ob_get_contents();
      ob_get_clean();
      	
      //add script to document
      Rb_Factory::getDocument()->addScriptDeclaration($output);
      
      // render canvas which should be echo'ed
      return $this->_renderCanvas();
	}
}

class Rb_Chartdata
{
	// every entry should be of this type
	// id , label, pattern, type
	protected $_cols 	  =  array();
	
	// number of data in each row should equal to number of columns
	protected $_rows 	  =  array();
	
	public function addColumn($id, $label, $type)
	{
		$col = array('type'=>$type, 'label'=>$label, 'id'=>$id);
		$this->_cols[] = $col;
		return $this;
	}
	
	public function addColumns(Array $columns)
	{		
		foreach($columns as $col){
			if(is_object($col)){
				$col  = (array)$col;
			}
			
			// check validity
			if(!isset($col['id']) || !isset($col['label']) || !isset($col['type'])){
				Rb_Error::assert(false, "Incorrect column passed ".var_export($col, true));
			}
			
			// $col have id/label/type
			$this->_cols[] = $col;
		}
		
		return $this;
	}
	
	// Provide data in array
	public function addRows(Array $data)
	{
		// validate rows
		$validData = array();
		foreach($data as $row){
			if(count($row) == count($this->_cols)){
				$validData[] = $row;
				continue;
			}
			Rb_Error::assert(false, 'NOT CORRECT DATA AS PER COLUMNS SPECIFICATION', Rb_Error::MESSAGE);			
		}

		$this->_rows = array_merge($this->_rows, $validData);
		return $this;
	}
	
	public function getRows()
	{
		return $this->_rows;
	}
	
	public function getCols()
	{
		return $this->_cols;
	}
}