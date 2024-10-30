<div class="chatondesk_wrapper cod-accordion <?php echo $disablePlayground; ?>" style="padding: 5px 10px 10px 10px;">
    <strong><?php echo wp_kses_post($chatondesk_helper); ?></strong>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Chat On Desk Username', 'chat-on-desk'); ?>
                <span class="tooltip" data-title="Enter Chat On Desk"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td style="vertical-align: top;">
                <?php
                if ($islogged ) {
                    echo esc_attr($chatondesk_name); 
                }
                ?>
                <input type="text" name="chatondesk_gateway[chatondesk_name]" id="chatondesk_gateway[chatondesk_name]" value="<?php echo esc_attr($chatondesk_name); ?>" data-id="chatondesk_name" class="<?php echo esc_attr($hidden); ?>">
                <input type="hidden" name="action" value="save_chatondesk_settings" />
                <?php
                echo wp_nonce_field('wp_save_chatondesk_settings_nonce', 'save_chatondesk_settings_nonce', true, false);
                ?>
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('your Chat On Desk user name', 'chat-on-desk'); ?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Chat On Desk Password', 'chat-on-desk'); ?>
                <span class="tooltip" data-title="Enter Chat On Desk Password"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php
                if ($islogged ) {
                    echo '*****';
                }
                ?>
                <input type="text" name="chatondesk_gateway[chatondesk_password]" id="chatondesk_gateway[chatondesk_password]" value="<?php echo esc_attr($chatondesk_password); ?>" data-id="chatondesk_password" class="<?php echo esc_attr($hidden); ?>">
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('your Chat On Desk password', 'chat-on-desk'); ?></span>
            </td>
        </tr>
        <?php do_action('verify_chatondesk_user_button'); ?>
        <tr valign="top">
            <th scope="row">
                <?php esc_html_e('Chat On Desk Channel', 'chat-on-desk'); ?>
                <span class="tooltip" data-title="Chat On Desk Channel"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <?php if ($islogged ) { ?>
                    <?php echo esc_attr($chatondesk_api); ?>
                    <input type="hidden" value="<?php echo esc_attr($chatondesk_api); ?>" name="chatondesk_gateway[chatondesk_api]" id="chatondesk_gateway[chatondesk_api]">
                <?php } else { ?>
                <select parent_accordian="general" name="chatondesk_gateway[chatondesk_api]" id="chatondesk_gateway[chatondesk_api]" disabled>
                    <option value="SELECT"><?php esc_html_e('SELECT', 'chat-on-desk'); ?></option>
                </select>
                <span class="<?php echo esc_attr($hidden); ?>"><?php esc_html_e('channel name for SMS\'s to be sent', 'chat-on-desk'); ?></span>
                <?php } ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row">
            </th>
            <td>
                <?php if ($islogged ) { ?>
                <a href="#" class="button-primary" onclick="codlogout(); return false;"><?php esc_html_e('Logout', 'chat-on-desk'); ?></a>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>
<br>
<?php if ($islogged ) { ?>
<div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
    <table class="form-table">
	     <!-- <tr valign="top">
		<td class="td-heading" style="width:30%">
			<?php esc_html_e('Alternate Channel', 'chat-on-desk'); ?>
			<span class="tooltip" data-title="Select alternate channel"><span class="dashicons dashicons-info"></span></span>
		</td>                                        
		<td> -->
        <?php
       /* $content = '<select name="'.'chatondesk_general[alternate_channel][]" id="alternate_channel" multiple class="multiselect chosen-select">';
        foreach ( $alternate_channel as $key => $channel ) {
            $content .= '<option value="' . esc_attr($channel) . '" selected="selected"></option>';
        }
        $content .= '</select>';

        $content .= '<script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>';
        echo $content; */
        ?>
                                          <!--  </td>
                                        </tr> -->
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Send Admin SMS To', 'chat-on-desk'); ?>
                <span class="tooltip" data-title="Please make sure that the number must be without country code (e.g.: 8010551055)"><span class="dashicons dashicons-info"></span></span>
            </th>
            <td>
                <select id="send_admin_sms_to" onchange="toggle_send_admin_alert(this);">
                    <option value=""><?php esc_html_e('Custom', 'chat-on-desk'); ?></option>
                    <option value="post_author" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Post Author', 'chat-on-desk'); ?></option>
                    <?php if (is_plugin_active('woocommerce-shipping-local-pickup-plus/woocommerce-shipping-local-pickup-plus.php') ) { ?>
                    <option value="store_manager" <?php echo ( trim($sms_admin_phone) === 'store_manager' ) ? 'selected="selected"' : ''; ?>><?php esc_html_e('Store Manager', 'chat-on-desk'); ?></option>
                    <?php } ?>
                </select>
                <script>
                function toggle_send_admin_alert(obj)
                {
                    if(obj.value == "post_author")
                    {
                        tagInput1.addTag(obj.value);
                    }
                    if(obj.value == "store_manager")
                    {
                        tagInput1.addTag(obj.value);
                    }
                }
                </script>
                <input type="text" name="chatondesk_message[sms_admin_phone]" class="admin_no" id="chatondesk_message[sms_admin_phone]" <?php echo ( trim($sms_admin_phone) === 'post_author' ) ? 'readonly="readonly"' : ''; ?> value="<?php echo esc_attr($sms_admin_phone); ?>"><br /><br />
                <span><?php esc_html_e('Admin order sms notifications will be sent to this number.', 'chat-on-desk'); ?></span>
            </td>
        </tr>
    </table>
</div>
<?php } ?>
