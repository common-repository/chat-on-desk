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
$url = add_query_arg(
    array(
        'action'    => 'foo_modal_box',
        'TB_iframe' => 'true',
        'width'     => '800',
        'height'    => '500',
    ),
    admin_url('admin.php?page=all-order-variable')
);
?>
<div class="cod-accordion">
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
						'name'  => $template['textareaNameId'],
						'sms_text'  => $template['text-body'],
						'data_parent_id'  => $template['checkboxNameId'],
						'menu_id'  => 'menu_'.$checkTemplateFor.'_'.$template['status'],
						'token'     => $template['token'],
						'moreoption' => $template['moreoption'],
					);
				    ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
					?>
                    </td>
                </tr>
            </table>
        </div>
    <?php } ?>
        <div class="" style="padding: 5px 10px 10px 10px;">
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row"> <?php esc_html_e('Send Review SMS after', 'chat-on-desk'); ?> <span class="tooltip" data-title="Enter ChatOnDesk Password"><span class="dashicons dashicons-info"></span></span>
                        </th>
                        <td>
                            <input type="number" data-parent_id="chatondesk_or_general[customer_notify]" name="chatondesk_review[schedule_day]" id="chatondesk_review[schedule_day]" min="1" max="90" value="<?php echo esc_attr(ChatOnDesk\chatondesk_get_option('schedule_day', 'chatondesk_review', '1')); ?>"  style="width: 36%;"><span class="tooltip" data-title="Max day 90"><span class="dashicons dashicons-info"></span></span>
                        </td>
                        <th scope="row"><?php esc_html_e('Days when order is marked as', 'chat-on-desk'); ?><span class="tooltip" data-title="Select Order Status"><span class="dashicons dashicons-info"></span></span>
                        </th>
                        <td>
                            <select name="chatondesk_review[review_status]" id="chatondesk_review[review_status]" data-parent_id="chatondesk_or_general[customer_notify]" style="width:100%">
                                <option value="completed" selected>
                                <?php
                                echo esc_html(ChatOnDesk\chatondesk_get_option('review_status', 'chatondesk_review', __('Completed', 'chat-on-desk')));
                                ?>
                                </option>
                                <?php
                                $order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();
                                foreach ( $order_statuses as $status ) {
                                    ?>
                                <option value="<?php echo esc_attr(strtr(strtolower($status), ' ', '-')); ?>"><?php echo esc_attr($status); ?></option>
                                <?php } ?>
                            </select>
                            <span class="tooltip" data-title="Select Order Status"><span class="dashicons dashicons-info"></span></span>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                        <input type="checkbox" data-parent_id="chatondesk_or_general[customer_notify]" name="chatondesk_review[send_at]" id="chatondesk_review[send_at]" class="notify_box" <?php echo ( ( ChatOnDesk\chatondesk_get_option('send_at', 'chatondesk_review', 'off') === 'on' ) ? "checked='checked'" : '' ); ?>/><?php esc_html_e('Send At', 'chat-on-desk'); ?> <span class="tooltip" data-title="Send At"><span class="dashicons dashicons-info"></span></span>
                        </th>
                        <td>
                            <input type="time" data-parent_id="chatondesk_review[send_at]" name="chatondesk_review[schedule_time]" id="chatondesk_review[schedule_time]" value="<?php echo esc_attr(ChatOnDesk\chatondesk_get_option('schedule_time', 'chatondesk_review', '10:00')); ?>" ><span class="tooltip" data-title="Schedule time"><span class="dashicons dashicons-info"></span></span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
