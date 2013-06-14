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
	public static function load($attribs = array())
	{
		static $loaded = false;
		
		if($loaded === false){
			$loaded = true;
			Rb_Html::stylesheet('daterangepicker/daterangepicker.css', $attribs, false);
			
			Rb_Html::script('daterangepicker/moment.js', $attribs, false);
			Rb_Html::script('daterangepicker/daterangepicker.js', $attribs, false);			
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
                           '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TODAY');?>': [new Date(), new Date()],
                           '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_YESTERDAY');?>': [moment().subtract('days', 1), moment().subtract('days', 1)],
                           '<?php echo Rb_Text::sprintf('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_N_DAYS', 7);?>': [moment().subtract('days', 6), new Date()],
                           '<?php echo Rb_Text::sprintf('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_N_DAYS', 30);?>': [moment().subtract('days', 29), new Date()],
                           '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_THIS_MONTH');?>': [moment().startOf('month'), moment().endOf('month')],
                           '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_LAST_MONTH');?>': [moment().subtract('month', 1).startOf('month'), moment().subtract('month', 1).endOf('month')]
                        },
                        opens: 'left',
                        format: 'MM/DD/YYYY',
                        separator: ' to ',
                        startDate: moment().subtract('days', 29),
                        endDate: new Date(),
//                        minDate: '01/01/2012',
//                        maxDate: '12/31/2013',
                        locale: {
                            applyLabel: '<?php echo Rb_Text::_('JSUBMIT');?>',
                            fromLabel: '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_FROM');?>',
                            toLabel: '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_TO');?>',
                            customRangeLabel: '<?php echo Rb_Text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_CUSTOM_RANGE');?>',
                            daysOfWeek: ['<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_SU');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_MO');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TU');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_WE');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_TH');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_FR');?>',
                                         '<?php echo Rb_text::_('PLG_SYSTEM_RBSL_DATERANGEPICKER_SA');?>'
                                         ],                                         
                            monthNames: ['<?php echo Rb_text::_('JANUARY');?>',
                                         '<?php echo Rb_text::_('FEBRUARY');?>',
                                         '<?php echo Rb_text::_('MARCH');?>',
                                         '<?php echo Rb_text::_('APRIL');?>',
                                         '<?php echo Rb_text::_('May');?>',
                                         '<?php echo Rb_text::_('JUNE');?>',
                                         '<?php echo Rb_text::_('JULY');?>',
                                         '<?php echo Rb_text::_('AUGUST');?>',
                                         '<?php echo Rb_text::_('SEPTEMBER');?>',
                                         '<?php echo Rb_text::_('OCTOBER');?>',
                                         '<?php echo Rb_text::_('NOVEMBER');?>',
                                         '<?php echo Rb_text::_('DECEMBER');?>'],
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
