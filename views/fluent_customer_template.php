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

$fluent_forms = \ChatOnDesk\FluentForm::getFluentForms();
if (! empty($fluent_forms) ) {
	$disablePlayground     = \ChatOnDesk\SmsAlertUtility::isPlayground()?"disablePlayground":"";
    ?>
<!-- accordion -->
<div class="cod-accordion">
    <div class="accordion-section">
    <?php foreach ( $fluent_forms as $ks => $vs ) { ?>
        <div class="cod-accordion-body-title" data-href="#accordion_cust_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="chatondesk_fluent_general[fluent_order_status_<?php echo esc_attr($ks); ?>]" id="chatondesk_fluent_general[fluent_order_status_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( ChatOnDesk\chatondesk_get_option('fluent_order_status_' . esc_attr($ks), 'chatondesk_fluent_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_attr(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_cust_<?php echo esc_attr($ks); ?>" class="cod-accordion-body-content">
            <table class="form-table">
                <tr>
                    <td><input data-parent_id="chatondesk_fluent_general[fluent_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_fluent_general[fluent_message_<?php echo esc_attr($ks); ?>]" id="chatondesk_fluent_general[fluent_message_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( ChatOnDesk\chatondesk_get_option('fluent_message_' . esc_attr($ks), 'chatondesk_fluent_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_fluent_general[fluent_message_<?php echo esc_attr($ks); ?>]">Enable Message</label>
                    <a href="admin.php?page=fluent_forms&route=editor&form_id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'chat-on-desk')?></small></a>
                    </td>
                    </tr>
                <tr valign="top"  style="position:relative">
                    <td class="<?php echo $disablePlayground; ?>">
                        
				<?php
				$token = array();
				$fields = \ChatOnDesk\FluentForm::getFluentVariables($ks);
				foreach ( $fields as $key=>$value ) {
						$token['['. $key .']'] = $value;						
				}				
				$template = \ChatOnDesk\chatondesk_get_option('fluent_sms_body_' . $ks, 'chatondesk_fluent_message', '');
					$params = array(
						'name'  => "chatondesk_fluent_message[fluent_sms_body_".$ks."]",
						'data_parent_id'  => "chatondesk_fluent_general[fluent_order_status_".$ks."]",
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
                        Select Phone Field : <select name="chatondesk_fluent_general[fluent_sms_phone_<?php echo esc_attr($ks); ?>]">
        <?php
        foreach ( $fields as $key=>$value ) {
            ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php echo ( trim(ChatOnDesk\chatondesk_get_option('fluent_sms_phone_' . $ks, 'chatondesk_fluent_general', '')) === $key ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($value); ?></option>
            <?php
        }
        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input data-parent_id="chatondesk_fluent_general[fluent_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_fluent_general[fluent_otp_<?php echo esc_attr($ks); ?>]" id="chatondesk_fluent_general[fluent_otp_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( ChatOnDesk\chatondesk_get_option('fluent_otp_' . esc_attr($ks), 'chatondesk_fluent_general', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_fluent_general[fluent_otp_<?php echo esc_attr($ks); ?>]">Enable Mobile Verification</label>
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
