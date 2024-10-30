<?php
/**
 * Wc checkout
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */ 
 
namespace ChatOnDesk;
if (! defined('ABSPATH') ) {
    exit;
}
if (! is_plugin_active('woocommerce/woocommerce.php') ) {
    return; 
}
 /**
  * PHP version 5
  *
  * @category Handler
  * @package  ChatOnDesk
  * @author   Chat On Desk <support@cozyvision.com>
  * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
  * @link     https://www.chatondesk.com/
  *
  * WooCommerceCheckOutForm class
  */
class WooCommerceCheckOutForm extends \ChatOnDesk\FormInterface
{
    /**
     * If OTP is enabled only for guest users.
     *
     * @var $guest_check_out_only If OTP is enabled only for guest users.
     */
    private $guest_check_out_only;

    /**
     * Show OTP verification button
     *
     * @var $show_button Show OTP verification button
     */
    private $show_button;

    /**
     * Woocommerce default registration form key
     *
     * @var $form_session_var Woocommerce default registration form key
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::WC_CHECKOUT;

    /**
     * Woocommerce block checkout registration form key
     *
     * @var $form_session_var2 Woocommerce checkout popup form key
     */
    private $form_session_var2 = \ChatOnDesk\FormSessionVars::WC_CHECKOUT_POPUP;

    /**
     * Woocommerce Post checkout form key
     *
     * @var $form_session_var3 Woocommerce Post checkout form key
     */
    private $form_session_var3 = \ChatOnDesk\FormSessionVars::WC_POST_CHECKOUT;

    /**
     * Popup enabled
     *
     * @var $popup_enabled Popup enabled
     */
    private $popup_enabled;

    /**
     * Payment methods
     *
     * @var $payment_methods Payment methods
     */
    private $payment_methods;

    /**
     * Verify OTP only for selected gateways
     *
     * @var $otp_for_selected_gateways Verify OTP only for selected gateways
     */
    private $otp_for_selected_gateways;

    /**
     * 
     * Handles form.
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter('woocommerce_checkout_fields', array( $this, 'getCheckoutFields' ), 1, 1);
		if (isset($_REQUEST['option']) && sanitize_text_field(wp_unslash($_REQUEST['option']) === 'chatondesk-woocommerce-checkout-process') ) {
          add_action('woocommerce_after_checkout_validation', array( $this, 'woocommerceSiteCheckoutErrors' ), 10, 2); 
		}
        $post_verification = chatondesk_get_option('post_order_verification', 'chatondesk_general');
        if ('on' === $post_verification ) {
            add_action('woocommerce_thankyou_order_received_text', array( $this, 'sendPostOrderOtp' ), 10, 2);
            add_action('woocommerce_order_details_after_order_table', array( $this, 'orderDetailsAfterPostOrderOtp' ), 10);
        }

        //add_action( 'woocommerce_blocks_enqueue_checkout_block_scripts_after', array( $this, 'showButtonOnBlockPage' ) );

        $this->payment_methods           = maybe_unserialize(chatondesk_get_option('checkout_payment_plans', 'chatondesk_general'));
        $this->otp_for_selected_gateways = ( chatondesk_get_option('otp_for_selected_gateways', 'chatondesk_general') === 'on' ) ? true : false;
        $this->popup_enabled             = ( (chatondesk_get_option('checkout_show_otp_button', 'chatondesk_general') !== 'on') || (chatondesk_get_option('checkout_otp_popup', 'chatondesk_general', 'off') === 'on') ) ? true : false;
        $this->guest_check_out_only      = ( chatondesk_get_option('checkout_show_otp_guest_only', 'chatondesk_general') === 'on' ) ? true : false;
        $this->show_button               = ( chatondesk_get_option('checkout_show_otp_button', 'chatondesk_general') === 'on' ) ? true : false;
        $checkout_otp_enabled = chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general');
        $cod_register_otp_enabled = chatondesk_get_option('buyer_signup_otp', 'chatondesk_general');

        if (( 'on' === $checkout_otp_enabled ) || ( 'on' !== $checkout_otp_enabled && 'on' === $cod_register_otp_enabled ) ) {
            add_action('woocommerce_review_order_after_submit', array( $this, 'addCodShortcode' ), 1, 1);
        }
        add_action('woocommerce_after_checkout_billing_form', array( $this, 'myCustomCheckoutField' ), 99);
        //in aero checkout modal was not showing
        add_action('wfacp_footer_after_print_scripts', array( $this, 'aeroCheckoutPage' ), 99);
        add_action('wfacp_after_billing_phone_field', array( $this, 'myCustomCheckoutField' ), 99);
        add_action('woocommerce_order_partially_refunded', array( $this, 'refundedPartiallyAmount' ), 99, 2);                                                     
        $this->routeData();
    }
    /**
     * Send sms partially refunded.
     *
     * @param int $order_id   order_id
     * @param int $refundedid refundedid
     *
     * @return void
     */
    public function refundedPartiallyAmount($order_id, $refundedid)
    {
    
        if (! $order_id ) {
            return;
        }
        $order                = wc_get_order($order_id);
        $admin_sms_data       = array();
		if ( version_compare( WC_VERSION, '7.1', '<' ) ) {
          $refundamount         = get_post_meta($refundedid, '_refund_amount', true);
		  $buyerNumber          = get_post_meta( $order_id, '_billing_phone', true );
        } else {
         $refundamount         = $order->get_meta('_refund_amount'); 
         $buyerNumber          = $order->get_meta('_billing_phone');
		}
		$new_status           = 'partially_refunded';
        $buyer_sms_body       = chatondesk_get_option('sms_body_'.$new_status, 'chatondesk_message');
        $buyer_sms_body       = str_replace('[order_status]', 'partially refunded', $buyer_sms_body);    
        $buyer_sms_body       =str_replace('[refund_amount]', $refundamount, $buyer_sms_body);
        $sms_data['sms_body'] = $buyer_sms_body;
        $buyerMessage         =  WooCommerceCheckOutForm::pharseSmsBody($sms_data, $order_id);
        do_action('cod_send_sms', $buyerNumber, $buyerMessage['sms_body']);    
        /* Admin Notification  */
        $admin_phone_number    = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
        $admin_phone_number    = str_replace('postauthor', 'post_author', $admin_phone_number);    
        if (chatondesk_get_option('admin_notification_' . $new_status, 'chatondesk_general', 'on') === 'on' && ! empty($admin_phone_number) ) {
            // send sms to post author.
            $has_sub_order = metadata_exists('post', $order_id, 'has_sub_order');
            if (( strpos($admin_phone_number, 'post_author') !== false ) 
                && ( ( 0 !== $order->get_parent_id() ) || ( ( 0 === $order->get_parent_id() ) && empty($has_sub_order) ) ) 
            ) {
                $order_items = $order->get_items();
                $first_item  = current($order_items);
                $prod_id     = $first_item['product_id'];
                $product     = wc_get_product($prod_id);
                $author_no   = apply_filters('cod_post_author_no', $prod_id);

                if (0 === $order->get_parent_id() ) {
                    $admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
                } else {
                    $admin_phone_number   = $author_no;
                }
            }
            if (( strpos($admin_phone_number, 'store_manager') !== false ) && ( ( 0 === $order->get_parent_id() ) && empty($has_sub_order) ) ) {

                $author_no              = apply_filters('cod_store_manager_no', $order);

                $admin_phone_number     = str_replace('store_manager', $author_no, $admin_phone_number);
            }
            $default_template           = SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_' . str_replace('-', '_', strtoupper($new_status)));
            $default_admin_sms          = ( ( ! empty($default_template) ) ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED') );
            $admin_sms_body             = chatondesk_get_option('admin_sms_body_' . $new_status, 'chatondesk_message', $default_admin_sms);
            $admin_sms_body             = str_replace('[refund_amount]', $refundamount, $admin_sms_body);
            $admin_sms_body             = str_replace('[order_status]', 'partially refunded', $admin_sms_body);    
            $admin_sms_data['sms_body'] = $admin_sms_body;
            $admin_sms_data['number']   = $admin_phone_number;
            $adminMessage  =  WooCommerceCheckOutForm::pharseSmsBody($admin_sms_data, $order_id);
            do_action('cod_send_sms', $admin_phone_number, $adminMessage['sms_body']);    
            
            
        }    
    }
    
    /**
     * Routes data.
     *
     * @return void
     */
    public function aeroCheckoutPage()
    {
        echo '<script>
		jQuery(window).load(function(){
		  var modal = jQuery(".modal.chatondeskModal");
		  jQuery("body").append(modal.detach());
		});</script>';
    }
    
    /**
     * This function shows checkout error message.
     *
     * @param array  $data   Data array.
     * @param string $errors Errors.
     *
     * @return void
     */
    public function woocommerceSiteCheckoutErrors( $data, $errors )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (isset($_SESSION['sa_mobile_verified']) ) {
            unset($_SESSION['sa_mobile_verified']);
            return $errors;
        }
        $verify = check_ajax_referer('woocommerce-process_checkout', 'woocommerce-process-checkout-nonce', false);
        if (!$verify) {
            $errors->add('registration-error-invalid-nonce', __('Sorry, nonce did not verify.', 'chat-on-desk'));
        }
        if (! \ChatOnDesk\SmsAlertUtility::isBlank(array_filter($errors->errors)) ) {
            return $errors;
        }
        \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var2);
        $user_phone = ( ! empty($_REQUEST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_REQUEST['billing_phone'])) : '';
        if (\ChatOnDesk\SmsAlertUtility::isBlank($user_phone)) {
            $errors->add('registration-error-invalid-phone', __('Please enter phone number.', 'chat-on-desk'));
        } else if (! isset($user_phone) || ! \ChatOnDesk\SmsAlertUtility::validatePhoneNumber($user_phone) ) {
            global $phoneCodLogic;
            $errors->add('registration-error-invalid-phone', str_replace('##phone##', $user_phone, $phoneCodLogic->_get_otp_invalid_format_message()));
        } 
        if (isset($_POST['createaccount']) && $_POST['createaccount']) {
            $username = isset($_POST['account_username'])?$_POST['account_username']:$data['billing_email'];
            $error = false;
            if (email_exists($data['billing_email']) ) {
                $errors->add('registration-error-email-exists', __('An account is already registered with your email address. <a href="#" class="showlogin">Please log in.</a>', 'woocommerce'));
            }
            if (isset($_POST['account_username'])) {
                if (username_exists($_POST['account_username'])) {
                    $errors->add('registration-error-username-exists', __('An account is already registered with that username. Please choose another.', 'woocommerce'));
                }
            }
            
            if (chatondesk_get_option('allow_multiple_user', 'chatondesk_general') !== 'on' ) {

                $getusers = \ChatOnDesk\SmsAlertUtility::getUsersByPhone('billing_phone', $user_phone);
                if (count($getusers) > 0 ) {
                    $errors->add('registration-error-number-exists', __('An account is already registered with this mobile number. Please login.', 'chat-on-desk'));
                }
            }
        }
        if ($errors->get_error_code() ) {
            throw new \Exception($errors->get_error_message());
        }
        if (isset($_REQUEST['checkout'])) {
            return $this->processFormFields($errors);
        } else if (isset($_REQUEST['order_verify'])) {
            $this->myCustomCheckoutFieldProcess();
        }
    }

    /**
     * This function processed form fields.
     *
     * @param array $errors Errors array.
     *
     * @return void
     */
    public function processFormFields( $errors )
    {
        $phone_no  = ( ! empty($_POST['billing_phone']) ) ? sanitize_text_field(wp_unslash($_POST['billing_phone'])) : '';
        $phone_num = preg_replace('/[^0-9]/', '', $phone_no);
        chatondesk_site_challenge_otp(null, null, $errors, $phone_num, 'phone', null);
    }

    /**
     * Autocomplete and changes in pincode fields.
     *
     * @return void
     */
    public function addCodShortcode()
    { 
        $checkout_otp_enabled = chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general');
        echo '
	<script>
	var cod_signup_checkout = "'.get_option('woocommerce_enable_signup_and_login_from_checkout').'";
	var cod_guest_checkout = "'.get_option('woocommerce_enable_guest_checkout').'";
	var cod_user_logged_in = "'.is_user_logged_in().'";
	var cod_is_popup = "'.$this->popup_enabled.'";
	var cod_selected_payment = "'.$this->otp_for_selected_gateways.'";
	var cod_post_verify = "'.( ( chatondesk_get_option('post_order_verification', 'chatondesk_general') === 'on' ) ? true : false ).'";
	var cod_enable_country = "'.( ( chatondesk_get_option('checkout_show_country_code', 'chatondesk_general') === 'on' ) ? true : false ).'";
	var cod_ask_otp = "'.( $this->guest_check_out_only && is_user_logged_in() ? false : (($checkout_otp_enabled === 'on' ) ? true : false) ).'";
	var cod_paymentMethods = '.json_encode($this->payment_methods).';
	var cod_register_otp = "'.( ( chatondesk_get_option('buyer_signup_otp', 'chatondesk_general') === 'on' ) ? true : false ).'";
	var cod_btn_text = "'.chatondesk_get_option('otp_verify_btn_text', 'chatondesk_general', '').'";
	function chatondesk() {
	 if (cod_signup_checkout == "yes" && cod_guest_checkout != "yes" && cod_register_otp && !cod_user_logged_in)
	  {
		addCodShortcode();  
	  }		  
      if (cod_ask_otp && !cod_selected_payment && !cod_post_verify)
	  {
		addCodShortcode();
	  } else {	jQuery("input[name=payment_method],input[name=radio-control-wc-payment-method-options]").each(function(e, t) {
		if (!cod_post_verify)
		 {
            var o = jQuery(t).val();
            if (jQuery(t).is(":checked") && cod_ask_otp && ((jQuery.inArray(o, cod_paymentMethods) > -1) || !cod_selected_payment) )
			{
				addCodShortcode();
			}
		 }
        });
	  }
	jQuery("input[name=payment_method]").click(function() {   onCodChangePayment(jQuery("input[name=payment_method]:checked").val());
    });  
    jQuery(document).on("payment_method_selected",function() {
		 var payment = jQuery("input[name=payment_method]:checked").val(); 
		   onCodChangePayment(payment);
    });
	 jQuery(".woocommerce #createaccount").click(function() {
        if (1 == jQuery(this).prop("checked") && cod_register_otp)
		{
			addCodShortcode();
		} else {
			if (cod_post_verify)
			{
				removeCodShortcode();
			} else {
			onCodChangePayment(jQuery("input[name=payment_method]:checked").val());
			}
		}
    });		
	if (1 == jQuery(".woocommerce #createaccount").prop("checked") && cod_register_otp)
	{		
	  addCodShortcode();
	}
	} 
	jQuery(document).on("updated_checkout",function() {
    jQuery("#order_verify_field,#chatondesk_otp_token_submit").addClass("cod-default-btn-hide");
    chatondesk();
	if (cod_enable_country && jQuery(\'.woocommerce [name="billing_phone"]:hidden\').length == 0)
	{
	initialiseCodCountrySelector(".woocommerce #billing_phone");
	jQuery(\'.woocommerce [name="billing_phone"]:hidden\').val(jQuery(\'.woocommerce [name="billing_phone"]\').intlTelInput("getNumber"));
	}
    });
	function onCodChangePayment(payment)
	{
		if (!cod_post_verify)
		{
		if ((cod_ask_otp && ((jQuery.inArray(payment, cod_paymentMethods) > -1) || !cod_selected_payment)) || ((1 == jQuery(".woocommerce #createaccount").prop("checked") || 0 == jQuery(".woocommerce #createaccount").length) && cod_register_otp && !cod_user_logged_in))
		{
			addCodShortcode();
		} else {
			removeCodShortcode();
		}
		}
	}
	function addCodShortcode()
	{
		removeCodShortcode();
		jQuery(".phone-valid,#billing_phone").trigger("keyup");
		reset_cod_otp_val();
		if (cod_is_popup)
		{
		var uniqueid = generateCodUniqueId();
		if (jQuery("form").hasClass("wc-block-components-form"))
		{
			add_chatondesk_button(".wc-block-components-checkout-place-order-button","#phone",uniqueid,cod_btn_text);
			jQuery(document).on("click", "#cod_verify_"+uniqueid,function(event){
				event.preventDefault();
			send_cod_otp(this,".wc-block-components-checkout-place-order-button","#phone","","");
			});
		} else {
		add_chatondesk_button(".place-order #place_order","#billing_phone",uniqueid,cod_btn_text);
		jQuery(document).on("click", "#cod_verify_"+uniqueid,function(event){
				event.preventDefault();
		send_cod_otp(this,".place-order #place_order","#billing_phone","","");
		});
		}
		jQuery(document).on("keypress", "input", function(e){
				if (e.which === 13)
				{
					e.preventDefault();
					var pform 	= jQuery(this).parents("form");
					pform.find("#cod_verify_"+uniqueid).trigger("click");
				}
		});
		} else {
		jQuery("#order_verify_field,#chatondesk_otp_token_submit").removeClass("cod-default-btn-hide")	
		}
	}

	function removeCodShortcode()
	{
		if (cod_is_popup)
		{
		jQuery(".place-order .chatondesk_otp_btn_submit,.wc-block-components-checkout-place-order-button.chatondesk_otp_btn_submit").remove();
		jQuery(".place-order #place_order,.wc-block-components-checkout-place-order-button").removeClass("cod-default-btn-hide");
		} else {
		jQuery("#order_verify_field,#chatondesk_otp_token_submit").addClass("cod-default-btn-hide")	
		}
	}

		function generateCodUniqueId()
		{
			return Math.random().toString(36).substr(2, 9);
		}
		function reset_cod_otp_val() 
		{
		   "11111" == jQuery("#order_verify").val() && jQuery("#order_verify").val("");
		 } 
	  </script>
	  ';        
    }

    /**
     * Onpage load when customer logged in billing phone at checkout page when country code is enabled.
     *
     * @param array $fields Existing field array.
     *
     * @return void
     */
    public function getCheckoutFields( $fields )
    {

        $phone = empty($_POST['billing_phone']) ? '' : sanitize_text_field(wp_unslash($_POST['billing_phone']));
        if (! empty($phone) ) {
            $_POST['billing_phone'] = \ChatOnDesk\SmsAlertUtility::formatNumberForCountryCode($phone);
        }
        return $fields;
    }

    /**
     * Shows Verification button on block page.
     *
     * @return void
     */
    public function showButtonOnBlockPage()
    {
        add_action('wp_footer', array( $this, 'addCodShortcode' ), 1, 1);
    }

    /**
     * Checks if Form is enabled.
     *
     * @return void
     */
    public static function isFormEnabled()
    {
        $user_authorize     = new chatondesk_Setting_Options();
        $islogged           = $user_authorize->is_user_authorised();
        $signup_on_checkout = get_option('woocommerce_enable_signup_and_login_from_checkout');
        return ( $islogged && is_plugin_active('woocommerce/woocommerce.php') && ( 'on' === chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general') || ( 'on' === chatondesk_get_option('buyer_signup_otp', 'chatondesk_general') && 'yes' === $signup_on_checkout ) ) ) ? true : false;
    }

    /**
     * Routes data.
     *
     * @return void
     */
    public function routeData()
    {
        if (! array_key_exists('option', $_GET) ) {
            return;
        }
        $option = trim(sanitize_text_field(wp_unslash($_GET['option'])));
        if (strcasecmp($option, 'chatondesk-woocommerce-checkout') === 0 || strcasecmp($option, 'chatondesk-woocommerce-post-checkout') === 0 ) {
            $this->handleWoocommereCheckoutForm($_POST);
        }

    }

    /**
     * Handles woocommerce checkout form.
     *
     * @param array $getdata Checkout form fields.
     *
     * @return void
     */
    public function handleWoocommereCheckoutForm( $getdata )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! empty($_GET['option']) && sanitize_text_field(wp_unslash($_GET['option'])) === 'chatondesk-woocommerce-post-checkout' ) {
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var3);
        } else {
            \ChatOnDesk\SmsAlertUtility::initialize_transaction($this->form_session_var);
        }
        $phone_num = Chatondesk::checkPhoneNos($getdata['user_phone']);
        $email = !empty($getdata['user_email']) ? $getdata['user_email'] : null;
        chatondesk_site_challenge_otp('test', $email, null, trim($phone_num), 'phone');
    }

    /**
     * Checks if verification code is entered or not.
     *
     * @return void
     */
    public function checkIfVerificationCodeNotEntered()
    {
        if ($this->popup_enabled ) {
            return false;
        }

        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (empty($_POST['order_verify']) ) {
            wc_add_notice(__('Your mobile number is not verified yet. Please verify your mobile number.', 'chat-on-desk'), 'error');
            return true;
        }
    }
    
    /**
     * Adds a custom checkout field.
     *
     * @param array $checkout Currently not in use.
     *
     * @return void
     */
    public function myCustomCheckoutField( $checkout )
    {
        ?>
        <script>
        setTimeout(function() {
            if (jQuery(".modal.chatondeskModal").length==0)    
            {            
            var popup = '<?php echo str_replace(array("\n","\r","\r\n"), '', (get_chatondesk_template("template/otp-popup.php", array(), true))); ?>';
            jQuery('body').append(popup);
            }
        }, 200);
        </script>
        <?php	
		
        if ($this->guest_check_out_only && is_user_logged_in() ) {
            return;
        }

        $checkout_otp_enabled = chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general');
         
        if ('on' === $checkout_otp_enabled && ! $this->popup_enabled ) {
            $this->showValidationButtonOrText();
            echo '<input type="hidden" name="order_verify" id="order_verify">';

            $this->commonButtonOrLinkEnableDisableScript();

        }
    }

    /**
     * Checks if validation button is to be displayed or popup.
     *
     * @param string $popup Currently not in use.
     *
     * @return void
     */
    public function showValidationButtonOrText( $popup = false )
    {
        $this->showButtonOnPage();
    }

    /**
     * Shows a button on checkout page.
     *
     * @return void
     */
    public function showButtonOnPage()
    {
        $otp_verify_btn_text = chatondesk_get_option('otp_verify_btn_text', 'chatondesk_general', '');
        echo wp_kses_post(
            '<button type="submit" class="button alt" id="chatondesk_otp_token_submit" value="'
            . $otp_verify_btn_text . '" ><span class="button__text">' . $otp_verify_btn_text . '</span></button>'
        );
    }

    /**
     * Common script to enable or disable button or link.
     *
     * @return void
     */
    public function commonButtonOrLinkEnableDisableScript()
    {
        $this->enableDisableScriptForButtonOnPage();
    }

    /**
     * Enable or disable verify button on page.
     *
     * @return void
     */
    public function enableDisableScriptForButtonOnPage()
    {
		$otp_resend_timer = !empty(SmsAlertUtility::get_elementor_data("cod_otp_re_send_timer"))?SmsAlertUtility::get_elementor_data("cod_otp_re_send_timer"):chatondesk_get_option('otp_resend_timer', 'chatondesk_general', '15');
        echo '<script> jQuery(document).ready(function() {';
        echo 'jQuery(".woocommerce-message").length>0&&(jQuery("#order_verify").focus(),jQuery("#salert_message").addClass("woocommerce-message"));';
        echo 'jQuery("#chatondesk_otp_token_submit").click(function(o){';
        echo 'var action_url = "'. esc_url(site_url()) . '/?option=chatondesk-shortcode-ajax-verify";';
        
        if (is_checkout() && chatondesk_get_option('checkout_show_country_code', 'chatondesk_general') === 'on' ) {
            echo 'm=jQuery(this).parents("form").find("input[name=billing_phone]").intlTelInput("getNumber"),';
        } else {
            echo 'm=jQuery(this).parents("form").find("input[name=billing_phone]").val(),';
        }
        echo 'a=jQuery("div.woocommerce");a.addClass("processing").block({message:null,overlayCSS:{background:"#fff",opacity:.6}}),
            jQuery(this).addClass("cod-otp-btn-init");
				codInitOTPProcess(
					this,
					action_url,
					{user_phone:m},
					' . esc_attr($otp_resend_timer) . ',
					function(resp){
						a.removeClass( "processing" ).unblock();
					},
					function(resp){
						a.removeClass( "processing" ).unblock();
					}
				)
			return false;
		}),';
        echo '""!=jQuery("input[name=billing_phone]").val()&&jQuery("#chatondesk_otp_token_submit").prop( "disabled", false );
		jQuery(document).on("input change","input[name=billing_phone]",function() {
			jQuery(this).val(jQuery(this).val().replace(/^0+/, "").replace(/\s+/g, ""));
			
			var phone;
			if (typeof cod_otp_settings !=  "undefined" && cod_otp_settings["show_countrycode"]=="on" )
			{
				 phone = jQuery("input[name=billing_phone]:hidden").val();
			} else {
				 phone = jQuery(this).val();
			}
			
			if (typeof phone != "undefined" && phone.replace(/\s+/g, "").match(' . esc_attr(\ChatOnDesk\SmsAlertConstants::getPhonePattern()) . ') && (typeof jQuery(".cod_phone_error") == "undefined" || jQuery(".cod_phone_error").text()==""))  
			{
			
				jQuery("#chatondesk_otp_token_submit").prop( "disabled", false );
			
		} else { jQuery("#chatondesk_otp_token_submit").prop( "disabled", true ); }}),jQuery("input[name=billing_phone]").trigger( "input").trigger( "change")});</script>';
    }

    /**
     * Process the custom checkout form.
     *
     * @return void
     */
    public function myCustomCheckoutFieldProcess()
    {
        $post_verification    = chatondesk_get_option('post_order_verification', 'chatondesk_general');
        $checkout_otp_enabled = chatondesk_get_option('buyer_checkout_otp', 'chatondesk_general');
        $buyer_checkout_otp   = chatondesk_get_option('buyer_signup_otp', 'chatondesk_general');

        if ('on' === $post_verification ) {
            return;
        }
            
        if (!isset($_REQUEST['order_verify']) ) {
            return;
        }    

        if (! isset($_SESSION[ $this->form_session_var ]) && ! isset($_SESSION[ $this->form_session_var2 ]) && ! isset($_SESSION[ $this->form_session_var3 ]) && ( 'on' !== $checkout_otp_enabled && 'on' === $buyer_checkout_otp && empty($_REQUEST['createaccount']) ) ) {
            return;
        }

        if ($this->guest_check_out_only && is_user_logged_in() ) {
            return;
        }

        if (empty($_REQUEST['createaccount']) && ! $this->isPaymentVerificationNeeded() ) {
            return;
        }

        if ($this->checkIfVerificationCodeNotEntered() ) {
            return;
        }
    }

    /**
     * Checks if OTP verification is required.
     *
     * @param string $payment_method Payment method selected.
     *
     * @return void
     */
    public function isPaymentVerificationNeeded( $payment_method = null )
    {
        if (! $this->otp_for_selected_gateways ) {
            return true;
        }

        $payment_method = ( ! empty($_POST['payment_method']) ? sanitize_text_field(wp_unslash($_POST['payment_method'])) : $payment_method );
        return in_array($payment_method, $this->payment_methods, true);
    }

    /**
     * Handles failed OTP verification
     *
     * @param string $user_login   User login.
     * @param string $user_email   Email Id.
     * @param string $phone_number Phone number of the user.
     *
     * @return void
     */
    public function handle_failed_verification( $user_login, $user_email, $phone_number )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) && ! isset($_SESSION[ $this->form_session_var2 ]) && ! isset($_SESSION[ $this->form_session_var3 ]) ) {
            return;
        }
        if (isset($_SESSION[ $this->form_session_var2 ]) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
        } elseif (isset($_SESSION[ $this->form_session_var3 ]) ) {

            if ((chatondesk_get_option('checkout_show_otp_button', 'chatondesk_general') !== 'on') || (chatondesk_get_option('checkout_otp_popup', 'chatondesk_general', 'off') === 'on') ) {
                wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('INVALID_OTP'), 'error'));
                exit();
            } else {
                wc_add_notice(\ChatOnDesk\SmsAlertUtility::_get_invalid_otp_method(), 'error');
                if (! empty($_SERVER['REQUEST_URI']) ) {
                    wp_safe_redirect(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])));
                    exit();
                }
            }
        } else {
            wc_add_notice(\ChatOnDesk\SmsAlertUtility::_get_invalid_otp_method(), 'error');
        }
    }

    /**
     * Handles Post OTP verification
     *
     * @param string $redirect_to  The url to be redirected to.
     * @param string $user_login   User login.
     * @param string $user_email   Email Id.
     * @param string $password     Password.
     * @param string $phone_number Phone number of the user.
     * @param array  $extra_data   Extra form data.
     *
     * @return void
     */
    public function handle_post_verification( $redirect_to, $user_login, $user_email, $password, $phone_number, $extra_data )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) && ! isset($_SESSION[ $this->form_session_var2 ]) && ! isset($_SESSION[ $this->form_session_var3 ]) ) {
            return;
        }

        if (isset($_SESSION[ $this->form_session_var2 ]) ) {
            wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
            $this->unsetOTPSessionVariables();
            exit();
        } elseif (isset($_SESSION[ $this->form_session_var3 ]) ) {
            $order_id = ! empty($_REQUEST['o_id']) ? sanitize_text_field(wp_unslash($_REQUEST['o_id'])) : '';
            $output   = update_post_meta($order_id, '_chatondesk_post_order_verification', 1);
            if ($output > 0 ) {
                wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response(SmsAlertMessages::showMessage('VALID_OTP'), 'success'));
                $this->unsetOTPSessionVariables();
                exit();
            } 
        } else {
            $this->unsetOTPSessionVariables();
        }
    }

    /**
     * Unset OTP session variables.
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[ $this->tx_session_id ]);
        unset($_SESSION[ $this->form_session_var ]);
        unset($_SESSION[ $this->form_session_var2 ]);
        unset($_SESSION[ $this->form_session_var3 ]);
    }

    /**
     * Checks if ajax form is active.
     *
     * @param bool $is_ajax Whether the request is ajax request.
     *
     * @return void
     */
    public function is_ajax_form_in_play( $is_ajax )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        return ( isset($_SESSION[ $this->form_session_var ]) || isset($_SESSION[ $this->form_session_var2 ]) || isset($_SESSION[ $this->form_session_var3 ]) ) ? true : $is_ajax;
    }

    /**
     * Handle form options.
     *
     * @return void
     */
    public function handleFormOptions()
    {
        add_action('add_meta_boxes', array( $this, 'addSendSmsMetaBox' ));
        add_action('wp_ajax_wc_chatondesk_sms_send_order_sms', array( $this, 'sendCustomSms' ));

        if (is_plugin_active('woocommerce/woocommerce.php') ) {
            add_action('cod_addTabs', array( $this, 'addTabs' ), 1);
            add_filter('codDefaultSettings', __CLASS__ . '::addDefaultSetting', 1);
        }
        add_action('woocommerce_admin_order_data_after_billing_address', array( $this, 'addAdminGeneralOrderVariationDescription' ), 10, 1);
    }

    /**
     * Add Post order verification status in admin section.
     *
     * @param object $order Order object.
     *
     * @return void
     */
    public function addAdminGeneralOrderVariationDescription( $order )
    {
        $order_id          = $order->get_id();
        $post_verification = get_post_meta($order_id, '_chatondesk_post_order_verification', true);
        if ($post_verification ) {
            echo '
			<p><strong>Chat On Desk Post Verified</strong></p>
			<span class="dashicons dashicons-yes" style="color: #fff;width: 22px;height: 22px;background: #07930b;border-radius: 25px;line-height: 22px;" title="Chat On Desk Post Verified"></span>';
        }
    }

    /**
     * Get order variables.
     *
     * @return void
     */
    public static function getOrderVariables()
    {

        $variables = array(
        '[order_id]'             => 'Order Id',
        '[order_status]'         => 'Order Status',
        '[order_amount]'         => 'Order Amount',
        '[refund_amount]'         => 'Refund Amount',                            
        '[order_date]'           => 'Order Date',
        '[store_name]'           => 'Store Name',
        '[item_name]'            => 'Product Name',
        '[item_name_qty]'        => 'Product Name with Quantity',
        '[billing_first_name]'   => 'Billing First Name',
        '[billing_last_name]'    => 'Billing Last Name',
        '[billing_company]'      => 'Billing Company',
        '[billing_address_1]'    => 'Billing Address 1',
        '[billing_address_2]'    => 'Billing Address 2',
        '[billing_city]'         => 'Billing City',
        '[billing_state]'        => 'Billing State',
        '[billing_postcode]'     => 'Billing Postcode',
        '[billing_country]'      => 'Billing Country',
        '[billing_email]'        => 'Billing Email',
        '[billing_phone]'        => 'Billing Phone',
        '[shipping_first_name]'  => 'Shipping First Name',
        '[shipping_last_name]'   => 'Shipping Last Name',
        '[shipping_company]'     => 'Shipping Company',
        '[shipping_address_1]'   => 'Shipping Address 1',
        '[shipping_address_2]'   => 'Shipping Address 2',
        '[shipping_city]'        => 'Shipping City',
        '[shipping_state]'       => 'Shipping State',
        '[shipping_postcode]'    => 'Shipping Postcode',
        '[shipping_country]'     => 'Shipping Country',
        '[order_currency]'       => 'Order Currency',
        '[payment_method]'       => 'Payment Method',
        '[payment_method_title]' => 'Payment Method Title',
        '[shipping_method]'      => 'Shipping Method',
        '[shop_url]'             => 'Shop Url',
        '[customer_note]'        => 'Customer Note',
        );
        return $variables;
    }

    /**
     * Get Customer templates.
     *
     * @return void
     */
    public static function getCustomerTemplates()
    {
        $order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();

        $order_statuses['partially_refunded'] = 'Partially Refunded';
        $chatondesk_notification_status     = chatondesk_get_option('order_status', 'chatondesk_general', '');
        $chatondesk_notification_onhold     = ( is_array($chatondesk_notification_status) && array_key_exists('on-hold', $chatondesk_notification_status) ) ? $chatondesk_notification_status['on-hold'] : 'on-hold';
        $chatondesk_notification_processing = ( is_array($chatondesk_notification_status) && array_key_exists('processing', $chatondesk_notification_status) ) ? $chatondesk_notification_status['processing'] : 'processing';
        $chatondesk_notification_completed  = ( is_array($chatondesk_notification_status) && array_key_exists('completed', $chatondesk_notification_status) ) ? $chatondesk_notification_status['completed'] : 'completed';
        $chatondesk_notification_cancelled  = ( is_array($chatondesk_notification_status) && array_key_exists('cancelled', $chatondesk_notification_status) ) ? $chatondesk_notification_status['cancelled'] : 'cancelled';

        $chatondesk_notification_notes = chatondesk_get_option('buyer_notification_notes', 'chatondesk_general', 'on');
        $sms_body_new_note           = chatondesk_get_option('sms_body_new_note', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_NOTE'));

        $templates = array();
        foreach ( $order_statuses as $ks  => $order_status ) {

            $prefix = 'wc-';
            $vs     = $ks;
            if (substr($vs, 0, strlen($prefix)) === $prefix ) {
                $vs = substr($vs, strlen($prefix));
            }

            $current_val = ( is_array($chatondesk_notification_status) && array_key_exists($vs, $chatondesk_notification_status) ) ? $chatondesk_notification_status[ $vs ] : $vs;

            $current_val = ( $current_val === $vs ) ? 'on' : 'off';

            $checkbox_name_id = 'chatondesk_general[order_status][' . $vs . ']';
            $textarea_name_id = 'chatondesk_message[sms_body_' . $vs . ']';

            $default_template = SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_' . str_replace('-', '_', strtoupper($vs)));
            $text_body        = chatondesk_get_option('sms_body_' . $vs, 'chatondesk_message', ( ( ! empty($default_template) ) ? $default_template : SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_STATUS_CHANGED') ));

            $templates[ $ks ]['title']          = 'When Order is ' . ucwords($order_status);
            $templates[ $ks ]['enabled']        = $current_val;
            $templates[ $ks ]['status']         = $vs;
            $templates[ $ks ]['chkbox_val']     = $vs;
            $templates[ $ks ]['text-body']      = $text_body;
            $templates[ $ks ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $ks ]['textareaNameId'] = $textarea_name_id;
            $templates[ $ks ]['moreoption']     = 1;
            $templates[ $ks ]['token']          = self::getvariables($vs);
        }

        $new_note                                = self::getOrderVariables();
        $new_note['[note]']                      = 'Order Note';
        $templates['new-note']['title']          = 'When a new note is added to order';
        $templates['new-note']['enabled']        = $chatondesk_notification_notes;
        $templates['new-note']['status']         = 'new-note';
        $templates['new-note']['text-body']      = $sms_body_new_note;
        $templates['new-note']['checkboxNameId'] = 'chatondesk_general[buyer_notification_notes]';
        $templates['new-note']['textareaNameId'] = 'chatondesk_message[sms_body_new_note]';
        $templates['new-note']['token']          = $new_note;
        return $templates;
    }

    /**
     * Get multi vendor admin templates.
     *
     * @return void
     */
    public static function getMVAdminTemplates()
    {
        $order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? self::multivendorstatuses() : array();

        $templates = array();
        foreach ( $order_statuses as $ks  => $order_status ) {

            $vs               = $ks;
            $current_val      = chatondesk_get_option('multivendor_notification_' . $vs, 'chatondesk_general', 'on');
            $checkbox_name_id = 'chatondesk_general[multivendor_notification_' . $vs . ']';
            $textarea_name_id = 'chatondesk_message[multivendor_sms_body_' . $vs . ']';
            $default_template = SmsAlertMessages::showMessage('DEFAULT_NEW_USER_' . str_replace('-', '_', strtoupper($vs)));
            $text_body        = chatondesk_get_option('multivendor_sms_body_' . $vs, 'chatondesk_message', ( ( ! empty($default_template) ) ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED') ));

            $templates[ $ks ]['title']          = 'When Vendor Account is ' . ucwords($order_status);
            $templates[ $ks ]['enabled']        = $current_val;
            $templates[ $ks ]['status']         = $vs;
            $templates[ $ks ]['text-body']      = $text_body;
            $templates[ $ks ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $ks ]['textareaNameId'] = $textarea_name_id;
            $templates[ $ks ]['moreoption']     = 1;
            $templates[ $ks ]['token']          = array(
            '[username]'   => 'Username',
            '[store_name]' => 'Store Name',
            '[shop_url]'   => 'Shop URL',
            );
        }
        return $templates;
    }

    /**
     * Get admin templates.
     *
     * @return void
     */
    public static function getAdminTemplates()
    {
        $order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();

        $order_statuses['partially_refunded'] = 'Partially Refunded';
        $templates = array();
        foreach ( $order_statuses as $ks  => $order_status ) {

            $prefix = 'wc-';
            $vs     = $ks;
            if (substr($vs, 0, strlen($prefix)) === $prefix ) {
                $vs = substr($vs, strlen($prefix));
            }

            $current_val      = chatondesk_get_option('admin_notification_' . $vs, 'chatondesk_general', 'on');
            $checkbox_name_id = 'chatondesk_general[admin_notification_' . $vs . ']';
            $textarea_name_id = 'chatondesk_message[admin_sms_body_' . $vs . ']';
            $default_template = SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_' . str_replace('-', '_', strtoupper($vs)));
            $text_body        = chatondesk_get_option('admin_sms_body_' . $vs, 'chatondesk_message', ( ( ! empty($default_template) ) ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED') ));

            $templates[ $ks ]['title']          = 'When Order is ' . ucwords($order_status);
            $templates[ $ks ]['enabled']        = $current_val;
            $templates[ $ks ]['status']         = $vs;
            $templates[ $ks ]['text-body']      = $text_body;
            $templates[ $ks ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $ks ]['textareaNameId'] = $textarea_name_id;
            $templates[ $ks ]['moreoption']     = 1;
            $templates[ $ks ]['token']          = self::getvariables($vs);
        }
        return $templates;
    }

    /**
     * 
     * Add tabs to smsalert settings at backend.
     *
     * @param array $tabs array of existing tabs.
     *
     * @return void
     */
    public static function addTabs( $tabs = array() )
    {
        $customer_param = array(
        'checkTemplateFor' => 'wc_customer',
        'templates'        => self::getCustomerTemplates(),
        );

        $admin_param = array(
        'checkTemplateFor' => 'wc_admin',
        'templates'        => self::getAdminTemplates(),
        );

        $multi_vendor_param = array(
        'checkTemplateFor' => 'wc_mv_vendor',
        'templates'        => self::getMVAdminTemplates(),
        );

        $tabs['woocommerce']['nav']                                     = 'Woocommerce';
        $tabs['woocommerce']['icon']                                    = 'dashicons-list-view';
        $tabs['woocommerce']['inner_nav']['wc_customer']['title']       = 'Customer Notifications';
        $tabs['woocommerce']['inner_nav']['wc_customer']['tab_section'] = 'customertemplates';

        $tabs['woocommerce']['inner_nav']['wc_customer']['tabContent'] = $customer_param;
        $tabs['woocommerce']['inner_nav']['wc_customer']['filePath']   = 'views/message-template.php';
        $tabs['woocommerce']['inner_nav']['wc_customer']['help_links']                        = array(
        'youtube_link' => array(
        'href'   => 'https://youtu.be/91ek7RjRavo',
        'target' => '_blank',
        'alt'    => 'Watch steps on Youtube',
        'class'  => 'btn-outline',
        'label'  => 'Youtube',
        'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

        ),
        'kb_link'      => array(
        'href'   => 'https://kb.smsalert.co.in/knowledgebase/woocommerce-sms-notifications/#notification-to-buyer',
        'target' => '_blank',
        'alt'    => 'Read how to use customer notifications',
        'class'  => 'btn-outline',
        'label'  => 'Documentation',
        'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
        ),

        );
        $tabs['woocommerce']['inner_nav']['wc_customer']['first_active'] = true;
        $tabs['woocommerce']['inner_nav']['wc_admin']['title']           = 'Admin Notifications';
        $tabs['woocommerce']['inner_nav']['wc_admin']['tab_section']     = 'admintemplates';
        $tabs['woocommerce']['inner_nav']['wc_admin']['tabContent']      = $admin_param;
        $tabs['woocommerce']['inner_nav']['wc_admin']['filePath']        = 'views/message-template.php';
        $tabs['woocommerce']['inner_nav']['wc_admin']['help_links']                        = array(
        'youtube_link' => array(
        'href'   => 'https://youtu.be/91ek7RjRavo',
        'target' => '_blank',
        'alt'    => 'Watch steps on Youtube',
        'class'  => 'btn-outline',
        'label'  => 'Youtube',
        'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

        ),
        'kb_link'      => array(
        'href'   => 'https://kb.smsalert.co.in/knowledgebase/woocommerce-sms-notifications/#notification-to-admin',
        'target' => '_blank',
        'alt'    => 'Read how to use admin notifications',
        'class'  => 'btn-outline',
        'label'  => 'Documentation',
        'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
        ),

        );
        if (is_plugin_active('dc-woocommerce-multi-vendor/dc_product_vendor.php') || is_plugin_active('dokan-lite/dokan.php') || is_plugin_active('wc-frontend-manager/wc_frontend_manager.php') ) {
            $tabs['woocommerce']['inner_nav']['wc_mv_vendor']['title']       = 'Multi Vendor';
            $tabs['woocommerce']['inner_nav']['wc_mv_vendor']['tab_section'] = 'multivendortemplates';
            $tabs['woocommerce']['inner_nav']['wc_mv_vendor']['tabContent']  = $multi_vendor_param;
            $tabs['woocommerce']['inner_nav']['wc_mv_vendor']['filePath']    = 'views/message-template.php';
        } 
        return $tabs;
    }

    /**
     * 
     * Gets multivendor account status's.
     *
     * @return void
     */
    public static function multivendorstatuses()
    {
        return array(
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        );
    }

    /**
     * 
     * Adds default settings for plugin.
     *
     * @param array $defaults array of default settings.
     *
     * @return void
     */
    public static function addDefaultSetting( $defaults = array() )
    {
        $order_statuses = is_plugin_active('woocommerce/woocommerce.php') ? wc_get_order_statuses() : array();
        $order_statuses['partially_refunded'] = 'Partially Refunded';
        foreach ( $order_statuses as $ks => $vs ) {
            $prefix = 'wc-';
            if (substr($ks, 0, strlen($prefix)) === $prefix ) {
                $ks = substr($ks, strlen($prefix));
            }
            $defaults['chatondesk_general'][ 'admin_notification_' . $ks ] = 'off';
            $defaults['chatondesk_general']['order_status'][ $ks ]         = '';
            $defaults['chatondesk_message'][ 'admin_sms_body_' . $ks ]     = '';
            $defaults['chatondesk_message'][ 'sms_body_' . $ks ]           = '';
        }

        $mv_statuses = is_plugin_active('woocommerce/woocommerce.php') ? self::multivendorstatuses() : array();
        foreach ( $mv_statuses as $ks  => $mv_status ) {

            $defaults['chatondesk_general'][ 'multivendor_notification_' . $ks ] = 'off';
            $defaults['chatondesk_message'][ 'multivendor_sms_body_' . $ks ]     = '';
        }
        return $defaults;
    }
	
	/**
     * 
     * Adds default settings for plugin.
     *
     * @param array $sms_data array containing sms text and number.
     * @param int   $order_id Order Id.
     *
     * @return void
     */
    public static function pharseSmsBody( $sms_data, $order_id )
    {
        if (empty($sms_data['sms_body']) ) {
            return $sms_data;
        }

        $content         = $sms_data['sms_body'];
        $order_variables = get_post_custom($order_id);
        $order           = wc_get_order($order_id);
		$order_refunds = array_reverse($order->get_refunds());
		$refund_reason = '';
		if(!empty($order_refunds))
		{
			foreach( $order_refunds as $order_refund ){
				if( method_exists( $order_refund, 'get_reason' ) ) {
					$refund_reason = $order_refund->get_reason();
				}
				else{
					$refund_reason = $order_refund->get_refund_reason();
				}
			}
		}
        $order_status    = $order->get_status();
        $order_items     = $order->get_items(array( 'line_item', 'shipping' ));
        $order_note      = ( ! empty($sms_data['note']) ? $sms_data['note'] : '' );
        $rma_status          = ( ! empty($sms_data['rma_status']) ? $sms_data['rma_status'] : '' );
        $rma_id          = ( ! empty($sms_data['rma_id']) ? $sms_data['rma_id'] : '' );
        
        
        if (strpos($content, 'orderitem') !== false ) {
            $content = self::saParseOrderItemData($order_items, $content);
        }
        if (strpos($content, 'shippingitem') !== false ) {
            $content = self::saParseOrderItemData($order_items, $content);
        }

        $order_item_products = array_filter(
            $order_items, function ($o) {
                return get_class($o) === 'WC_Order_Item_Product'; 
            }
        );

        $item_name          = implode(
            ', ',
            array_map(
                function ( $o ) {
                        return $o['name'];
                },
                $order_item_products
            )
        );
        $item_name_with_qty = implode(
            ', ',
            array_map(
                function ( $o ) {
                        return sprintf('%s [%u]', $o['name'], $o['qty']);
                },
                $order_item_products
            )
        );
        $store_name         = get_bloginfo();
        $shop_url           = get_site_url();
        $date_format        = 'F j, Y';
        $date_tag           = '[order_date]';

        if (preg_match_all('/\[order_date.*?\]/', $content, $matched) ) {
            $date_tag    = $matched[0][0];
            $date_params = \ChatOnDesk\SmsAlertUtility::parseAttributesFromTag($date_tag);
            $date_format = array_key_exists('format', $date_params) ? $date_params['format'] : 'F j, Y';
        }
        
        $order_date = (!empty($order->get_date_created()))? $order->get_date_created()->date($date_format) : '';
        $total_amount = $order->get_total();
        $find    = array(
        '[order_id]',
        $date_tag,
        '[order_status]',
		'[refund_reason]',
        '[rma_status]',
        '[first_name]',
        '[item_name]',
        '[item_name_qty]',
        '[order_amount]',
        '[refund_amount]',        
        '[note]',
        '[rma_number]',
        '[order_pay_url]',
        '[wc_order_id]',
        '[customer_note]',
        '[shipping_method]'
        );
        $replace = array(
        $order->get_order_number(),
        $order_date,
        $order_status,
		$refund_reason,
        $rma_status,
        '[billing_first_name]',
        wp_specialchars_decode($item_name),
        wp_specialchars_decode($item_name_with_qty),
        $total_amount,
        $total_amount,           
        $order_note,
        $rma_id,
        $order->get_checkout_payment_url(),
        $order_id,
        $order->get_customer_note(),
		$order->get_shipping_method()
        );
        $content = str_replace($find, $replace, $content);
		if ( version_compare( WC_VERSION, '8.2', '<' ) ) {
		    if(!empty($order_variables))
			{
				$content = self::saParseOrderVariableData($order_variables, $content);
				
				foreach ( $order_variables as &$value ) {
					$value = $value[0];
				}
				unset($value);
				$order_variables      = array_combine(
					array_map(
						function ( $key ) {
								return '[' . ltrim($key, '_') . ']'; 
						},
						array_keys($order_variables)
					),
					$order_variables
				);
				$sms_data['sms_body'] = str_replace(array_keys($order_variables), array_values($order_variables), $content);
			}
			else{	
			 $sms_data['sms_body'] = $content;
			}	
		}
		else {
		  $order_variables   = $order->get_data();
		  $content = self::saParseWcOrderVariableData($order_variables, $content);
		  if (!empty($order_variables['meta_data'])) {
				foreach ($order_variables['meta_data'] as $metaData) {
					$metaData = $metaData->get_data();
					$order_variables[$metaData['key']] = $metaData['value'];        
				}			
				$order_variables      = array_combine(
						array_map(
							function ( $key ) {
									return '[' .ltrim($key, '_'). ']'; 
							},
							array_keys($order_variables)
						),
						$order_variables
					);
				$sms_data['sms_body'] = str_replace(array_keys($order_variables), array_values($order_variables), $content);
			} else{
				$sms_data['sms_body'] = $content;
			}
		}

        return $sms_data;
    }

    /**
     * 
     * Sends a custom SMS.
     *
     * @param array $data currently not in use.
     *
     * @return void
     */
    public function sendCustomSms( $data )
    {
        $order_id = empty($_POST['order_id']) ? '' : sanitize_text_field(wp_unslash($_POST['order_id']));
        $sms_body = empty($_POST['sms_body']) ? '' : sanitize_textarea_field(wp_unslash($_POST['sms_body']));
        $buyer_sms_data             = array();
		if ( version_compare( WC_VERSION, '7.1', '<' ) ) {
          $buyer_sms_data['number']   = get_post_meta( $order_id, '_billing_phone', true );
		} else {
		  $order       = wc_get_order($order_id);
          $buyer_sms_data['number']   = $order->get_meta('_billing_phone');
		}
        
        $buyer_sms_data['sms_body'] = $sms_body;
        $buyer_sms_data             = apply_filters('cod_wc_order_sms_customer_before_send', $buyer_sms_data, $order_id);
        wp_send_json(Chatondesk::sendsms($buyer_sms_data));
        exit();
    }

    /**
     * 
     * Adds default settings for plugin.
     *
     * @param array $data array containing order id and note.
     *
     * @return void
     */
    public static function trigger_new_customer_note( $data )
    {

        if (chatondesk_get_option('buyer_notification_notes', 'chatondesk_general') === 'on' ) {
            $order_id                   = $data['order_id'];
            $buyer_sms_body             = chatondesk_get_option('sms_body_new_note', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_BUYER_NOTE'));
            $buyer_sms_data             = array();
            $order       = wc_get_order($order_id );
			if ( version_compare( WC_VERSION, '7.1', '<' ) ) {
              $buyer_sms_data['number']   = get_post_meta( $order_id , '_billing_phone', true );
			} else {
			  $buyer_sms_data['number']   = $order->get_meta('_billing_phone');
            }
            $buyer_sms_data['sms_body'] = $buyer_sms_body;
            $buyer_sms_data['note']     = $data['customer_note'];

            $buyer_sms_data = apply_filters('cod_wc_order_sms_customer_before_send', $buyer_sms_data, $order_id);

            $buyer_response = Chatondesk::sendsms($buyer_sms_data);
            $response       = json_decode($buyer_response, true);

            if ('success' === $response['status'] ) {
                $order->add_order_note(__('Order note SMS Sent to buyer', 'chat-on-desk'));
            } else {
                $order->add_order_note($response['description']['desc']);
            }
        }
    }

    /**
     * 
     * Adds a custom sms meta box.
     *
     * @return void
     */
    public function addSendSmsMetaBox()
    {
        add_meta_box(
            'wc_chatondesk_send_sms_meta_box',
            'Chat On Desk (Custom SMS)',
            array( $this, 'displaySendSmsMetaBox' ),
            'shop_order',
            'side',
            'default'
        );
    }

    /**
     * 
     * Displays send sms meta box.
     *
     * @param object $data order object.
     *
     * @return void
     */
    public static function displaySendSmsMetaBox( $data )
    {
        global $woocommerce;
        $post_type = get_post_type($data);
        if ('shop_order' === $post_type ) {
            $data = new \WC_Order($data->ID);
        }
        $order_id = $data->get_id();

        $username  = chatondesk_get_option('chatondesk_name', 'chatondesk_gateway');
        $password  = chatondesk_get_option('chatondesk_password', 'chatondesk_gateway');
        $templates    = Chatondesk::getTemplates($username, $password);
        //$templates = (array)json_decode($result, true);

        wp_enqueue_script('admin-chatondesk-scripts', COD_MOV_URL . 'js/admin.js', array( 'jquery' ), \ChatOnDesk\SmsAlertConstants::SA_VERSION, true);

        wp_localize_script(
            'admin-chatondesk-scripts',
            'chat-on-desk',
            array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            )
        );
        if ('shop_order' !== $post_type ) {
            echo '<style>.inside{position:relative}.woocommerce-help-tip{color:#666;display:inline-block;font-size:1.1em;font-style:normal;height:16px;line-height:16px;position:relative;vertical-align:middle;width:16px}.woocommerce-help-tip::after{font-family:Dashicons;speak:none;font-weight:400;font-variant:normal;text-transform:none;line-height:1;-webkit-font-smoothing:antialiased;margin:0;text-indent:0;position:absolute;top:0;left:0;width:100%;height:100%;text-align:center;content:"";cursor:help}</style>';

            echo '<div id="wc_chatondesk_send_sms_meta_box" class="postbox ">
				<h2 class="hndle ui-sortable-handle"><span style="font-size: 22px;">Chat On Desk (Custom SMS)</span></h2>
					<div class="inside">';
        }
        ?>
                        <select name="chatondesk_templates" id="chatondesk_templates" style="width:87%;" onchange="return codselecttemplate(this, '#wc_chatondesk_sms_order_message');">
                        <option value=""><?php esc_html_e('Select Template', 'chat-on-desk'); ?></option>
        <?php
        if (!empty($templates['description']) && is_array($templates['description']) && ( ! array_key_exists('desc', $templates['description']) ) ) {
            foreach ( $templates['description'] as $template ) {
                ?>
                        <option value="<?php echo esc_textarea(!empty($template['Smstemplate']['template'])?$template['Smstemplate']['template']:json_encode($template)); ?>"><?php echo esc_attr(!empty($template['Smstemplate']['title'])?$template['Smstemplate']['title']:$template['Structuredtemplate']['name']); ?></option>
                <?php
            }
        }
        ?>
                        </select>
                        <span class="woocommerce-help-tip" data-tip="You can add templates from your www.chatondesk.com Dashboard" title="You can add templates&#13&#10from your&#13&#10www.chatondesk.com Dashboard"></span>
                        <p><textarea type="text" name="wc_chatondesk_sms_order_message" id="wc_chatondesk_sms_order_message" class="input-text token-area" style="width: 100%;margin-top: 15px;" rows="4" value=""></textarea></p>
                        <div id="menu_custom" class="cod-menu-token" role="listbox"></div>
                        <input type="hidden" class="wc_sms_alert_order_id" id="wc_sms_alert_order_id" value="<?php echo esc_attr($order_id); ?>" >
                        <p><a class="button tips" id="wc_chatondesk_sms_order_send_message" data-tip="<?php esc_html_e('Send an SMS to the billing phone number for this order.', 'chat-on-desk'); ?>"><?php esc_html_e('Send SMS', 'chat-on-desk'); ?></a>
                        <span id="wc_chatondesk_sms_order_message_char_count" style="color: green; float: right; font-size: 16px;">0</span></p>
                        <div id="custom_token_list" style="display:none"></div>
        <?php
        if ('shop_order' !== $post_type ) {
            echo '</div></div>';
        }
        ?>
        <script>
        jQuery(document).ready(function(){
                    custom_sms_token('<?php echo esc_attr($order_id); ?>');
                });
        </script>
        <?php
    }

    /**
     * 
     * Gets order item meta.
     *
     * @param object $item Order item.
     * @param string $code meta key.
     *
     * @return void
     */
    public static function saWcGetOrderItemMeta( $item, $code )
    {
        $code      = str_replace('__', ' ', $code);
        $item_data = $item->get_data();

        foreach ( $item_data as $i_key => $i_val ) {

            if ($i_key === $code ) {
                $val = $i_val;
                break;
            } else {
                if ('meta_data' === $i_key ) {
                    $item_meta_data = $item->get_meta_data();
                    foreach ( $item_meta_data as $mkey => $meta ) {
                        if ($code === $mkey ) {
                            $meta_value = $meta->get_data();
                            $temp       = maybe_unserialize($meta_value['value']);
                            if (is_array($temp) ) {
                                $val = $temp;
                                break;
                            } else {
                                $val = $meta_value['value'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $val;
    }

    /**
     * 
     * Change keys recursively.
     *
     * @param object $arr array.
     * @param string $set key.
     *
     * @return void
     */
    public static function recursiveChangeKey( $arr, $set = '' )
    {
        if (is_numeric($set) ) {
            $set = '';
        }
        if (! empty($set) ) {
            $set = $set . '.';
        }
        if (is_array($arr) ) {
            $new_arr = array();
            foreach ( $arr as $k => $v ) {
                $new_arr[ $set . $k ] = is_array($v) ? self::recursiveChangeKey($v, $set . $k) : $v;
            }
            return $new_arr;
        }
        return $arr;
    }

    /**
     * Sa parse orderItem data.
     * attributes can be used : order_id,name,product_id,variation_id,quantity,tax_class,subtotal,subtotal_tax,total,total_tax.
     * properties : list="2" , format="%s,$d".
     * [orderitem list='2' name product_id quantity subtotal].
     *
     * @param array  $order_items array of order items.
     * @param string $content     Content.
     *
     * @return void
     */
    public static function saParseOrderItemData( $order_items, $content )
    {

        $pattern = get_shortcode_regex();

        preg_match_all('/\[orderitem(.*?)\]/', $content, $matches);
        $current_var_type = 'line_item';
        if (empty($matches[0]) ) {
            $current_var_type = 'shipping';
            preg_match_all('/\[shippingitem(.*?)\]/', $content, $matches);
        }

        $shortcode_tags = $matches[0];
        $parsed_codes   = array();
        foreach ( $shortcode_tags as $tag ) {
            $r_tag                = preg_replace('/\[|\]+/', '', $tag);
            $parsed_codes[ $tag ] = shortcode_parse_atts($r_tag);
        }

        $r_text       = '';
        $replaced_arr = array();

        foreach ( $parsed_codes as $token => &$parsed_code ) {
            $replace_text = '';
            $item_iterate = ( ! empty($parsed_code['list']) && $parsed_code['list'] > 0 ) ? (int) $parsed_code['list'] : 0;
            $format       = ( ! empty($parsed_code['format']) ) ? $parsed_code['format'] : '';
            $eq_index     = ( isset($parsed_code['eq']) ) ? (string) $parsed_code['eq'] : '';

            $prop = array();
            $tmp  = array();
            foreach ( $parsed_code as $kcode => $code ) {
                if (! in_array($kcode, array( 'orderitem', 'shippingitem', 'list', 'format', 'eq' ), true) ) {
                    $parts = array();
                    if (strpos($code, '.') !== false ) {
                        $parts = explode('.', $code);
                        $code  = array_shift($parts);
                    }

                    $sno = 0;

                    if (! empty($eq_index) && $eq_index > -1 ) {
                        $tmp_array    = array_keys($order_items);
                        $specific_key = $tmp_array[ $eq_index ];
                        if (array_key_exists($specific_key, $order_items) ) {
                            $temp_item                    = $order_items[ $specific_key ];
                            $order_items                  = array();
                            $order_items[ $specific_key ] = $temp_item;
                        }
                    }

                    foreach ( $order_items as $item_id => $item ) {
                        if ($item->get_type() === $current_var_type ) {
                            if (( $item_iterate > 0 ) && ( $sno >= $item_iterate ) ) {
                                break;
                            }

                            $tmp_code = str_replace('__', ' ', $code);

                            $attr_val = ( ! empty($item[ $tmp_code ]) ) ? $item[ $tmp_code ] : self::saWcGetOrderItemMeta($item, $code);

                            if (! empty($attr_val) ) {

                                if (! empty($parts) ) {
                                    $attr_val = self::getRecursiveVal($parts, $attr_val);
                                    $attr_val = is_array($attr_val) ? 'Array' : $attr_val;
                                }

                                if (! empty($format) ) {
                                    $prop[] = $attr_val;
                                } else {

                                    $tmp[] = $attr_val;
                                }
                            }
                            $sno++;
                        }
                    }
                }
            }

            if (! empty($format) ) {
                $tmp[] = vsprintf($format, $prop);
            }

            $replaced_arr[ $token ] = implode(', ', $tmp);
        }
        return str_replace(array_keys($replaced_arr), array_values($replaced_arr), $content);
    }
    
    
	/**
     * Sa parse order variable data.
     *
     * @param object $order_variables order_variables.
     * @param object $content         content.
     *
     * @return void
     */
    public static function saParseWcOrderVariableData($order_variables, $content)
    {
	   foreach ( $order_variables as $meta_key => $value ) {
            if (is_array($value) ) {
				foreach($value as $key=>$val)
				{
					$variables[ $meta_key.'_'.$key ] = $val;
				}
            } else {
                $variables[ $meta_key ] = $value;
				if($meta_key == 'currency')
				{
					$variables[ 'order_currency' ] = $value;
				}
            }
        }
		if(!empty($order_variables['meta_data']))
		{
			foreach ($order_variables['meta_data'] as $metaData) {
				$metaData = $metaData->get_data();
				$variables[$metaData['key']] = $metaData['value'];        
			}
		}
		
        foreach ($variables as $key => $val) {
            if (gettype($val) == 'string') {
                
                $replaced_arr[ $key ] = $val;
            } elseif ($key == '_wc_shipment_tracking_items') {
                foreach ($val[0] as $k => $v) {
                    $replaced_arr[ $k ] = $v;
                }
            }            
        }
        
        preg_match_all('/\[_wc_shipment_tracking_items(.*?)\]/', $content, $matches);
        
        $shortcode_tags = $matches[0];
        $parsed_codes   = array();
        foreach ( $shortcode_tags as $tag ) {
            $r_tag                = preg_replace('/\[|\]+/', '', $tag);
            $parsed_codes[ $tag ] = shortcode_parse_atts($r_tag);
        }
        foreach ( $parsed_codes as $token => &$parsed_code ) {
            
            foreach ( $parsed_code as $kcode => $code ) {
                $parts = array();
                if (strpos($code, '.') !== false ) {
                    $parts = explode('.', $code);
                    $code  = array_shift($parts);
                }
            
                $find      = array_shift($parsed_code);        
                $content = str_replace('['.$find.']', $replaced_arr[$parts[1]], $content);
            }
        }		
		$replace_keys = array_map(
				function ($k) {
					return '['.$k.']';
				}, array_keys($replaced_arr)
			);	 	
        return str_replace($replace_keys, array_values($replaced_arr), $content);
    }
	
    /**
     * Sa parse orderVariable data.
     *
     * @param object $order_variables order_variables.
     * @param object $content         content.
     *
     * @return void
     */
    public static function saParseOrderVariableData($order_variables, $content)
    {
	  if(!empty($order_variables))
	  {		  
        foreach ( $order_variables as $meta_key => &$value ) {
            $temp = maybe_unserialize($value[0]);

            if (is_array($temp) ) {
                $variables[ $meta_key ] = $temp;
            } else {
                $variables[ $meta_key ] = $value[0];
            }
        }
        foreach ($variables as $key => $val) {
            if (gettype($val) == 'string') {
                
                $replaced_arr[ $key ] = $val;
            } elseif ($key == '_wc_shipment_tracking_items') {
                foreach ($val[0] as $k => $v) {
                    $replaced_arr[ $k ] = $v;
                }
            }            
        }
        
        preg_match_all('/\[_wc_shipment_tracking_items(.*?)\]/', $content, $matches);
        
        $shortcode_tags = $matches[0];
        $parsed_codes   = array();
        foreach ( $shortcode_tags as $tag ) {
            $r_tag                = preg_replace('/\[|\]+/', '', $tag);
            $parsed_codes[ $tag ] = shortcode_parse_atts($r_tag);
        }
        foreach ( $parsed_codes as $token => &$parsed_code ) {
            
            foreach ( $parsed_code as $kcode => $code ) {
                $parts = array();
                if (strpos($code, '.') !== false ) {
                    $parts = explode('.', $code);
                    $code  = array_shift($parts);
                }
            
                $find      = array_shift($parsed_code);        
                $content = str_replace('['.$find.']', $replaced_arr[$parts[1]], $content);
            }
        }
        $replace_keys = array_map(
            function ($k) {
                return '['.$k.']';
            }, array_keys($replaced_arr)
        );
        return str_replace($replace_keys, array_values($replaced_arr), $content);
	  }
	  return $content;
    }
    

    /**
     * 
     * Gets key value recursively from array.
     *
     * @param object $array array.
     * @param string $attr  attr.
     *
     * @return void
     */
    public static function getRecursiveVal( $array, $attr )
    {
        foreach ( $array as $part ) {
            if (is_array($part) ) {
                $attr = self::getRecursiveVal($part, $attr);
            } else {
                $attr = ( ! empty($attr[ $part ]) ) ? $attr[ $part ] : '';
            }
        }
        return $attr;
    }

    /**
     * 
     * This method is executed after order is placed.
     *
     * @param int    $order_id   Order id.
     * @param string $old_status Old Order status.
     * @param string $new_status New order status.
     *
     * @return void
     */
    public static function trigger_after_order_place( $order_id, $old_status, $new_status )
    {

        if (! $order_id ) {
            return;
        }

        $order          = wc_get_order($order_id);
        $admin_sms_data = array();
        $buyer_sms_data = array();

        $order_status_settings = chatondesk_get_option('order_status', 'chatondesk_general', array());
        $admin_phone_number    = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
        $admin_phone_number    = str_replace('postauthor', 'post_author', $admin_phone_number);

        if (count($order_status_settings) < 0 ) {
            return;
        }

        if (in_array($new_status, $order_status_settings, true) && ( 0 === $order->get_parent_id() ) ) {
            $default_buyer_sms = defined('SmsAlertMessages::DEFAULT_BUYER_SMS_' . str_replace(' ', '_', strtoupper($new_status))) ? constant('SmsAlertMessages::DEFAULT_BUYER_SMS_' . str_replace(' ', '_', strtoupper($new_status))) : SmsAlertMessages::showMessage('DEFAULT_BUYER_SMS_STATUS_CHANGED');

            $buyer_sms_body             = chatondesk_get_option('sms_body_' . $new_status, 'chatondesk_message', $default_buyer_sms);
			if ( version_compare( WC_VERSION, '7.1', '<' ) ) {
              $buyer_sms_data['number']   = get_post_meta( $order_id, '_billing_phone', true );
			} else {
              $buyer_sms_data['number']   = $order->get_meta('_billing_phone');
            }
            $buyer_sms_data['sms_body'] = $buyer_sms_body;

            $buyer_sms_data = apply_filters('cod_wc_order_sms_customer_before_send', $buyer_sms_data, $order_id);
            $buyer_response = Chatondesk::sendsms($buyer_sms_data);
            $response       = json_decode($buyer_response, true);

            if ('success' === $response['status'] ) {
                $order->add_order_note(__('SMS Send to buyer Successfully.', 'chat-on-desk'));
            } else {
                if (isset($response['description']) && is_array($response['description']) && array_key_exists('desc', $response['description']) ) {
                    $order->add_order_note($response['description']['desc']);
                } else {
                    $order->add_order_note($response['description']);
                }
            }
        }

        if (chatondesk_get_option('admin_notification_' . $new_status, 'chatondesk_general', 'on') === 'on' && ! empty($admin_phone_number) ) {
            // send sms to post author.
            $has_sub_order = metadata_exists('post', $order_id, 'has_sub_order');
            if (( strpos($admin_phone_number, 'post_author') !== false ) 
                && ( ( 0 !== $order->get_parent_id() ) || ( ( 0 === $order->get_parent_id() ) && empty($has_sub_order) ) ) 
            ) {
                $order_items = $order->get_items();
                $first_item  = current($order_items);
                $prod_id     = $first_item['product_id'];
                $product     = wc_get_product($prod_id);
                $author_no   = apply_filters('cod_post_author_no', $prod_id);

                if (0 === $order->get_parent_id() ) {
                    $admin_phone_number = str_replace('post_author', $author_no, $admin_phone_number);
                } else {
                    $admin_phone_number = $author_no;
                }
            }
            if (( strpos($admin_phone_number, 'store_manager') !== false ) && ( ( 0 === $order->get_parent_id() ) && empty($has_sub_order) ) ) {

                $author_no = apply_filters('cod_store_manager_no', $order);

                $admin_phone_number = str_replace('store_manager', $author_no, $admin_phone_number);
            }

            $default_template = SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_' . str_replace('-', '_', strtoupper($new_status)));

            $default_admin_sms = ( ( ! empty($default_template) ) ? $default_template : SmsAlertMessages::showMessage('DEFAULT_ADMIN_SMS_STATUS_CHANGED') );

            $admin_sms_body             = chatondesk_get_option('admin_sms_body_' . $new_status, 'chatondesk_message', $default_admin_sms);
            $admin_sms_data['number']   = $admin_phone_number;
            $admin_sms_data['sms_body'] = $admin_sms_body;

            $admin_sms_data = apply_filters('cod_wc_order_sms_admin_before_send', $admin_sms_data, $order_id);

            $admin_response = Chatondesk::sendsms($admin_sms_data);
            $response       = json_decode($admin_response, true);
            if ('success' === $response['status'] ) {
                $order->add_order_note(__('SMS Sent Successfully.', 'chat-on-desk'));
            } else {
                if (is_array($response['description']) && array_key_exists('desc', $response['description']) ) {
                    $order->add_order_note($response['description']['desc']);
                } else {
                    $order->add_order_note($response['description']);
                }
            }
        }
    }

    /**
     * 
     * Gets variables.
     *
     * @param string $status Order status.
     *
     * @return void
     */
    public static function getvariables( $status = null )
    {
        $variables = self::getOrderVariables();
        if (in_array($status, array( 'pending', 'failed' ), true) ) {
            $variables = array_merge(
                $variables,
                array(
                '[order_pay_url]' => 'Order Pay URL',
                )
            );
        }

        $variables = apply_filters('cod_wc_variables', $variables, $status);
        return $variables;
    }

    /**
     * 
     * Gets order details for post orver verification.
     *
     * @param object $order Order object.
     *
     * @return void
     */
    public function orderDetailsAfterPostOrderOtp( $order )
    {
        if ($this->guest_check_out_only && is_user_logged_in() ) {
            return;
        }
        $order_id = $order->get_id();
        if (! $order_id ) {
            return;
        }
        if (! get_post_meta($order_id, '_chatondesk_post_order_verification', true) && is_wc_endpoint_url('view-order') && ( 'processing' === $order->get_status() ) ) {
            $this->sendPostOrderOtp('', $order);
        }
    }

    /**
     * 
     * Gets order details for post orver verification.
     *
     * @param string $title title.
     * @param object $order Order object.
     *
     * @return void
     */
    public function sendPostOrderOtp( $title = null, $order = array() )
    {
        $order_id                = $order->get_id();
        $post_order_verification = chatondesk_get_option('post_order_verification', 'chatondesk_general');

        wp_localize_script(
            'wccheckout',
            'otp_for_selected_gateways',
            array(
            'is_thank_you' => true,
            'cod_post_verify'  => ( ( 'on' === chatondesk_get_option('post_order_verification', 'chatondesk_general') ) ? true : false ),

            )
        );

        $verified = false;
        if ('on' !== $post_order_verification ) {
            return;
        }
        if ($this->guest_check_out_only && is_user_logged_in() ) {
            return;
        }
        if (! $order_id ) {
            return;
        }

        if (! $this->isPaymentVerificationNeeded($order->get_payment_method()) ) {
            return;
        }
        
        if (! get_post_meta($order_id, '_chatondesk_post_order_verification', true) ) {
            $billing_phone       = $order->get_billing_phone();
            $otp_verify_btn_text = chatondesk_get_option('otp_verify_btn_text', 'chatondesk_general', '');

            echo "<div class='post_verification_section'><p>Your order has been placed but your mobile number is not verified yet. Please verify your mobile number.</p>";

            echo "<form class='woocommerce-form woocommerce-post-checkout-form' method='post'>";
            echo "<p class='woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide' style='display:none;'>";
            echo "<input type='hidden' name='billing_phone' class='cod-phone-field' value=" . esc_attr($billing_phone) . '>';
            echo "<input type='hidden' name='billing_email' >";
            echo "<input type='hidden' name='o_id' value='" . esc_attr($order_id) . "'>";
            echo '</p>';
            $this->showValidationButtonOrText(true);
            echo '</form>';
            echo do_shortcode('[cod_verify id="form1" phone_selector=".cod-phone-field" submit_selector= "#chatondesk_otp_token_submit" ]');
            echo '<script>';
            echo 'jQuery(".woocommerce-thankyou-order-received").hide();';
            echo '</script>';
            echo '</div>';
            echo '<style>.post_verification_section{padding: 1em 1.618em;border: 1px solid #f2f2f2;background: #fff;box-shadow: 10px 5px 5px -6px #ccc;}</style>';
        } else {
            return __('Thank you, Your mobile number has been verified successfully.', 'chat-on-desk');
        }
    }
}
new WooCommerceCheckOutForm();
?>
<?php
 /**
  * PHP version 5
  *
  * @category Handler
  * @package  ChatOnDesk
  * @author   Chat On Desk <support@cozyvision.com>
  * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
  * @link     https://www.chatondesk.com/
  * Sa_all_order_variable class
  */
class cod_all_order_variable
{

    /**
     * 
     * Constructor for class.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('woocommerce_after_register_post_type', array( $this, 'routeData' ), 10, 1);
    }

    /**
     * 
     * Routes data.
     *
     * @return void
     */
    public function routeData()
    {
        $order_id = isset($_REQUEST['order_id']) ? sanitize_text_field(wp_unslash($_REQUEST['order_id'])) : '';
        $option   = isset($_REQUEST['option']) ? sanitize_text_field(wp_unslash($_REQUEST['option'])) : '';

        if (! empty($option) && ( 'fetch-order-variable' === sanitize_text_field($option) ) && ! empty($order_id) ) {
            $tokens = array();

            global $woocommerce, $post;

            $order = new \WC_Order($order_id);

            $order_variables = get_post_custom($order_id);

            $variables = array();
            foreach ( $order_variables as $meta_key => &$value ) {
                $temp = maybe_unserialize($value[0]);

                if (is_array($temp) ) {
                    $variables[ $meta_key ] = $temp;
                } else {
                    $variables[ $meta_key ] = $value[0];
                }
            }
            $variables['order_status'] = $order->get_status();
            $variables['order_date']   = $order->get_date_created();
            $tokens['Order details']   = $variables;

            $item_variables = array();
            foreach ( $order->get_items(array( 'line_item', 'shipping' )) as $item_key => $item ) {
                $item_data = $item->get_data();
                $item_type = ( 'shipping' === $item->get_type() ) ? 'shippingitem' : 'orderitem';

                $tmp1 = array();
                foreach ( $item_data as $i_key => $i_val ) {
                    if ('meta_data' === $i_key ) {
                        $item_meta_data = $item->get_meta_data();
                        foreach ( $item_meta_data as $mkey => $meta ) {

                            $meta_value = $meta->get_data();
                            $temp       = maybe_unserialize($meta_value['value']);

                            if (is_array($temp) ) {
                                $tmp1[ "$item_type " . $meta_value['key'] ] = $temp;
                            } else {
                                $tmp1[ "$item_type " . str_replace(' ', '__', $meta_value['key']) ] = $meta_value['value'];
                            }
                        }
                    } else {
                        $tmp1[ "$item_type " . $i_key ] = $i_val;
                    }
                }
                $item_variables[] = $tmp1;
            }
            $item_variables = WooCommerceCheckOutForm::recursiveChangeKey($item_variables);

            $tokens['Order details']['Order Items'] = $item_variables;
            wp_send_json($tokens);
            exit();
        }
    }
}
new cod_all_order_variable();

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * SA_CodTOPrepaid class
 */
class SA_CodTOPrepaid
{
    /**
     * Construct function.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('cod_addTabs', array( $this, 'addTabs' ), 10);
        add_action('cod_tabContent', array( $this, 'tabContent' ), 1);
        add_filter('codDefaultSettings', array( $this, 'addDefaultSetting' ), 1);
        $notification_enabled = chatondesk_get_option('customer_notify', 'chatondesk_cod_to_prepaid', 'off');
        if ('on' === $notification_enabled) {
            add_action('cod_to_prepaid_cart_notification_sendsms_hook', array( $this, 'sendSms' ), 10);
            add_filter('woocommerce_valid_order_statuses_for_payment', array( $this,'filterWoocommerceValidOrderStatusesForPayment'), 10, 2);
            add_action('admin_notices', array($this, 'displayWpCronWarnings'), 10); 
        }
        
    }
    
    /**
     * FilterWoocommerceValidOrderStatusesForPayment
     *
     * @param array  $array    array.
     * @param string $instance instance.
     *
     * @return void
     */
    function filterWoocommerceValidOrderStatusesForPayment( $array, $instance )
    {
        $order_status     = str_replace('wc-', '', chatondesk_get_option('order_status', 'chatondesk_cod_to_prepaid', ""));
        
        if ('' === $order_status) {
            return $array;
        }
        return array_unique(array_merge($array, array($order_status)));
    }
    
    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public function addDefaultSetting( $defaults = array() )
    {
        
        $this->payment_methods           = maybe_unserialize(chatondesk_get_option('checkout_payment_plans', 'chatondesk_cod_to_prepaid'));
        $this->otp_for_selected_gateways = ( chatondesk_get_option('otp_for_selected_gateways', 'chatondesk_cod_to_prepaid') === 'on' ) ? true : false;         
        $defaults['chatondesk_cod_to_prepaid']['order_status']                   = 'Processing';
        $defaults['chatondesk_cod_to_prepaid']['notification_frequency']         = '10';
        $defaults['chatondesk_cod_to_prepaid']['customer_notify']                = 'off';
        $defaults['chatondesk_cod_to_prepaid_scheduler']['cron'][0]['frequency'] = '60';
        $defaults['chatondesk_cod_to_prepaid_scheduler']['cron'][0]['message']   = '';
        $defaults['chatondesk_cod_to_prepaid_scheduler']['cron'][1]['frequency'] = '120';
        $defaults['chatondesk_cod_to_prepaid_scheduler']['cron'][1]['message']   = '';

        return $defaults;
    }

    /**
     * Add tabs to smsalert settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public function addTabs( $tabs = array() )
    { 
        $smsalertcart_param = array(
        'checkTemplateFor' => 'Code_to_prepaid',
        'templates'        => $this->getSmsAlertCodTemplates(),
        );

        $tabs['woocommerce']['inner_nav']['code_to_prepaid']['title']       = 'COD To Prepaid';
        $tabs['woocommerce']['inner_nav']['code_to_prepaid']['tab_section'] = 'smsalertcarttemplates';
        $tabs['woocommerce']['inner_nav']['code_to_prepaid']['tabContent']  = $smsalertcart_param;
        $tabs['woocommerce']['inner_nav']['code_to_prepaid']['filePath']    = 'views/cod-to-prepaid-setting-template.php';
        $tabs['woocommerce']['inner_nav']['code_to_prepaid']['params']      = $smsalertcart_param;
        return $tabs;
    }

    /**
     * Get Chat On Desk cod templates.
     *
     * @return array
     */
    public function getSmsAlertCodTemplates()
    {
        $current_val      = chatondesk_get_option('customer_notify', 'chatondesk_cod_to_prepaid', 'off');
        
        $checkbox_name_id = 'chatondesk_cod_to_prepaid[customer_notify]';

        $scheduler_data = get_option('chatondesk_cod_to_prepaid_scheduler');
        
        $templates      = array();
        $count          = 0;
        if (empty($scheduler_data) ) {
            $scheduler_data['cron'][] = array(
            'frequency' => '60',
            'message'   => SmsAlertMessages::showMessage('DEFAULT_COD_PREPAID_CUSTOMER_MESSAGE'),  
            );
            $scheduler_data['cron'][] = array(
            'frequency' => '120',
            'message'   => SmsAlertMessages::showMessage('DEFAULT_COD_PREPAID_CUSTOMER_MESSAGE'),
            );            
        }

        foreach ( $scheduler_data['cron'] as $key => $data ) {
            $textarea_name_id = 'chatondesk_cod_to_prepaid_scheduler[cron][' . $count . '][message]';
            
            $selectNameId     = 'chatondesk_cod_to_prepaid_scheduler[cron][' . $count . '][frequency]';
            $text_body        = $data['message'];

            $templates[ $key ]['frequency']      = $data['frequency'];
            $templates[ $key ]['enabled']        = $current_val;
            $templates[ $key ]['title']          = 'Send message to customer when order is COD';
            $templates[ $key ]['checkboxNameId'] = $checkbox_name_id;
            $templates[ $key ]['text-body']      = $text_body;
            $templates[ $key ]['textareaNameId'] = $textarea_name_id;
            $templates[ $key ]['selectNameId']   = $selectNameId;
            
            $variables=WooCommerceCheckOutForm::getvariables();
            
            $variables['[order_pay_url]'] = "Order Pay Url";
            
            $templates[ $key ]['token']          = $variables;

            $count++;
        }
        return $templates;
    }
    
    
    /**
     * Send sms function.
     *
     * @return void
     */
    function sendSms()
    { 
        $order_statuses =    $order_status     = chatondesk_get_option('order_status', 'chatondesk_cod_to_prepaid', "processing");
        
        $payment_method     = chatondesk_get_option('checkout_payment_plans', 'chatondesk_cod_to_prepaid', "cod");
        $notification_enabled = chatondesk_get_option('customer_notify', 'chatondesk_cod_to_prepaid', 'off');
        if ('off' === $notification_enabled ) {
            return;
        }

        global $wpdb;
     
        $cron_frequency = CART_CRON_INTERVAL; // pick data from previous CART_CRON_INTERVAL min
       
        $scheduler_data = get_option('chatondesk_cod_to_prepaid_scheduler');
        
        foreach ( $scheduler_data['cron'] as $sdata ) {
            
            $datetime = current_time('mysql');
            $fromdate = date('Y-m-d H:i:s', strtotime('-' . $sdata['frequency'] . ' minutes', strtotime($datetime)));
            $todate = date('Y-m-d H:i:s', strtotime('-' . ( $sdata['frequency'] + $cron_frequency ) . ' minutes', strtotime($datetime)));    
  
            $rows_to_phone = $wpdb->get_results('select * from wp_posts as p inner join wp_postmeta as pm1 on (p.ID = pm1.post_id) where (pm1.meta_key = "_payment_method" and pm1.meta_value = "'.$payment_method.'") and (p.post_status = "'.(strtolower($order_statuses)).'") AND (p.post_date >= "'.$todate.'" and p.post_date <="'.$fromdate.'")', ARRAY_A);

            if ($rows_to_phone ) { // If we have new rows in the database
            
                $customer_message = $sdata['message'];
                $frequency_time   = $sdata['frequency'];
                   
                if ('' !== $customer_message && 0 !== $frequency_time ) {
                    $obj = array();
                    foreach ( $rows_to_phone as $key=>$data ) {    
                    
                        $order_id = $data['ID'];
                        $order      =get_post_custom($order_id); 
                        
                        $buyerNumber   =  $order[ '_billing_phone'][0];
                        
                        
                        $sms_data['sms_body'] = $customer_message;
                        
                        if (!empty($buyerNumber) && !empty($sms_data['sms_body'])) {
                            
                               $buyerMessage  =  WooCommerceCheckOutForm::pharseSmsBody($sms_data, $order_id);
                                
                                
                               do_action('cod_send_sms', $buyerNumber, $buyerMessage['sms_body']);
                        }
                        
                    }
                    
                    
                }
            }
        }  
    }
    
    /**
     * Display wp cron warnings function.
     *
     * @return void
     */
    function displayWpCronWarnings()
    {
        global $pagenow;

        // Checking if we are on open plugin page
        if ('admin.php' === $pagenow && 'chat-on-desk' === sanitize_text_field($_GET['page']) ) {

            // Checking if WP Cron hooks are scheduled
            $missing_hooks = array();
            // $user_settings_notification_frequency = chatondesk_get_option('customer_notify','chatondesk_abandoned_cart');

            if (wp_next_scheduled('cod_to_prepaid_cart_notification_sendsms_hook') === false ) { // If we havent scheduled msg notifications and notifications have not been disabled
                $missing_hooks[] = 'cod_to_prepaid_cart_notification_sendsms_hook';
            }
            if (! empty($missing_hooks) ) { // If we have hooks that are not scheduled
                $hooks   = '';
                $current = 1;
                $total   = count($missing_hooks);
                foreach ( $missing_hooks as $missing_hook ) {
                    $hooks .= $missing_hook;
                    if ($current !== $total ) {
                        $hooks .= ', ';
                    }
                    $current++;
                }
                ?>
                <div class="warning notice updated">
                <?php
                echo sprintf(
                /* translators: %s - Cron event name */
                    _n('It seems that WP Cron event <strong>%s</strong> required for automation is not scheduled.', 'It seems that WP Cron events <strong>%s</strong> required for automation are not scheduled.', $total, 'chat-on-desk'),
                    $hooks
                );
                ?>
                <?php
                echo sprintf(
                /* translators: %1$s - Plugin name, %2$s - Link */
                    __('Please try disabling and enabling %1$s plugin. If this notice does not go away after that, please <a href="https://wordpress.org/support/plugin/chat-on-desk/" target="_blank">get in touch with us</a>.', 'chat-on-desk'),
                    CHATONDESK_PLUGIN_NAME
                );
                ?>
                    </p>
                </div>
                <?php
            }

            // Checking if WP Cron is enabled
            if (defined('DISABLE_WP_CRON') ) {
                if (DISABLE_WP_CRON == true ) {
                    ?>
                    <div class="warning notice updated">
                        <p class="left-part"><?php esc_html_e('WP Cron has been disabled. Several WordPress core features, such as checking for updates or sending notifications utilize this function. Please enable it or contact your system administrator to help you with this.', 'chat-on-desk'); ?></p>
                    </div>
                    <?php
                }
            }
        }
    }
}
new SA_CodTOPrepaid();
if (! class_exists('WP_List_Table') ) {
    include_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/ 
 * Class All_Order_List 
 */
class All_Order_List extends \WP_List_Table
{

    /**
     * 
     * Class constructor
     *
     * @return void     
     */
    public function __construct()
    {
        parent::__construct(
            array(
            'singular' => 'allordervaribale',
            'plural'   => 'allordervariables',
            )
        );
    }

    /**
     * 
     * Get all subscriber info 
     *
     * @return void
     */
    public static function getAllOrder()
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}posts  WHERE post_type = 'shop_order' && post_status != 'auto-draft' ORDER BY post_date desc LIMIT 5";

        $result = $wpdb->get_results($sql, 'ARRAY_A');

        return $result;
    }

    /**
     * No items.
     *
     * @return void
     */
    public function no_items()
    {
        esc_html_e('No Order.', 'chat-on-desk');
    }

    /**
     * Column post checkbox.
     *
     * @param array $item        Item.
     * @param array $column_name Column Name.
     *
     * @return void
     */
    public function column_default( $item, $column_name )
    {
        return $item[ $column_name ];
    }

    /**
     * Column post checkbox.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_cb( $item )
    {
        return sprintf(
            '<input type="checkbox" name="ID[]" value="%s" />',
            $item['ID']
        );
    }

    /**
     * Column post status.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_post_status( $item )
    {
        $post_status = sprintf('<button class="button-primary"/>%s</a>', str_replace('wc-', '', $item['post_status']));
        return $post_status;
    }

    /**
     * Column post date.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_post_date( $item )
    {
        $date = date('d-m-Y', strtotime($item['post_date']));
        return $date;
    }

    /**
     * Get columns.
     *
     * @return void
     */
    public function get_columns()
    {
        $columns = array(
        'ID'          => __('Order'),
        'post_date'   => __('Date'),
        'post_status' => __('Status'),
        );

        return $columns;
    }

    /**
     * Prepare items.
     *
     * @return void
     */
    public function prepareItems()
    {

        $columns               = $this->get_columns();
        $this->items           = self::getAllOrder();
        $this->_column_headers = array( $columns );

        return $this->items;
    }
}

/**
 * Adds a sub menu page for all order variables.
 *
 * @return void
 */
function allOrderVariableAdminMenu()
{
    add_submenu_page(null, 'All Order Variable', 'All Order Variable', 'manage_options', 'all-order-variable', 'ChatOnDesk\all_cod_order_variable_page_handler');
}

add_action('admin_menu', 'ChatOnDesk\allOrderVariableAdminMenu');

/**
 * All order variables page handler.
 *
 * @return void
 */
function all_cod_order_variable_page_handler()
{
    global $wpdb;

    $table_data = new All_Order_List();
    $data       = $table_data->prepareItems();
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2 class="title">Order List</h2>
    <form id="order-table" method="GET">
        <input type="hidden" name="page" value="<?php echo empty($_REQUEST['page']) ? '' : esc_attr($_REQUEST['page']); ?>"/>
    <?php $table_data->display(); ?>
    </form>
    <div id="cod_order_variable" class="cod_variables" style="display:none">
        <h3 class="h3-background">Select your variable <span id="order_id" class="alignright"><?php echo esc_attr($order_id); ?></span></h3>
        <ul id="order_list"></ul>
    </div>
</div>
<script>
jQuery(document).ready(function(){
    jQuery("tbody tr").addClass("order_click");

    jQuery(".order_click").click(function(){
        var id = jQuery(this).find(".ID").text().replace(/\D/g,'');
        jQuery("#order-table, .title").hide();
        jQuery("#cod_order_variable").show();
        jQuery("#order_id").html('Order Id: '+id);

        if (id != ''){
            jQuery.ajax({
                url         : "<?php echo esc_url(admin_url()); ?>?option=fetch-order-variable",
                data        : {order_id:id},
                dataType    : 'json',
                success: function(data)
                {
                    var arr1    = data;
                    var content1 = parseVariables(arr1);

                    jQuery('ul#order_list').html(content1);

                    jQuery("ul").prev("a").addClass("nested");

                    jQuery('ul#order_list, ul#order_item_list').css('textTransform', 'capitalize');

                    jQuery(".nested").parent("li").css({"list-style":"none"});

                    jQuery("ul#order_list li ul:first").show();
                    jQuery("ul#order_list").show();
                    jQuery("ul#order_list li a:first").addClass('nested-close');

                    toggleSubMenu();
                    addToken();
                },
                error:function (e,o){
                }
            });
        }

    });

     /**
     * ParseVariables.
     *
     * @param int $data data.
     * @param int $prefix       prefix.
     *
     * @return void
     */
    function parseVariables(data,prefix='')
    {
        text = '';
        jQuery.each(data,function(i,item){


            if (typeof item === 'object')
            {
                var nested_key = i.toString().replace(/_/g," ").replace(/orderitem/g,"");
                var key = i.toString().replace(/^_/i,"");



                if (nested_key != ''){
                    text+='<li><a href="#" value="['+key+']">'+nested_key+'</a><ul style="display:none">';
                    text+= parseVariables(item,prefix);
                    text+="</li></ul>";
                }
            } else {

                var j         = i.toString();
                var key     = i.toString().replace(/_/g," ").replace(/orderitem/g,"");
                var title     = item;
                var val     = j.toString().replace(/^_/i,"");


                text+='<li><a href="#" value="['+val+']" title="'+title+'">'+key+'</a></li>';
            }
        });
        return text;
    }
/**
     * ToggleSubMenu.
     *
     * @return void
     */
    function toggleSubMenu(){
        jQuery("a.nested").click(function(){
            jQuery(this).parent('li').find('ul:first').toggle();
            if (jQuery(this).hasClass("nested-close")){
                jQuery(this).removeClass("nested-close");
            } else {
                jQuery(this).addClass("nested-close");
            }
            return false;
        });
    }
/**
     * AddToken.
     *
     * @return void
     */
    function addToken(){
        jQuery('.cod_variables a').click( function() {
            if (jQuery(this).hasClass("nested")){
                return false;
            }
            var token = jQuery(this).attr('value');
            var datas = [];
            datas['token'] = token;
            datas['type'] = 'chatondesk_token';
            window.parent.postMessage(datas, '*');
        });
    }
    return false;
});
</script>
<?php } 

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/ 
 * Class All_Order_List 
 */
class All_Template_List extends \WP_List_Table
{

    /**
     * 
     * Class constructor
     *
     * @return void     
     */
    public function __construct()
    {
        parent::__construct(
            array(
            'singular' => 'alltemplatelist',
            'plural'   => 'alltemplatelists',
            )
        );
    }

    /**
     * 
     * Get all subscriber info 
     *
     * @return void
     */
    public static function getAllTemplates()
    {
        $templates = Chatondesk::getTemplates();
		return (!empty($templates['status']) && $templates['status'] == 'success')?$templates['description']:array();
    }

    /**
     * No items.
     *
     * @return void
     */
    public function no_items()
    {
        esc_html_e('No template.', 'chat-on-desk');
    }

    /**
     * Column post checkbox.
     *
     * @param array $item        Item.
     * @param array $column_name Column Name.
     *
     * @return void
     */
    public function column_default( $item, $column_name )
    {
        return $item['Structuredtemplate'][ $column_name ];
    }
	
	   /**
     * Column post checkbox.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_cb( $item )
    {
        return sprintf(
            '<input type="checkbox" name="id[]" value="%s" />',
            $item['id']
        );
    }

    /**
     * Column post checkbox.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_browse( $item )
    {
        return '<a href="#" class="button-primary" onclick="useTemplate(this); return false;">' . esc_html__("Use Template", "chat-on-desk") . '</a>';
    }

    /**
     * Column post date.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_name( $item )
    {
        return $item['Structuredtemplate']['name'];
    }
	
	/**
     * Column post date.
     *
     * @param array $item Item.
     *
     * @return void
     */
    public function column_template( $item )
    {
        return json_encode($item);
    }

    /**
     * Get columns.
     *
     * @return void
     */
    public function get_columns()
    {
        $columns = array(
        'id'   => __('Template Id', 'chat-on-desk'),
        'name'   => __('Template Name', 'chat-on-desk'),
        'template'   => __('Template', 'chat-on-desk'),
        'browse'   => __('Action', 'chat-on-desk'),
        );

        return $columns;
    }

    /**
     * Prepare items.
     *
     * @return void
     */
    public function prepareItems()
    {

        $columns               = $this->get_columns();
        $this->items           = self::getAllTemplates();
        $this->_column_headers = array( $columns, array('template','id') );

        return $this->items;
    }
}

/**
 * Adds a sub menu page for all order variables.
 *
 * @return void
 */
function allCodTemplateListAdminMenu()
{
    add_submenu_page(null, 'Preview Template', 'Preview Template', 'manage_options', 'preview-cod-template', 'ChatOnDesk\preview_cod_template');
    add_submenu_page(null, 'All Template List', 'All Template List', 'manage_options', 'add-cod-template', 'ChatOnDesk\add_cod_template_list_page_handler');
}

add_action('admin_menu', 'ChatOnDesk\allCodTemplateListAdminMenu');

/**
 * Preview template.
 *
 * @return void
 */
function preview_cod_template()
{
	$url = add_query_arg(
		array(
			'action'    => 'foo_modal_box',
			'TB_iframe' => 'true',
			'width'     => '800',
			'height'    => '500'
		),
		admin_url('admin.php?page=add-cod-template')
	);
	?>
	<div class="wrap cod_template_preview" style="display:none;">
    <div id="cod_template_preview">
        <h3 class="h3-background"><span id="template_prev_id"><?php esc_html_e('Template Preview', 'chat-on-desk'); ?></span><a href="<?php echo esc_url($url); ?>" class="alignright cod-white newtemp"><?php esc_html_e('New Template', 'chat-on-desk'); ?></a></h3>
		<form class="cod_map_variable">
        <table class="form-table" style="table-layout: fixed;">
			<tbody id="template_preview">
			<tr class="msg-preview preview-message">
			<td colspan="3">
			</td>
			</tr>
			<tr class="token-prev">
            <td class="td-heading">
			</td>
			<td class="td-dropdown">
			</td>
			<td class="td-input">
			</td>
			</tr>
			<tbody>
		</table>
		</form>
    </div>
</div>
<script>
   var template = jQuery('.cod-browse-btn.active',parent.document).parent().find('.cod_template_text').val();
   template = isJson(template)?JSON.parse(template):'';
   var str_temp = isJson(template)?JSON.parse(template.Structuredtemplate.template):'';
   if(str_temp != '' && template.Structuredtemplate.template.match(/##[\w_]+##/g) == null)
   {
		jQuery('.newtemp')[0].click();
   }
   else{
	jQuery('.cod_template_preview').show();   
    var msg_text = '';
	if(str_temp == '')
	{
		msg_text = "<?php esc_html_e('No template is selected', 'chat-on-desk'); ?>";
	}
	else{
		if(typeof str_temp.header != 'undefined')
		{
			msg_text+=str_temp.header.message;
		}
		if(typeof str_temp.body != 'undefined')
		{
			msg_text+=(msg_text!='')?'</br>'+str_temp.body.message:str_temp.body.message;
		}
		if(typeof str_temp.footer != 'undefined')
		{
			msg_text+='</br>'+str_temp.footer.message;
		}
	}
	if(typeof template.data != 'undefined')
	{
		jQuery.each(template.data,function(key,val){
			msg_text = msg_text.replace('##'+key+'##',val);	
		});
	}
	jQuery('.msg-preview td').html(msg_text);
	var params = [];
	if(isJson(template))
	{
		jQuery.each(template.Structuredtemplate.template.match(/##[\w_]+##/g),function(key,param){
		  if(jQuery.inArray(param, params) === -1)
		  {
			  var last_ele	= jQuery(".token-prev").last();
			  last_ele.find('.td-heading').text(param);
			  last_ele.find('.td-dropdown').html(jQuery('.cod-browse-btn.active',parent.document).parent().find('.cod-token').attr('name',param.replace(/##/g,'')).clone().removeClass('hide'));
			  last_ele.after(last_ele.clone());
			  jQuery.each(template.data,function(key,value){
				if(param.replace(/##/g,'') == key)
				{
				   last_ele.find('.td-dropdown .cod-token').val(value);
				   if(last_ele.find('.td-dropdown .cod-token').val() == null)
				   {
					last_ele.find(".cod-token option").filter(function () {return ($(this).text() == 'Custom'); }).val(value).attr('selected','selected');
					last_ele.find('.td-input').html("<input name='cod_custom_text' type='text' class='cod_custom_text' placeholder='Enter Value' value='"+value+"'>");
					initialiseTextbox();				
				   }
				}
			  });
		  }
		   params.push(param);
		});
	}
   }
	if(params.length > 0)
	{
		jQuery(".token-prev").last().remove();
		jQuery(".token-prev").last().after('<tr><td><a href="#" class="button-primary" onclick="mapVariable(); return false;"><?php esc_html_e("Submit", "chat-on-desk"); ?></a></td></tr>');
		initialiseDropdown();
	}
	
	function initialiseDropdown()
	{
		jQuery(".td-dropdown .cod-token").change(function() {
		var selected = jQuery(this).find("option:selected").text();
		if(selected == 'Custom')
		{
			jQuery(this).parents('.token-prev').find('.td-input').html('<input name="cod_custom_text" type="text" class="cod_custom_text" placeholder="<?php esc_html_e('Enter Value', 'chat-on-desk'); ?>">');
			initialiseTextbox();
		}
		else{
			jQuery(this).parents('.token-prev').find('.td-input').html('');
		}
		});
	}
	function initialiseTextbox()
	{
		jQuery(".td-input .cod_custom_text").keyup(function() {
		var text_data = jQuery(this).val();
		jQuery(this).parents('.token-prev').find(".cod-token option").filter(function () {return ($(this).text() == 'Custom'); }).attr('value',text_data);
		});
	}
	
	/**
	 * mapVariable.
	 *
	 * @return void
	 */
	function mapVariable(){
		var token_array = {};
		jQuery.map(jQuery('.cod_map_variable').serializeArray(), function(n, i){
			if(n['name'] != 'cod_custom_text')
			{
			 token_array[n['name']] = n['value'];
			}
		});
		var template = jQuery('.cod-browse-btn.active',parent.document).parent().find('.cod_template_text').val();
        template = (template!='')?JSON.parse(template):'';
		template['data'] = token_array;
		var datas = [];
		datas['template'] = JSON.stringify(template);
		datas['type'] = 'chatondesk_template_data';
		window.parent.postMessage(datas, '*');
	}
	
	function isJson(str) {
		if(typeof str == 'object')
		{
			return true;
		}
		try {
			JSON.parse(str);
		} catch (e) {
			return false;
		}
		return true;
   }
</script>
<?php
}

/**
 * All order variables page handler.
 *
 * @return void
 */
function add_cod_template_list_page_handler()
{
    global $wpdb;
    $table_data = new All_Template_List();
    $data       = $table_data->prepareItems();
    ?>
<div class="wrap">
    <div class="icon32 icon32-posts-post" id="icon-edit"><br></div>
    <h2 class="title">Template List</h2>
    <form id="template-table" method="GET">
        <input type="hidden" name="page" value="<?php echo empty($_REQUEST['page']) ? '' : esc_attr($_REQUEST['page']); ?>"/>
    <?php $table_data->display(); ?>
    </form>
    <div id="cod_template_list" class="cod_templates" style="display:none">
        <h3 class="h3-background">Map template variable <span id="template_id" class="alignright"></span></h3>
		<form class="cod_map_variable">
        <table class="form-table" style="table-layout: fixed;">
			<tbody id="template_list">
			<tr class="token-row">
            <td class="td-heading">
			</td>
			<td class="td-dropdown">
			</td>
			<td class="td-input">
			</td>
			</tr>
			<tbody>
		</table>
		</form>
    </div>
</div>
<script>
    function useTemplate(obj)
	{
		var temp_name = jQuery(obj).parents('tr').find(".name").text();
        var template = jQuery(obj).parents('tr').find(".template").text();
        jQuery("#template-table, .title").hide();
        jQuery("#cod_template_list").show();
        jQuery("#template_id").html('Template Name: '+temp_name);

        var temp = JSON.parse(template).Structuredtemplate.template;
		jQuery("#template_id").attr('temp-data',template);
		var params = [];
		jQuery.each(temp.match(/##[\w_]+##/g),function(key,param){
		  if(jQuery.inArray(param, params) === -1)
		  {
			  var last_ele	= jQuery(".token-row").last();
			  last_ele.find('.td-heading').text(param);
			  last_ele.find('.td-dropdown').html(jQuery('.cod-browse-btn.active',parent.document).parent().find('.cod-token').attr('name',param.replace(/##/g,'')).clone().removeClass('hide'));
			  last_ele.after(last_ele.clone());
		  }
		   params.push(param);
		});
		if(params.length > 0)
		{
			jQuery(".token-row").last().remove();
			jQuery(".token-row").last().after('<tr><td><a href="#" class="button-primary" onclick="mapVariable(); return false;">Submit</a></td></tr>');
			initialiseDropdown();
		}
		else{
			var datas = [];
			datas['template'] = template;
			datas['type'] = 'chatondesk_template_data';
			window.parent.postMessage(datas, '*');
		}
    }
	function initialiseDropdown()
	{
		jQuery(".td-dropdown .cod-token").change(function() {
		var selected = jQuery(this).find("option:selected").text();
		if(selected == 'Custom')
		{
			jQuery(this).parents('.token-row').find('.td-input').html('<input name="cod_custom_text" type="text" class="cod_custom_text" placeholder="Enter Value">');
			initialiseTextbox();
		}
		else{
			jQuery(this).parents('.token-row').find('.td-input').html('');
		}
		});
	}
	function initialiseTextbox()
	{
		jQuery(".td-input .cod_custom_text").keyup(function() {
		var text_data = jQuery(this).val();
		jQuery(this).parents('.token-row').find(".cod-token option").filter(function () {return ($(this).text() == 'Custom'); }).attr('value',text_data);
		});
	}
	
/**
 * mapVariable.
 *
 * @return void
 */
function mapVariable(){
	var token_array = {};
	jQuery.map(jQuery('.cod_map_variable').serializeArray(), function(n, i){
		if(n['name'] != 'cod_custom_text')
		{
		 token_array[n['name']] = n['value'];
		}
	});
	var template  = JSON.parse(jQuery("#template_id").attr('temp-data'));
	template['data'] = token_array;
	var datas = [];
	datas['template'] = JSON.stringify(template);
	datas['type'] = 'chatondesk_template_data';
	window.parent.postMessage(datas, '*');
}
</script>
<?php } ?>

