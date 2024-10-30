<?php
/**
 * Template.
  * PHP version 5
 *
 * @category View
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */
 namespace ChatOnDesk;
$forminator_forms = SA_Forminator::getForminatorForms();
if (! empty($forminator_forms) ) {
	$disablePlayground     = \ChatOnDesk\SmsAlertUtility::isPlayground()?"disablePlayground":"";
    ?>
<!-- accordion -->
<div class="cod-accordion">
    <div class="accordion-section">
    <?php foreach ( $forminator_forms as $ks => $vs ) { ?>
        <div class="cod-accordion-body-title" data-href="#accordion_cust_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="chatondesk_forminator_general[forminator_form_status_<?php echo esc_attr($ks); ?>]" id="chatondesk_forminator_general[forminator_form_status_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('forminator_form_status_' . esc_attr($ks), 'chatondesk_forminator_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_attr(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_cust_<?php echo esc_attr($ks); ?>" class="cod-accordion-body-content">
            <table class="form-table">
                <tr>
                    <td><input data-parent_id="chatondesk_forminator_general[forminator_form_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_forminator_general[forminator_message_<?php echo esc_attr($ks); ?>]" id="chatondesk_forminator_general[forminator_message_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('forminator_message_' . esc_attr($ks), 'chatondesk_forminator_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_forminator_general[forminator_message_<?php echo esc_attr($ks); ?>]">Enable Message</label>
                    <a href="admin.php?page=forminator-cform-wizard&id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'chat-on-desk')?></small></a>
                    </td>
                    </tr>
                <tr valign="top"  style="position:relative">
                    <td class="<?php echo $disablePlayground; ?>">
					
								
								<?php
							$token = array();
							 $fields = SA_Forminator::getForminatorVariables($ks);
							foreach ( $fields as $key=>$value ) {
									$token['['. $key .']'] = $value;						
							}				
							$template = chatondesk_get_option('forminator_sms_body_'. $ks, 'chatondesk_forminator_message', '');
								$params = array(
									'name'  => "chatondesk_forminator_message[forminator_sms_body_".$ks."]",
									'data_parent_id'  => "chatondesk_forminator_general[forminator_form_status_".$ks."]",
									'menu_id'  => "menu_cust_".$ks,
									'sms_text'  =>  $template,
									'token'     =>  $token,
									'moreoption' => false,
								);								
								echo \ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
									
							
						 ?>                        
                    </td>
                </tr>
                <tr>
                    <td>
                        Select Phone Field : <select name="chatondesk_forminator_general[forminator_sms_phone_<?php echo esc_attr($ks); ?>]">
        <?php
        foreach ( $fields as $key=>$value ) {
            ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php echo ( trim(chatondesk_get_option('forminator_sms_phone_' . $ks, 'chatondesk_forminator_general', '')) === $key ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($value); ?></option>
            <?php
        }
        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input data-parent_id="chatondesk_forminator_general[forminator_form_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_forminator_general[forminator_otp_<?php echo esc_attr($ks); ?>]" id="chatondesk_forminator_general[forminator_otp_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('forminator_otp_' . esc_attr($ks), 'chatondesk_forminator_general', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_forminator_general[forminator_otp_<?php echo esc_attr($ks); ?>]">Enable Mobile Verification</label>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    </div>
</div>
<!--end accordion-->
    <?php
} else {
    echo '<h3>No Form(s) published</h3>';
}
?>
