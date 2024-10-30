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
    //if ( $has_woocommerce || $has_w_p_members || $has_ultimate || $has_w_p_a_m || $has_learn_press ) 
    { ?>
<div class="cod-accordion">
    <div class="accordion-section">
        <?php if ($has_woocommerce ) { ?>
        <div class="cod-accordion-body-title " data-href="#accordion_6"> <input type="checkbox" name="chatondesk_general[buyer_checkout_otp]" id="chatondesk_general[buyer_checkout_otp]" class="notify_box" <?php echo ( ( 'on' === $chatondesk_notification_checkout_otp ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[buyer_checkout_otp]"><?php esc_html_e('OTP for Checkout', 'chat-on-desk'); ?></label>
                      
		<span class="expand_btn"></span>
        </div>
        <div id="accordion_6" class="cod-accordion-body-content">
            <table class="form-table">
            <?php
            if ($has_woocommerce || $has_ultimate || $has_w_p_a_m ) {
                $post_order_verification = ChatOnDesk\chatondesk_get_option('post_order_verification', 'chatondesk_general', 'off');
                $pre_order_verification  = ChatOnDesk\chatondesk_get_option('pre_order_verification', 'chatondesk_general', 'on');
                ?>
            <tr valign="top">
                <td scope="row" class="td-heading" colspan="2">
                    <!--Post Order Verification-->
                    <input type="checkbox" name="chatondesk_general[post_order_verification]" data-parent_id="chatondesk_general[buyer_checkout_otp]" id="chatondesk_general[post_order_verification]" class="notify_box" <?php echo ( ( 'on' === $post_order_verification ) ? "checked='checked'" : '' ); ?> data-name="checkout_otp"/><label for="chatondesk_general[post_order_verification]"><?php esc_html_e('Post Order Verification ', 'chat-on-desk'); ?></label> <small>(<?php esc_html_e('disable pre-order verification', 'chat-on-desk'); ?>)</small>
                    <!--/-Post Order Verification-->
                </td>
            </tr>
            <?php } ?>
            <?php
            if ($has_woocommerce ) {
                ?>
                    <tr valign="top">
                    <td scope="row" class="td-heading" style="width:40%">
                        <input type="checkbox" name="chatondesk_general[otp_for_selected_gateways]" id="chatondesk_general[otp_for_selected_gateways]" class=" notify_box" data-parent_id="chatondesk_general[buyer_checkout_otp]"  <?php echo ( ( 'on' === $otp_for_selected_gateways ) ? "checked='checked'" : '' ); ?> parent_accordian="otpsection"/><label for="chatondesk_general[otp_for_selected_gateways]"><?php esc_html_e('Enable OTP only for Selected Payment Options', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="Please select payment gateway for which you wish to enable OTP Verification"><span class="dashicons dashicons-info"></span></span><br /><br />
                    </td>
                    <td>
                <?php
                if ($has_woocommerce ) {
                    ?>
                    <select multiple size="5" name="chatondesk_general[checkout_payment_plans][]" id="checkout_payment_plans" class="multiselect chosen-select" data-parent_id="chatondesk_general[otp_for_selected_gateways]" data-placeholder="Select Payment Gateways">
                    <?php
                    $payment_plans = WC()->payment_gateways->payment_gateways();
                    foreach ( $payment_plans as $payment_plan ) {
                         echo '<option ';
                        if (in_array($payment_plan->id, $checkout_payment_plans, true) ) {
                            echo( 'selected' );
                        }
                         echo( ' value="' . esc_attr($payment_plan->id) . '">' . esc_attr($payment_plan->title) . '</option>' );
                    }
                    ?>
                    </select>
                    <script>jQuery(function() {jQuery(".chosen-select").chosen({width: "100%"});});</script>
                <?php } ?>
                    </td>
                </tr>
            <?php } ?>
                <tr valign="top" class="top-border">
            <?php
            if ($has_woocommerce ) {
                ?>
                    <td scope="row" class="td-heading">
                        <input type="checkbox" name="chatondesk_general[checkout_show_otp_button]" id="chatondesk_general[checkout_show_otp_button]" class="notify_box" data-parent_id="chatondesk_general[buyer_checkout_otp]" <?php echo ( ( 'on' === $checkout_show_otp_button ) ? "checked='checked'" : '' ); ?>/>
                        <label for="chatondesk_general[checkout_show_otp_button]"><?php esc_html_e('Show Verify Button next to phone field', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="Show verify button in-place of link at checkout"><span class="dashicons dashicons-info"></span></span>
                    </td>
            <?php } ?>
                </tr>
                <tr valign="top">
                    <td scope="row" class="td-heading">
            <?php
            if ($has_woocommerce ) {
                ?>
                        <input type="checkbox" name="chatondesk_general[checkout_show_otp_guest_only]" id="chatondesk_general[checkout_show_otp_guest_only]" class="notify_box" data-parent_id="chatondesk_general[buyer_checkout_otp]" <?php echo ( ( 'on' === $checkout_show_otp_guest_only ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[checkout_show_otp_guest_only]"><?php esc_html_e('Verify only Guest Checkout', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="OTP verification only for guest checkout"><span class="dashicons dashicons-info"></span></span>
            <?php } ?>
                    </td>
                </tr>
                <tr valign="top">
                    <td scope="row" class="td-heading"><?php esc_html_e('OTP Verify Button Text', 'chat-on-desk'); ?> </td>
                    <td>
                        <input type="text" name="chatondesk_general[otp_verify_btn_text]" id="chatondesk_general[otp_verify_btn_text]" class="notify_box" value="<?php echo esc_html($otp_verify_btn_text); ?>" style="width:90%" required/>
                        <span class="tooltip" data-title="Set OTP Verify Button Text"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
            </table>
        </div>
		<?php  } ?>
        
        <div class="cod-accordion-body-title" data-href="#accordion_7"> <input type="checkbox" name="chatondesk_general[buyer_signup_otp]" id="chatondesk_general[buyer_signup_otp]" class="notify_box" <?php echo ( ( 'on' === $chatondesk_notification_signup_otp ) ? "checked='checked'" : '' ); ?> > <label for="chatondesk_general[buyer_signup_otp]"><?php esc_html_e('OTP for Registration', 'chat-on-desk'); ?></label>
        <span class="expand_btn"></span>
        </div>
        <div id="accordion_7" class="cod-accordion-body-content">
            <table class="form-table">
                <tr valign="top">
                    <td scope="row" class="td-heading">
                        <?php
                        //if ( $has_woocommerce )
                        {
                        ?>
                        <input type="checkbox" name="chatondesk_general[register_otp_popup_enabled]" id="chatondesk_general[register_otp_popup_enabled]" class="notify_box" data-parent_id="chatondesk_general[buyer_signup_otp]" <?php echo ( ( 'on' === $register_otp_popup_enabled ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[register_otp_popup_enabled]"><?php esc_html_e('Register OTP in Popup', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="Register OTP in Popup"><span class="dashicons dashicons-info"></span></span>
                        <?php } ?>
                    </td>

                    <?php
                    //if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) 
                    {
                    ?>
                    <td scope="row" class="td-heading">
                        <input type="checkbox" name="chatondesk_general[allow_multiple_user]" id="chatondesk_general[allow_multiple_user]" class="notify_box" data-parent_id="chatondesk_general[buyer_signup_otp]" <?php echo ( ( 'on' === $chatondesk_allow_multiple_user ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[allow_multiple_user]"><?php esc_html_e('Allow multiple accounts with same mobile number', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="OTP at registration should be active"><span class="dashicons dashicons-info"></span></span>
                    </td>
                    <?php } ?>
                </tr>
            </table>
        </div>

        <?php if ($has_woocommerce || $has_w_p_a_m ) { ?>
        <div class="cod-accordion-body-title " data-href="#accordion_8"> <input type="checkbox" name="chatondesk_general[buyer_login_otp]" id="chatondesk_general[buyer_login_otp]" class="notify_box" <?php echo ( ( 'on' === $chatondesk_notification_login_otp ) ? "checked='checked'" : '' ); ?>> <label for="chatondesk_general[buyer_login_otp]"><?php esc_html_e('2 Factor Authentication', 'chat-on-desk'); ?></label>
        <span class="expand_btn"></span>
        </div>
        <div id="accordion_8" class="cod-accordion-body-content">
            <table class="form-table">
            <?php
            if ($has_woocommerce ) {
                ?>
                <tr valign="top">
                    <td scope="row" class="login-width td-heading">
                <?php $class = ( $off_excl_role ) ? 'notify_box nopointer disabled' : 'notify_box'; ?>
                        <input type="checkbox" name="chatondesk_general[otp_for_roles]" id="chatondesk_general[otp_for_roles]" class="<?php echo esc_attr($class); ?>" data-parent_id="chatondesk_general[buyer_login_otp]"  <?php echo ( ( 'on' === $otp_for_roles ) ? "checked='checked'" : '' ); ?>/>

                        <label for="chatondesk_general[otp_for_roles]"><?php esc_html_e('Exclude Role from LOGIN OTP', 'chat-on-desk'); ?></label>
                        <span class="tooltip" data-title="Exclude Role from LOGIN OTP"><span class="dashicons dashicons-info"></span></span><br /><br />
                    </td>
                    <td>
                <?php

                global $wp_roles;
                $roles = $wp_roles->roles;

                if (! is_array($admin_bypass_otp_login) && 'on' === $admin_bypass_otp_login ) {
                    $admin_bypass_otp_login = array( 'administrator' );
                }
                ?>
                        <select multiple size="5" name="chatondesk_general[admin_bypass_otp_login][]" id="admin_bypass_otp_login" <?php echo ( ( $off_excl_role ) ? 'disabled' : 'data-parent_id="'.'chatondesk_general[otp_for_roles]"' ); ?> class="multiselect chosen-select" data-placeholder="Select Roles OTP For login">
                <?php
                foreach ( $roles as $role_key => $role ) {
                    ?>
                        <option
                    <?php
                    if (in_array($role_key, $admin_bypass_otp_login, true) ) {
                        ?>
                            selected
                        <?php
                    }
                    ?>
                        value="<?php echo esc_attr($role_key); ?>"><?php echo esc_attr($role['name']); ?></option>
                    <?php
                }
                ?>
                    </select>
                <?php
                if ($off_excl_role ) {
                    ?>
                            <span style='color:#da4722;padding: 6px;border: 1px solid #da4722;display: block;margin-top: 15px;'><span class='dashicons dashicons-info' style='font-size: 17px;'></span>
                    <?php
                    /* translators: %s: Admin URL */
                    echo wp_kses_post(sprintf(__("Admin phone number is missing, <a href='%s'>click here</a> to add it to your profile", 'chat-on-desk'), admin_url('profile.php')));
                    ?>
                            </span>
                    <?php
                }
                ?>
                    </td>
                </tr>
            <?php } ?>
                <tr valign="top">
                    <td scope="row" class="td-heading">
                        <!--Login with popup-->
            <?php
            if ($has_woocommerce || $has_w_p_a_m ) {
                ?>
                            <input type="checkbox" name="chatondesk_general[login_popup]" id="chatondesk_general[login_popup]" class="notify_box" data-parent_id="chatondesk_general[buyer_login_otp]" <?php echo ( ( 'on' === $login_popup ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[login_popup]"><?php esc_html_e('Show OTP in Popup', 'chat-on-desk'); ?></label>
                            <span class="tooltip" data-title="Login via Username & Pwd, OTP will be asked in Popup Modal"><span class="dashicons dashicons-info"></span></span>
            <?php } ?>
                        <!--/-Login with popup-->
                    </td>
                </tr>
            </table>
        </div>
            <?php
        }
        ?>
        <!--login with otp-->
        <div class="cod-accordion-body-title " data-href="#accordion_9"> <input type="checkbox" name="chatondesk_general[login_with_otp]" id="chatondesk_general[login_with_otp]" class="notify_box" <?php echo ( ( 'on' === $login_with_otp ) ? "checked='checked'" : '' ); ?>> <label for="chatondesk_general[login_with_otp]"><?php esc_html_e('Login With OTP', 'chat-on-desk'); ?></label>
		 <?php
                        if ($has_woocommerce ) {
                            ?>
        <span class="expand_btn"></span>
        </div>
        <div id="accordion_9" class="cod-accordion-body-content">
            <table class="form-table">
                <tr valign="top">
                    <td scope="row" class="td-heading">
                        <!--Hide default Login form-->
                       
                            <input type="checkbox" name="chatondesk_general[hide_default_login_form]" id="chatondesk_general[hide_default_login_form]" class="notify_box" data-parent_id="chatondesk_general[login_with_otp]" <?php echo ( ( 'on' === $hide_default_login_form ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[hide_default_login_form]"><?php esc_html_e('Hide default Login form', 'chat-on-desk'); ?></label>
                            <span class="tooltip" data-title="Hide default login form on my account"><span class="dashicons dashicons-info"></span></span>
                        <?php } ?>
                        <!--/-Hide default Login form-->
                    </td>
                </tr>
            </table>
        </div>
        <!--login with otp-->
        
        <!--signup with mobile-->
        <div class="cod-accordion-body-title " data-href="#accordion_11"> 
        
        <?php $signup_with_mobile = ChatOnDesk\chatondesk_get_option('signup_with_mobile', 'chatondesk_general', 'off'); ?>
        
        <input type="checkbox" name="chatondesk_general[signup_with_mobile]" id="chatondesk_general[signup_with_mobile]" class="notify_box" <?php echo ( ( 'on' === $signup_with_mobile ) ? "checked='checked'" : '' ); ?>> <label for="chatondesk_general[signup_with_mobile]"><?php esc_html_e('Signup With Mobile', 'chat-on-desk'); ?></label>
        
        
        <span class="expand_btn"></span>
        </div>
        <div id="accordion_11" class="cod-accordion-body-content">
            <table class="form-table">
                <tr valign="top">
                    <td scope="row" class="td-heading">
                        <!--Signup with Mob - Default Role-->
                        <?php
                        $chatondesk_defaultuserrole = get_option('chatondesk_defaultuserrole', 'customer');
                        if (! get_role($chatondesk_defaultuserrole) ) {
                            $chatondesk_defaultuserrole = 'subscriber';
                        }
                        ?>
                        <table class="form-table">
                        <tr class="top-border">
                            <th scope="row" style="vertical-align:top;">
                                <label for="chatondesk_defaultuserrole"><?php esc_html_e('Default User Role', 'chat-on-desk'); ?></label>
                            </th>
                            <td>
                                <select name="chatondesk_defaultuserrole" id="chatondesk_defaultuserrole" data-parent_id="chatondesk_general[signup_with_mobile]">
                                    <?php
                                    foreach ( wp_roles()->roles as $rkey => $rvalue ) {

                                        if ($rkey === $chatondesk_defaultuserrole ) {
                                            $sel = 'selected=selected';
                                        } else {
                                            $sel = '';
                                        }
                                        echo '<option value="' . esc_attr($rkey) . '" ' . esc_attr($sel) . '>' . esc_attr($rvalue['name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </td>
                        </tr>
                        </table>
                        <!--Signup with Mob - Default Role-->
                    </td>
                </tr>
            </table>
        </div>
        <!--signup with mobile-->
    </div>
</div>
<br>
<?php } ?>
<!--end accordion-->

<div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
    <table class="form-table">
        <?php
        if ($has_woocommerce || $has_w_p_a_m ) {
            ?>
        <tr valign="top">
            <td scope="row"  class="td-heading">
            <!--OTP FOR Reset Password-->
                <input type="checkbox" name="chatondesk_general[reset_password]" id="chatondesk_general[reset_password]" class="notify_box" <?php echo ( ( 'on' === $enable_reset_password ) ? "checked='checked'" : '' ); ?>/><label for="chatondesk_general[reset_password]"><?php esc_html_e('OTP For Reset Password', 'chat-on-desk'); ?></label>
            <!--/-OTP FOR Reset Password-->
            </td>
            <td colspan="3" scope="row"  class="td-heading">
                <!--OTP FOR User Profile Update-->
            <?php  $enable_otp_user_update = get_option('chatondesk_otp_user_update', 'on');?>
                <input type="checkbox" name="chatondesk_otp_user_update" id="chatondesk_otp_user_update" class="notify_box" <?php echo (($enable_otp_user_update=='on')?"checked='checked'":'')?>/><label for="chatondesk_otp_user_update"><?php _e('OTP For User Update', 'chat-on-desk') ?></label>
                <!--/-OTP FOR User Profile Update-->
            </td>
        </tr>
        <?php } ?>
        <tr valign="top" class="top-border <?php echo $disablePlayground; ?>">
            <td scope="row" class="td-heading"><?php esc_html_e('OTP Template Style', 'chat-on-desk'); ?> <span class="tooltip" data-title="Select OTP Template Style"><span class="dashicons dashicons-info"></span></span>
            </td>
            <td colspan="3">
                 <?php
                $disabled = (! is_plugin_active('elementor/elementor.php')) ? "anchordisabled" : "";
				$post = get_page_by_path( 'cod_modal_style', OBJECT, 'chat-on-desk' ); 
                ?>              
                <a href= <?php get_admin_url() ?>"edit.php?post_name=cod_modal_style" class="button <?php echo $disabled; ?> action" target="_blank" style="float:left;"><?php esc_html_e('Edit With Elementor', 'chat-on-desk'); ?></a>
                <?php if(!empty($post->post_type)){?>
                <a href= "javascript:void(0)" temp-style="cod_modal_style" class="btn-outline cod_btn_reset_style" style="float:left;"><?php esc_html_e('Reset', 'chat-on-desk'); ?></a>
                <?php }?>
				<span class="cod_reset_style"></span>	
			<?php
			if($disabled!='')
			{
            ?>		
            <span><?php esc_html_e('To edit, please install elementor plugin', 'chat-on-desk'); ?>	</span>
			<?php
			}
			?>
            </td>
        </tr>
        <tr valign="top" class="top-border otp-section-token <?php echo $disablePlayground; ?>">
            <td scope="row" class="td-heading" style="vertical-align: top;"><?php esc_html_e('OTP Template', 'chat-on-desk'); ?></td>
            <td colspan="3" style="margin-top:20px;position:relative">
            <?php
			$params = array(
				'name'  => 'chatondesk_message[sms_otp_send]',
				'sms_text'  => $sms_otp_send,
				'data_parent_id'  => 'sms_otp_send',
				'menu_id'  => 'menu_otp_section',
				'token'     => array('[otp]'=>'OTP','[shop_url]'=>'Shop Url'),
			);
			ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
			?>
            <span><?php esc_html_e('Template to be used for sending OTP', 'chat-on-desk'); ?><hr />
                <?php
                /* translators: %s: OTP tag */
                echo wp_kses_post(sprintf(__('It is mandatory to include %s tag in template content.', 'chat-on-desk'), '[otp]')); ?>
                <br /><br /><b><?php esc_html_e('Optional Attributes', 'chat-on-desk'); ?></b><br />
            <ul>
                <li><b>length</b> &nbsp; - <?php esc_html_e('length of OTP, default is 4, accepted values between 3 and 8,', 'chat-on-desk'); ?></li>
                <li><b>retry</b> &nbsp;&nbsp;&nbsp;&nbsp; - <?php esc_html_e('set how many times otp message can be sent in specific time default is 5,', 'chat-on-desk'); ?></li>
                <li><b>validity</b> &nbsp;- <?php esc_html_e('set validity of the OTP default is 15 minutes', 'chat-on-desk'); ?></li>
            </ul>
                <b>eg</b> : <code>[otp length="6" retry="2" validity="10"]</code></span>
            </td>
        </tr>
    </table>
</div>
<a href="https://youtu.be/bvmfEk_h9h0" target="_blank" class="btn-outline"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>