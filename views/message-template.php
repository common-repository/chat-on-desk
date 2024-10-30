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
add_thickbox();
?>
<!-- Admin-accordion -->
<div class="cod-accordion"><!-- cod-accordion -->
    <div class="accordion-section">
        <?php foreach ( $templates as $template ) { ?>
        <div class="cod-accordion-body-title" data-href="#accordion_<?php echo esc_attr($checkTemplateFor); ?>_<?php echo esc_attr($template['status']); ?>">
            <input type="checkbox" name="<?php echo esc_attr($template['checkboxNameId']); ?>" id="<?php echo esc_attr($template['checkboxNameId']); ?>" class="notify_box" <?php echo ( 'on' === $template['enabled'] ) ? "checked='checked'" : ''; ?> <?php echo ( ! empty($template['chkbox_val']) ) ? "value='" . esc_attr($template['chkbox_val']) . "'" : ''; ?>  /><label><?php echo esc_html($template['title']); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_<?php echo esc_attr($checkTemplateFor); ?>_<?php echo esc_attr($template['status']); ?>" class="cod-accordion-body-content">
            <table class="form-table">
                <tr valign="top" style="position:relative">
                    <td>
					<?php
			$params = array(
				'name'  => esc_attr($template['textareaNameId']),
				'data_parent_id'  => esc_attr($template['checkboxNameId']),
				'menu_id'  => "menu_".esc_attr($checkTemplateFor)."_".$template['status'],
				'sms_text'  => $template['text-body'],
				'token'     => $template['token'],
				'moreoption' => true,
			);
			echo ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
			?>

                    </td>
                </tr>
            </table>
        </div>
        <?php } ?>
    </div>
    
</div>
<!--help links-->
<?php
foreach ( $templates as $template ) {
    if (!empty($template['help_links']) ) {
            
        foreach($template['help_links'] as $link){
            echo wp_kses_post('<a href="'.$link['href'].'" alt="'.$link['alt'].'" target="'.$link['target'].'" class="'.$link['class'].'">'.$link['icon']." ".$link['label'].'</a>');
        }
    } 
} 
?>
<!--/-help links-->
<!-- /-cod-accordion -->
<!-- Delivery driver -->
<?php if ('delivery_drivers' === $checkTemplateFor ) { ?>
    <div class="submit">
    <a href="users.php?role=driver" class="button action alignright"><?php esc_html_e('View Drivers', 'chat-on-desk'); ?></a>
    </div>
<?php } ?>
<!-- /- Delivery driver -->
<!-- Backinstock -->
<?php if ('backinstock' === $checkTemplateFor ) { ?>
    <div class="submit" style="clear:both">
        <a href="admin.php?page=all-cod-subscriber" class="button action alignright"><?php esc_html_e('View Subscriber', 'chat-on-desk'); ?></a>
    </div>
<?php } ?>
<!-- /- Backinstock -->
<!-- Cartbounty -->
<?php
if ('cartbounty' === $checkTemplateFor ) {
    $options = get_option('cartbounty_notification_frequency');
    if (0 === $options['hours'] ) {
        ?>
<br>
<div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <td>
                <p><span class="dashicons dashicons-info"></span> <b><?php esc_html_e('Please enable Email Notification at Cart Bounty Setting page.', 'chat-on-desk'); ?></b> <a href="<?php echo esc_url($admin_url()) . 'admin.php?page=cartbounty&tab=settings'; ?>"><?php esc_html_e('Click Here', 'chat-on-desk'); ?></a></p>
            </td>
        </tr>
    </tbody></table>
</div>
        <?php
    }
}
?>
<!-- -/ Cartbounty -->
<!-- Backinstock -->
<?php if ('bc_customer' === $checkTemplateFor ) { ?>
    <div class="cod-accordion" style="padding: 10px 10px 10px 10px">
    <input type="checkbox" name="chatondesk_bc_general[otp_enable]" id="chatondesk_bc_general[otp_enable]" <?php echo ( ( ChatOnDesk\chatondesk_get_option('otp_enable', 'chatondesk_bc_general', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/>
    <label for="chatondesk_bc_general[otp_enable]"> Enable Mobile Verification </label>
    </div>
<?php } ?>
<?php if ('rr_customer' === $checkTemplateFor ) { ?>
    <div class="cod-accordion" style="padding: 10px 10px 10px 10px">
    <input type="checkbox" name="chatondesk_rr_general[otp_enable]" id="chatondesk_rr_general[otp_enable]" <?php echo ( ( ChatOnDesk\chatondesk_get_option('otp_enable', 'chatondesk_rr_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/>
    <label for="chatondesk_rr_general[otp_enable]"> Enable Mobile Verification </label>
    </div>
<?php } ?>
<?php if ('qr_customer' === $checkTemplateFor ) { ?>
    <div class="cod-accordion" style="padding: 10px 10px 10px 10px">
    <input type="checkbox" name="chatondesk_qr_general[otp_enable]" id="chatondesk_qr_general[otp_enable]" <?php echo ( ( ChatOnDesk\chatondesk_get_option('otp_enable', 'chatondesk_qr_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/>
    <label for="chatondesk_qr_general[otp_enable]"> Enable Mobile Verification </label>
    </div>
<?php } ?>
<?php if ('eap_customer' === $checkTemplateFor ) { ?>
    <div class="cod-accordion" style="padding: 10px 10px 10px 10px">
    <input type="checkbox" name="chatondesk_eap_general[otp_enable]" id="chatondesk_eap_general[otp_enable]" <?php echo ( ( ChatOnDesk\chatondesk_get_option('otp_enable', 'chatondesk_eap_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/>
    <label for="chatondesk_eap_general[otp_enable]"> Enable Mobile Verification </label>
    </div>
<?php } ?>
<?php if ('pmp_customer' === $checkTemplateFor ) { ?>
    <div class="cod-accordion" style="padding: 10px 10px 10px 10px">
    <input type="checkbox" name="chatondesk_pmp_general[otp_enable]" id="chatondesk_pmp_general[otp_enable]" <?php echo ( ( ChatOnDesk\chatondesk_get_option('otp_enable', 'chatondesk_pmp_general', 'on') === 'on' ) ? "checked='checked'" : '' ); ?>/>
    <label for="chatondesk_pmp_general[otp_enable]"> Enable Mobile Verification </label>
    </div>
<?php } ?>