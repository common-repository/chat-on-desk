<?php
/**
 * Login with otp form template.
 * PHP version 5
 *
 * @category Template
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

$redirect = isset($_GET['redirect_to'])?$_GET['redirect_to']:$_SERVER['REQUEST_URI'];
?>
<p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
    <label for="username"><?php esc_html_e('Mobile Number', 'chat-on-desk'); ?><span class="required">*</span></label>
    <input type="tel" class="woocommerce-Input woocommerce-Input--text input-text cod_mobileno phone-valid" name="billing_phone"  id="reg_with_mob" value="">
    <input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="redirect" value="<?php echo $redirect; ?>">
    <input type="hidden" class="woocommerce-Input woocommerce-Input--text input-text" name="chatondesk_name" value="<?php echo wp_rand(0, 99999)?>">
  
    
</p>
<?php 
echo apply_filters( 'gglcptch_display_recaptcha','', 'cod_swm_form' );
?>
<p class="form-row">
    <button type="submit" class="button chatondesk_reg_with_otp_btn" name="chatondesk_reg_with_otp_btn" id="sign_with_mob_btn" value="<?php echo esc_html_e('Signup with Mobile', 'chat-on-desk'); ?>"><span class="button__text"><?php echo esc_html_e('Signup with Mobile', 'chat-on-desk'); ?></span></button>    
    <a href="javascript:void(0)" class="cod_default_signup_form" data-parentForm="register"><?php esc_html_e('Back', 'chat-on-desk'); ?></a>
</p>

<?php 
//echo do_shortcode( '[cod_verify id="signupwithotp" phone_selector="#reg_with_mob" submit_selector= "chatondesk_reg_with_otp" ]' );
?>