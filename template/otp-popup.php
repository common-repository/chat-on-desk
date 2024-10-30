<?php
/**
 * Otp popup 2 template.
 * PHP version 5
 *
 * @category Template
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */
 use Elementor\Frontend;
$uniqueNo                  = rand();        
$alt_form_id             = 'saFormNo_'.$uniqueNo;
$otp_template_style = \ChatOnDesk\chatondesk_get_option('otp_template_style', 'chatondesk_general', 'popup-1');
$otp_template_style = ('otp-popup-1.php'===$otp_template_style)?'popup-1':(('otp-popup-2.php'===$otp_template_style)?'popup-2':$otp_template_style);
$sa_values              = !empty(\ChatOnDesk\SmsAlertUtility::get_elementor_data("form_list"))?\ChatOnDesk\SmsAlertUtility::get_elementor_data("form_list"):$otp_template_style;
$form_id                = (isset($form_id) ? $form_id : $alt_form_id);
$post = get_page_by_path( 'cod_modal_style', OBJECT, 'chat-on-desk' );
if ( is_plugin_active('elementor/elementor.php') && !empty($post)) {  
	$post_id= $post->ID;	
    $frontent = new Frontend();
    $content =  $frontent->get_builder_content($post_id);
}
else{
	if($sa_values == 'popup-2')
	{
	  $content = CodPopup::getModelStyle(array('otp_template_style'=>'popup-2'));
	}
	else if($sa_values == 'popup-3')
	{
		$content = CodPopup::getModelStyle(array('otp_template_style'=>'popup-3'));
	}
	else{
		$content = CodPopup::getModelStyle(array('otp_template_style'=>'popup-1'));
	}
}
 echo ' <div class="modal chatondeskModal '.$form_id.' '. esc_attr($sa_values) . '" data-modal-close="' . esc_attr(substr($sa_values, 0, -2)) . '" data-form-id="'.$form_id.'">
	<div class="modal-content">
		<div class="close"><span></span></div>		
		'.$content.'
		</div>
<div class="ring cod-hide"><span></span></div></div>'; 