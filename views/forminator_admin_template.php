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
<div class="cod-accordion <?php echo $disablePlayground; ?>">
    <div class="accordion-section">
    <?php foreach ( $forminator_forms as $ks => $vs ) { ?>
        <div class="cod-accordion-body-title" data-href="#accordion_<?php echo esc_attr($ks); ?>">
            <input type="checkbox" name="chatondesk_forminator_general[forminator_admin_notification_<?php echo esc_attr($ks); ?>]" id="chatondesk_forminator_general[forminator_admin_notification_<?php echo esc_attr($ks); ?>]" class="notify_box" <?php echo ( ( chatondesk_get_option('forminator_admin_notification_' . $ks, 'chatondesk_forminator_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/><label><?php echo esc_html(ucwords(str_replace('-', ' ', $vs))); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_<?php echo esc_attr($ks); ?>" class="cod-accordion-body-content">
            <table class="form-table">
                <tr valign="top" style="position:relative">
                <td>
					<a href="admin.php?page=forminator-cform-wizard&id=<?php echo $ks;?>" title="Edit Form" target="_blank" class="alignright"><small><?php esc_html_e('Edit Form', 'chat-on-desk')?></small></a>				
						<?php
						$token = array();
						 $fields = SA_Forminator::getForminatorVariables($ks);
						foreach ( $fields as $key=>$value ) {
								$token['['. $key .']'] = $value;						
						}
						 $template = chatondesk_get_option('forminator_admin_sms_body_' . $ks, 'chatondesk_forminator_message', '');
							$params = array(
								'name'  => "chatondesk_forminator_message[forminator_admin_sms_body_".$ks."]",
								'data_parent_id'  => "chatondesk_forminator_general[forminator_admin_notification_".$ks."]",
								'menu_id'  => "menu_forminator_admin_notification_".$ks,
								'sms_text'  =>  $template,
								'token'     =>  $token,
								'moreoption' => false,
							);					
							echo \ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params);
					 ?>
                </td>
                </tr>
            </table>
        </div>
    <?php } ?>
    </div>
</div>
    <?php
} else {
    echo '<h3>No Form(s) published</h3>';
}
?>
