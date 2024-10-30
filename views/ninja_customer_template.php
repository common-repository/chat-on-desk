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
$ninja_forms = NinjaForm::getNinjaForms();
if (! empty($ninja_forms) ) {
    ?>
<!-- accordion -->
<div class="cod-accordion">
    <div class="accordion-section">
    <?php foreach ( $ninja_forms as $ks => $vs ) { ?>
        <div class="cod-accordion-body-title" data-href="#accordion_cust_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="chatondesk_ninja_general[ninja_order_status_<?php echo esc_attr($ks); ?>]" id="chatondesk_ninja_general[ninja_order_status_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('ninja_order_status_' . esc_attr($ks), 'chatondesk_ninja_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_attr(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_cust_<?php echo esc_attr($ks); ?>" class="cod-accordion-body-content">
            <table class="form-table">
                <tr>
                    <td><input data-parent_id="chatondesk_ninja_general[ninja_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_ninja_general[ninja_message_<?php echo esc_attr($ks); ?>]" id="chatondesk_ninja_general[ninja_message_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('ninja_message_' . esc_attr($ks), 'chatondesk_ninja_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label>Enable Message</label>
                    <a href="admin.php?page=ninja-forms&form_id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'chat-on-desk')?></small></a>
                    </td>
                </tr>
                <tr valign="top" style="position:relative">
                    <td>					
					<?php
						$token = array();
						 $fields = NinjaForm::getNinjavariables($ks);
						 
						foreach ( $fields as $field ) {
							 if (! is_array($field) ) {
								$token['['. $field .']'] = $field;	
							}
							else{
								$field = isset($field['cells'][0]['fields'][0])?$field['cells'][0]['fields'][0]:'';
								if($field!='') {
								  $token['['. $field .']'] = $field;	
								}
							}						
						}
						 $template = chatondesk_get_option('ninja_sms_body_' . $ks, 'chatondesk_ninja_message', '');
						
							$params = array(
								'name'  => "chatondesk_ninja_message[ninja_sms_body_".$ks."]",
								'data_parent_id'  => "chatondesk_ninja_general[ninja_message_".$ks."]",
								'menu_id'  => "menu_ninja_cust_".$ks,
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
                        Select Phone Field : 
                        <select name="chatondesk_ninja_general[ninja_sms_phone_<?php echo esc_attr($ks); ?>]">
        <?php
        $phone_field = trim(chatondesk_get_option('ninja_sms_phone_' . $ks, 'chatondesk_ninja_general', ''));
        foreach ( $fields as $field ) {
            if (! is_array($field) ) {
                $selected = ($phone_field === $field )?'selected="selected"':((('' === $phone_field) && 0 === strpos($field, 'phone') )?'selected="selected"':'');    
                ?>
                            <option value="<?php echo esc_attr($field); ?>" <?php echo $selected; ?>><?php echo esc_attr($field); ?></option>
                <?php
            }
            else{
                $field = isset($field['cells'][0]['fields'][0])?$field['cells'][0]['fields'][0]:'';
                if($field!='') {
                    ?>
                                <option value="<?php echo esc_attr($field); ?>" <?php echo ( trim(chatondesk_get_option('ninja_sms_phone_' . $ks, 'chatondesk_ninja_general', '')) === $field ) ? 'selected="selected"' : ''; ?>><?php echo esc_attr($field); ?></option>
                     <?php
                }
            }
        }
        ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td><input data-parent_id="chatondesk_ninja_general[ninja_order_status_<?php echo esc_attr($ks); ?>]" type="checkbox" name="chatondesk_ninja_general[ninja_otp_<?php echo esc_attr($ks); ?>]" id="chatondesk_ninja_general[ninja_otp_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('ninja_otp_' . esc_attr($ks), 'chatondesk_ninja_general', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/><label>Enable Mobile Verification</label>
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