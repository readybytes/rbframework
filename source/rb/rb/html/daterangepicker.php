<?php
/**
* @copyright	Copyright (C) 2009 - 2012 Ready Bytes Software Labs Pvt. Ltd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* @package		RB Framework
* @subpackage	Frontend
* @contact 		payplans@readybytes.in
*/

if(defined('_JEXEC')===false) die('Restricted access' );

defined('JPATH_PLATFORM') or die;

/**
 * @author Gaurav Jain
 */
abstract class Rb_HtmlDaterangepicker
{
	/*
	 * @deprecated since 1.1 	Use Rb_helperTemplate::loadMedia() instead
	 */
	public static function load($attribs = array())
	{
		static $loaded = false;
		
		if($loaded === false){
			$loaded = true;
			Rb_Html::stylesheet('plg_system_rbsl/daterangepicker/daterangepicker.min.css', $attribs);
			
			Rb_Html::script('plg_system_rbsl/daterangepicker/moment.min.js', $attribs);
			Rb_Html::script('plg_system_rbsl/daterangepicker/daterangepicker.min.js', $attribs);			
		}
	}
	
	public static function edit($start_field_id, $end_field_id, $attr = null)
	{
		self::load();
		$container_id = $start_field_id.'_container';
		ob_start();
		?>
			<div id="<?php echo $container_id;?>" class="<?php echo isset($attr['class']) ? $attr['class'] : 'input-large';?>" style="background: #fff; cursor: pointer; padding: 5px 10px; border: 1px solid #ccc">
            	<i class="icon-calendar icon-large"></i>
               	<span></span> <b class="caret" style="margin-top: 8px"></b>
            </div>

			<script type="text/javascript">
               (function($){
               $(document).ready(function() {                  
                  $('#<?php echo $container_id;?>').daterangepicker(
                     {
                        ranges: {
                           '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TODAY');?>': [new Date(), new Date()],
                           '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_YESTERDAY');?>': [moment().subtract('days', 1), moment().subtract('days', 1)],
                           '<?php echo JText::sprintf('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_N_DAYS', 7);?>': [moment().subtract('days', 6), new Date()],
                           '<?php echo JText::sprintf('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_N_DAYS', 30);?>': [moment().subtract('days', 29), new Date()],
                           '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_THIS_MONTH');?>': [moment().startOf('month'), moment().endOf('month')],
                           '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_MONTH');?>': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                        },
                        opens: 'left',
                        format: 'MM/DD/YYYY',
                        separator: ' to ',
                        startDate: moment().subtract('days', 29),
                        endDate: new Date(),
//                        minDate: '01/01/2012',
//                        maxDate: '12/31/2013',
                        locale: {
                            applyLabel: '<?php echo JText::_('JSUBMIT');?>',
                            fromLabel: '<?php echo JText::_('PLG_SYSTEM_RBSL_FROM');?>',
                            toLabel: '<?php echo JText::_('PLG_SYSTEM_RBSL_TO');?>',
                            customRangeLabel: '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_CUSTOM_RANGE');?>',
                            daysOfWeek: ['<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_SU');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_MO');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TU');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_WE');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TH');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_FR');?>',
                                         '<?php echo JText::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_SA');?>'
                                         ],                                         
                            monthNames: ['<?php echo JText::_('JANUARY');?>',
                                         '<?php echo JText::_('FEBRUARY');?>',
                                         '<?php echo JText::_('MARCH');?>',
                                         '<?php echo JText::_('APRIL');?>',
                                         '<?php echo JText::_('May');?>',
                                         '<?php echo JText::_('JUNE');?>',
                                         '<?php echo JText::_('JULY');?>',
                                         '<?php echo JText::_('AUGUST');?>',
                                         '<?php echo JText::_('SEPTEMBER');?>',
                                         '<?php echo JText::_('OCTOBER');?>',
                                         '<?php echo JText::_('NOVEMBER');?>',
                                         '<?php echo JText::_('DECEMBER');?>'],
                            firstDay: 1
                        },
                        showWeekNumbers: true,
                        buttonClasses: ['btn-danger'],
                        dateLimit: false
                     },
                     function(start, end) {
                        $('#<?php echo $container_id;?> span').html(start.format('MMMM D, YYYY') + ' - ' + end.format('MMMM D, YYYY'));
                        $('#<?php echo $start_field_id;?>').val(start.format('MMMM D, YYYY'));
                        $('#<?php echo $end_field_id;?>').val(end.format('MMMM D, YYYY'));
                        
                        <?php if(isset($attr['onchange'])) :?>
    					<?php echo $attr['onchange'];?>
                      	<?php endif;?>
                     }
                  );
                  //Set the initial state of the picker label
                  $('#<?php echo $container_id;?> span').html(moment().subtract('days', 29).format('MMMM D, YYYY') + ' - ' + moment().format('MMMM D, YYYY'));
                  $('#<?php echo $start_field_id;?>').val(moment().subtract('days', 29).format('MMMM D, YYYY'));
                  $('#<?php echo $end_field_id;?>').val(moment().format('MMMM D, YYYY'));

                  <?php if(isset($attr['onchange'])) :?>
					<?php echo $attr['onchange'];?>
                  <?php endif;?>

               });
               })(rb.jQuery);
			</script>
		<?php 
		$content = ob_get_contents();
		ob_end_clean();
		
		return $content;
	}
}
