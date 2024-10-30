<?php
/**
 * Cf7 template.
  * PHP version 5
 *
 * @category View
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */
$wpcf7 = WPCF7_ContactForm::get_current();
if (empty($wpcf7->id()) ) {
    echo '<h3>';
    esc_html_e('Please save your contact form 7 once.', 'chat-on-desk');
    echo '</h3>';
} else {
    $contact_form = WPCF7_ContactForm::get_instance($wpcf7->id());
    $form_fields  = $contact_form->scan_form_tags();
    $visitor_msg_enable = ( isset($data['visitor_notification']) ) ? $data['visitor_notification'] : "off";
    $admin_msg_enable = ( isset($data['admin_notification']) ) ? $data['admin_notification'] : "off";
    $admin_message = ( ! empty($data['text']) ) ? trim($data['text']) : ChatOnDesk\SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE');
    $visitor_no = ( ! empty($data['visitorNumber']) ) ? $data['visitorNumber'] : "[billing_phone]";
    $visitor_msg = ( ! empty($data['visitorMessage']) ) ? $data['visitorMessage'] :ChatOnDesk\SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE');
    ?>    
<div id="cf7si-sms-sortables" class="meta-box-sortables ui-sortable">
 <div class="tab-panels woocommerce">
<section id="chatondesk_settings">
<div class="cod-accordion">
    <div class="accordion-section">
                <a class="cod-accordion-body-title" href="javascript:void(0)" data-href="#accordion_wc_visitor_notification">
            <input type="checkbox" name="wpcf7chatondesk-settings[visitor_notification]" id="wpcf7chatondesk-settings[visitor_notification]" class="notify_box" <?php echo ( ( 'on' === $visitor_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Visitor SMS Notification</label>
            <span class="expand_btn"></span>
        </a>
        <div id="accordion_wc_visitor_notification" class="cod-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody><tr valign="top">
                    <td>
            <?php
			$token = array();
			  foreach ( $form_fields as $form_field ) {
				 $field = json_decode(wp_json_encode($form_field), true);
				  if ('' !== $field['name'] ) {
						$token['['. $field['name'] .']'] = $field['name'];								
				  }
			  }
					
			$params = array(
				'name'  => esc_attr('wpcf7chatondesk-settings[visitorMessage]'),
				'data_parent_id'  => esc_attr('wpcf7chatondesk-settings[visitor_notification]'),
				'menu_id'  => "menu_wc_visitor_notification",
				'sms_text'  => $visitor_msg,
				'token'     =>  $token,
				'moreoption' => false,
			);	
			echo ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
			 ?>
                    </td>
                </tr>
            </tbody></table>
        </div>
                 <a class="cod-accordion-body-title" href="javascript:void(0)" data-href="#accordion_wc_admin_notification">
            <input type="checkbox" name="wpcf7chatondesk-settings[admin_notification]" id="wpcf7chatondesk-settings[admin_notification]" class="notify_box" <?php echo ( ( 'on' === $admin_msg_enable ) ? "checked='checked'" : '' ); ?> ><label>Admin SMS Notification</label>
            <span class="expand_btn"></span>
        </a>
        <div id="accordion_wc_admin_notification" class="cod-accordion-body-content" style="display: none;">
            <table class="form-table">
                <tbody>
                <tr>
                    <th scope="row" style="width:155px;">
                        <label for="wpcf7chatondesk-settings[phoneno]"><?php esc_html_e('Admin Mobile Number:', 'chat-on-desk'); ?></label>
                    </th>
                    <td data-parent_id="wpcf7chatondesk-settings[admin_notification]">
                        <input type="text" id="wpcf7chatondesk-settings[phoneno]" name="wpcf7chatondesk-settings[phoneno]" class="wide" size="70" value="<?php echo esc_attr($data['phoneno']); ?>"><span class="tooltip" data-title="<?php esc_html_e('Admin sms notifications will be sent to this number.', 'chat-on-desk'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                <tr valign="top">
                    <td colspan="2">
                       <div class="chatondesk_tokens">
         <?php
			$params = array(
				'name'  => esc_attr('wpcf7chatondesk-settings[text]'),
				'data_parent_id'  => esc_attr('wpcf7chatondesk-settings[admin_notification]'),
				'menu_id'  => "menu_wc_admin_notification",
				'sms_text'  => $admin_message,
				'token'     =>  $token,
				'moreoption' => false,
			);	
			echo ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
			 	
			 ?>
                    </td>
                </tr>
            </tbody></table>
        </div> 
        <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">
                <tr>
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"><?php esc_html_e('Visitor Mobile:', 'chat-on-desk'); ?></label>
                    </td>
                    <td>
                        <select name="wpcf7chatondesk-settings[visitorNumber]" id="visitorNumber">
                        <option value=""><?php esc_attr_e("--select phone field--", "chat-on-desk");?></option>
                        <?php
                        if (! empty($form_fields) ) {
                            foreach ( $form_fields as $form_field ) {
                                $field = json_decode(wp_json_encode($form_field), true);
                                if ('' !== $field['name'] ) {
                                    ?>
                            
                            
                            <option value="<?php echo '[' . esc_attr($field['name']) . ']'; ?>" <?php echo ( '[' . $field['name'] . ']' === $visitor_no ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($field['name']); ?></option>
                                                   <?php
                                }
                            }
                        }
                        ?>
                        </select>
                        <span class="tooltip" data-title="<?php esc_html_e('Select phone field.', 'chat-on-desk'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
                
                 <tr class="top-border">
                    <td scope="row" class="td-heading">
                        <label for="wpcf7-mail-body"></label>
                    </td>
                    <td>
                        <a href="https://www.youtube.com/watch?v=FFslKn_Stmc" target="_blank" class="btn-outline"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>

                        <a href="https://kb.smsalert.co.in/knowledgebase/integrate-otp-verification-with-contactform7/" target="_blank" class="btn-outline"><span class="dashicons dashicons-format-aside"></span> Documentation</a>
                    </td>
                </td>
                
                </tr>
                <tr>
            </table>
        </div>        
    </div>
    </div>
    </section>                                
    </div>
    </div>
    <style>
    .top-border {border-top: 1px dashed #b4b9be;}
    #chatondesk_settings select{max-width: 200px;}
    </style>
<script>
var adminnumber = "<?php echo esc_attr($data['phoneno']); ?>";
var tagInput1     = new TagsInput({
    selector: 'wpcf7chatondesk-settings[phoneno]',
    duplicate : false,
    max : 10,
});
var number = (adminnumber!='') ? adminnumber.split(",") : [];
if(number.length > 0){
    tagInput1.addData(number);
}    
</script>
<?php } ?>
