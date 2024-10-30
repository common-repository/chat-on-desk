<?php
/**
 * Signup with otp helper.
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
 * WCSignupWithOTp class
 */
class WCSignupWithOTp
{

    /**
     * Construct function
     *
     * @return void
     */
    public function __construct()
    {
		$user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        if (! $islogged ) {
            return;
        }
        $this->plugin_name = CHATONDESK_PLUGIN_NAME_SLUG;

        $signup_with_mobile     = chatondesk_get_option('signup_with_mobile', 'chatondesk_general', 'off');
        $enable_otp_user_update = get_option('chatondesk_otp_user_update', 'on');

        add_action('wp_enqueue_scripts', array( $this, 'enqueueScripts' ), 100);
        add_action('woocommerce_register_form_end', array( $this, 'smsalertDisplaySignupBtn' ), 100);
        add_action('woocommerce_login_form_end', array( $this, 'smsalertDisplayLoginBackBtn' ), 100);
        if ('on' === $signup_with_mobile ) {
            add_shortcode('cod-modal', array( $this, 'chatondeskModalLogin' ), 100);
        }

        if ('on' === $enable_otp_user_update ) {
            add_action('woocommerce_after_edit_address_form_billing', array( $this, 'updateBillingPhone' ), 100);
        }
        add_action('start_process_signwithmob', array( $this, 'processRegistration' ), 10, 1);
        $this->routeData();
        add_action('chatondesk_user_created', array( $this, 'smsalertWcUpdateNewDetails' ), 100, 2);
        add_filter('codDefaultSettings', array( $this, 'add_default_setting' ), 1);

    }

    /**
     * Handle post data via ajax submit
     *
     * @return void
     */
    public function routeData()
    {
        if (! array_key_exists('action', $_REQUEST) ) {
            return;
        }
        /*
        switch ( trim( sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) ) {
        case 'codsignwthmob':
        //$this->processRegistration();
        //do_action('start_process_signwithmob',$_POST);
        do_action('test_smsalert');
        break;
        } */
    }

    /**
     * Smsalert display login back button function.
     *
     * @return array
     */
    public function smsalertDisplayLoginBackBtn()
    {
        wp_enqueue_script($this->plugin_name . 'signup_with_otp');
        $users_can_register = get_option('woocommerce_enable_myaccount_registration', 'yes');
        if ('yes' === $users_can_register ) {
            ?>
            <div class="signdesc">Don't have an account? <a href="javascript:void(0)" class="signupbutton">Signup</a></div>
            <?php
        }
    }

    /**
     * Display signup button function.
     *
     * @return array
     */
    public function smsalertDisplaySignupBtn()
    {
        wp_enqueue_script($this->plugin_name . 'signup_with_otp');
        ?>
        <div class="backtoLoginContainer"><a href="javascript:void(0)" class="backtoLogin">Back to login</a></div>
        <?php
    }

   
    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public function add_default_setting( $defaults = array() )
    {
        $defaults['chatondesk_otp_user_update'] = 'off';
        $defaults['chatondesk_defaultuserrole'] = 'customer';
        return $defaults;
    }

    /**
     * Enqueue scripts function.
     *
     * @return void
     */
    public function enqueueScripts()
    {
        $register_otp = chatondesk_get_option('buyer_signup_otp', 'chatondesk_general', 'on');
        $enable_otp   = get_option('chatondesk_otp_user_update', 'on');

        //if ( 'on' === $register_otp || 'on' === $enable_otp ) {
        $js_data = array(
        'signupwithotp'     => esc_html__('SIGN UP WITH OTP', 'chat-on-desk'),
        'update_otp_enable' => $enable_otp,
        );
        wp_register_script($this->plugin_name . 'signup_with_otp', COD_MOV_URL . 'js/signup.js', array( 'jquery' ), \ChatOnDesk\SmsAlertConstants::SA_VERSION, false);
        wp_localize_script($this->plugin_name . 'signup_with_otp', 'chatondesk_mdet', $js_data);
        //}
    }

    /**
     * Update new details function.
     *
     * @param int $user_id user_id.
     * @param int $data    data.
     *
     * @return void
     */
    public function smsalertWcUpdateNewDetails( $user_id, $data )
    {
        $user = get_user_by('ID', $user_id);
        if (! $user ) {
            return false;
        }
        $billing_first_name = get_user_meta($user->ID, 'billing_first_name', true);
        if (! empty($billing_first_name) ) {
            return false;
        }
        if (! empty($user->first_name) ) {
            update_user_meta($user_id, 'billing_first_name', $user->first_name);
        }

        if (! empty($user->user_email) ) {
            update_user_meta($user_id, 'billing_email', $user->user_email);
        }

        if (empty($user->billing_phone) ) {
            update_user_meta($user_id, 'billing_phone', $data['billing_phone']);
            do_action('cod_send_sms', $data['billing_phone'], 'terer');
        }

    }
    /**
     * Update billing phone function.
     *
     * @return void
     */
    public function updateBillingPhone()
    {
        wp_enqueue_script($this->plugin_name . 'signup_with_otp');
        $enable_otp = get_option('chatondesk_otp_user_update', 'on');
        if ('on' === $enable_otp ) {
            echo '<div style="clear:both">';
            echo '<input type="hidden" id="old_billing_phone" value="' . esc_attr(\ChatOnDesk\SmsAlertUtility::formatNumberForCountryCode(get_user_meta(get_current_user_id(), 'billing_phone', true))) . '">';

            echo do_shortcode('[cod_verify id="form1" phone_selector="#billing_phone" submit_selector="save_address"]');
            echo '</div>';
            echo "<script>setTimeout(function(){ jQuery('[name=billing_phone]').trigger('change'); }, 1000);</script>";
        }
    }

    /**
     * Smsalert forms function.
     *
     * @param $values values
     *
     * @return void
     */
    public function smsalertForms( $values = '' )
    {
        $users_can_register = get_option('woocommerce_enable_myaccount_registration', 'yes');
        $default            = '';
        $showonly           = '';

        if (isset($values['default']) && '' !== $values['default'] ) {
            $default = $values['default'];
        }
        if (isset($values['showonly']) && '' !== $values['showonly'] ) {
            $showonly = strtolower($values['showonly']);
        }

        echo do_shortcode('[woocommerce_my_account]');

        if (( 'register' === $default && empty($showonly) ) || 'register' === $showonly ) {
            echo '<style>.signdesc,.u-column1{display:none;}.u-column2{display:block;}</style>';
        } elseif (( 'login' === $default && empty($showonly) ) || 'login' === $showonly ) {
            echo '<style>.u-column1{display:block;}.u-column2,.signdesc{display:none;}</style>';
        } elseif ('login,register' === $showonly || 'register,login' === $showonly ) {
            if ('login' === $default ) {
                echo '<style>.signdesc,.u-column1{display:block;}.u-column2{display:none;}</style>';
            } else {
                echo '<style>.signdesc,.u-column1{display:none;}.backtoLoginContainer,.u-column2{display:block;}</style>';
            }
        }

    }

    /**
     * Modal login function
     *
     * @param array $attrs attrs.
     *
     * @return string
     */
    public function chatondeskModalLogin( $attrs = array() )
    {
        wp_enqueue_script($this->plugin_name . 'signup_with_otp');
        $default       = isset($attrs['default']) ? $attrs['default'] : 'register';
        $showonly      = isset($attrs['showonly']) ? $attrs['showonly'] : '';
        $display_style = isset($attrs['display']) ? $attrs['display'] : 'center';
        $modal_id      = isset($attrs['modal_id']) ? $attrs['modal_id'] : uniqid();

        $users_can_register = get_option('woocommerce_enable_myaccount_registration', 'yes');
        if ('yes' !== $users_can_register && ( strpos($default, 'register') !== false || strpos($showonly, 'register') !== false ) ) {
            return; // if register disabled on myaccount page then return.
        }

        $element = 'attr-disclick="1" class="chatondesk-login-modal"';

        if (! is_user_logged_in() ) {
            $text = ( '' !== $default ) ? ucfirst($default) : 'Register';
            $url  = '?default=' . esc_attr($default) . '&showonly=' . esc_attr($showonly) . '&modal_id=' . esc_attr($modal_id);

            if ('' !== $showonly ) {
                $text = $showonly;
            }
            $text = isset($attrs['label']) ? $attrs['label'] : $text;
            ?>
            
            <div  class="modal chatondesk-modal chatondeskModal" style="display:none;" id="<?php echo esc_attr($modal_id); ?>">
                <div class="modal-content">
                    <div class="close"><span></span></div>
                    <div class="modal-body">
                        <div class="chatondesk_validate_field">
                            <div id="slide_form">
            <?php echo $this->smsalertForms(); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php
            return '<span href="' . esc_url($url) . '" ' . $element . ' data-display="' . esc_attr($display_style) . '" data-modal-id="' . esc_attr($modal_id) . '"><a href="javascript:void(0)">' . esc_attr($text) . '</a></span>';
        } else {
            return '';
        }
    }

    /**
     * Function wcUserCreated
     *
     * @param int $user_id user id.
     * @param int $data    data.
     *
     * @return string
     */
    public static function wcUserCreated( $user_id, $data )
    {
        $billing_phone = ( ! empty($data['billing_phone']) ) ? sanitize_text_field(wp_unslash($data['billing_phone'])) : null;
        // $billing_phone = apply_filters("cod_get_user_phone_no",$user_id,$billing_phone);
        $billing_phone = Chatondesk::checkPhoneNos($billing_phone);

        update_user_meta($user_id, 'billing_phone', $billing_phone);
        do_action('chatondesk_after_update_new_user_phone', $user_id, $billing_phone);

        self::smsalertAfterUserRegister($user_id, $billing_phone);
    }

    /**
     * This function gets role display name from system name.
     *
     * @param bool $system_name System name of the role.
     *
     * @return string
     */
    public static function get_user_roles( $system_name = null )
    {
        global $wp_roles;
        $roles = $wp_roles->roles;

        if (! empty($system_name) && array_key_exists($system_name, $roles) ) {
            return $roles[ $system_name ]['name'];
        } else {
            return $roles;
        }
    }

    /**
     * This function is executed after a user has been registered.
     *
     * @param int    $user_id       Userid of the user.
     * @param string $billing_phone Phone number of the user.
     *
     * @return string
     */
    public static function smsalertAfterUserRegister( $user_id, $billing_phone )
    {
        $user                = get_userdata($user_id);
        $role                = ( ! empty($user->roles[0]) ) ? $user->roles[0] : '';
        $role_display_name   = ( ! empty($role) ) ? self::get_user_roles($role) : '';
        $chatondesk_reg_notify = chatondesk_get_option('wc_user_roles_' . $role, 'chatondesk_signup_general', 'off');
        $sms_body_new_user   = chatondesk_get_option('signup_sms_body_' . $role, 'chatondesk_signup_message', SmsAlertMessages::showMessage('DEFAULT_NEW_USER_REGISTER'));

        $chatondesk_reg_admin_notify = chatondesk_get_option('admin_registration_msg', 'chatondesk_general', 'off');
        $sms_admin_body_new_user   = chatondesk_get_option('sms_body_registration_admin_msg', 'chatondesk_message', SmsAlertMessages::showMessage('DEFAULT_ADMIN_NEW_USER_REGISTER'));
        $admin_phone_number        = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');

        $store_name = trim(get_bloginfo());

        if ('on' === $chatondesk_reg_notify && ! empty($billing_phone) ) {
            $search = array(
            '[username]',
            '[email]',
            '[billing_phone]',
            );

            $replace           = array(
            $user->user_login,
            $user->user_email,
            $billing_phone,
            );
            $sms_body_new_user = str_replace($search, $replace, $sms_body_new_user);
            do_action('cod_send_sms', $billing_phone, $sms_body_new_user);
        }

        if ('on' === $chatondesk_reg_admin_notify && ! empty($admin_phone_number) ) {
            $search = array(
            '[username]',
            '[store_name]',
            '[email]',
            '[billing_phone]',
            '[role]',
            );

            $replace = array(
            $user->user_login,
            $store_name,
            $user->user_email,
            $billing_phone,
            $role_display_name,
            );

            $sms_admin_body_new_user = str_replace($search, $replace, $sms_admin_body_new_user);
            $nos                     = explode(',', $admin_phone_number);
            $admin_phone_number      = array_diff($nos, array( 'postauthor', 'post_author' ));
            $admin_phone_number      = implode(',', $admin_phone_number);
            do_action('cod_send_sms', $admin_phone_number, $sms_admin_body_new_user);
        }
    }

    /**
     * Process registration function.
     *
     * @param $data posted data.
     *
     * @return string
     */
    public function processRegistration( $data = array() )
    {
        $tname = '';
        $phone = '';
        $_POST = $data;
        if (isset($_POST['chatondesk_name']) ) {

            // $chatondesk_reg_details = self::chatondesk_get_reg_fields();
            // $mobileaccp           = $chatondesk_reg_details['chatondesk_reg_mobilenumber'];

            // $validation_error = new WP_Error();

            /*
            if (isset($_POST['billing_first_name']) ) {
            $name = sanitize_text_field(wp_unslash($_POST['billing_first_name']));
            } else {
            $name = '';
            } */

            $mail = isset($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';

            /*
            $generate_pwd = get_option('woocommerce_registration_generate_password');
            if ('yes' === $generate_pwd ) {
            $password = wp_generate_password();
            } elseif (isset($_POST['password']) ) {
            $password = sanitize_text_field(wp_unslash($_POST['password']));
            if (empty($password) ) {
            $validation_error->add('Password', __('Please enter a valid Password!', 'chat-on-desk'));
            }
            } */

            $error = '';
            $page  = 2;

            $m  = isset($_REQUEST['billing_phone']) ? sanitize_email(wp_unslash($_REQUEST['billing_phone'])) : '';
            $m2 = isset($_REQUEST['email']) ? sanitize_text_field(wp_unslash($_REQUEST['email'])) : '';

            /*
            if (2 === $emailaccep ) {
            if (empty($mail) || ! is_email($mail) ) {
            $validation_error->add('Mail', __('Please enter a valid Email!', 'chat-on-desk'));
            }
            } elseif (1 === $emailaccep && ! empty($mail) ) {
            if (! is_email($mail) ) {
            $validation_error->add('Mail', __('Please enter a valid Email!', 'chat-on-desk'));
            }
            } */

            /*
            if (! empty($mail) && email_exists($mail) ) {
            $validation_error->add('MailinUse', __('Email already in use!', 'chat-on-desk'));
            } */

            $useMobAsUname = '';
            /*
            if (isset($_POST['username']) ) {
            $username = sanitize_text_field(wp_unslash($_POST['username']));
            if (empty($username) ) {
            $validation_error->add('Mail', __('Please enter a valid Username!', 'chat-on-desk'));
            }
            } */

            // important
            $mobileaccp = 1;
            if ($mobileaccp > 0 ) {

                $m = isset($_REQUEST['billing_phone']) ? sanitize_text_field(wp_unslash($_REQUEST['billing_phone'])) : '';
                if (is_numeric($m) ) {
                    $m     = sanitize_text_field($m);
                    $phone = $m;

                }

                /*
                 if (empty($ulogin) ) {
                 $check = username_exists($phone);
                 if (! empty($check) ) {
                  $validation_error->add('MobinUse', __('Mobile number already in use!', 'chat-on-desk'));
                 } else {
                  $ulogin = $phone;
                 }
                } */
                $ulogin = $phone;

                // $validation_error = apply_filters('woocommerce_registration_errors', $validation_error, $ulogin, $mail);

                // if (! $validation_error->get_error_code() ) {
                 $password = '';
                if (empty($password) ) {
                    $password = wp_generate_password();
                }
                 // $ulogin       = sanitize_user($ulogin, true);
                 $mail         = $ulogin . '@nomail.com';
                 $new_customer = wp_create_user($ulogin, $password, $mail);

                // }
            }
            /*
            else {
            if (empty($password) && $password === 2 ) {
            $validation_error->add('invalidpassword', __('Invalid password', 'chat-on-desk'));
            } elseif (empty($password) ) {
            $password = wp_generate_password();
            }
            if (empty($ulogin) ) {
            $ulogin = strstr($mail, '@', true);
            if (username_exists($ulogin) ) {
            $validation_error->add('MailinUse', __('Email is already in use!', 'chat-on-desk'));
            }
            }

            if (! $validation_error->get_error_code() ) {
            $ulogin        = sanitize_user($ulogin, true);
            $new_customer  = wp_create_user($ulogin, $password, $mail);
            $login_message = "<span class='msggreen'>User registered successfully.</span>";

            $page = 1;
            } else {

            }
            }
            */         // important

            /*
            if ($validation_error->get_error_code() )
            {
            $e = implode('<br />', $validation_error->get_error_messages());

            wc_add_notice('<strong>' . __('Error:', 'woocommerce') . '</strong> ' . $e, 'error');

            } else { */

            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);

            if (! is_wp_error($new_customer) ) {

                $chatondesk_defaultuserrole = get_option('chatondesk_defaultuserrole', 'customer');

                $userdata = array(
                'ID'         => $new_customer,
                'user_login' => $ulogin,
                'user_email' => $mail,
                'role'       => $chatondesk_defaultuserrole,
                );

                /*
                if (! empty($name) ) {
                $userdata['first_name']   = $name;
                $userdata['display_name'] = $name;
                } */

                $role = array(
                'ID'   => $new_customer,
                'role' => $chatondesk_defaultuserrole,
                );
                /*
                if (! empty($name) ) {
                $role['first_name']   = $name;
                $role['display_name'] = $name;

                } */

                wp_update_user($role);

                $new_customer_data = apply_filters('woocommerce_new_customer_data', $userdata);
                wp_update_user($new_customer_data);

                apply_filters('woocommerce_registration_auth_new_customer', true, $new_customer);
                $new_customer_data['user_pass']     = $password;
                $new_customer_data['billing_phone'] = $phone;

                do_action('woocommerce_created_customer', $new_customer, $new_customer_data, $password);

                // WooCommerceRegistrationForm::wcUserCreated();

                // self::wcUserCreated($new_customer,$new_customer_data);
                do_action('chatondesk_user_created', $new_customer, $new_customer_data);

                wp_set_auth_cookie($new_customer);

                /*
                if ( ! empty( $_POST['redirect'] ) ) {
                $redirect = sanitize_text_field( wp_unslash( $_POST['redirect'] ) );
                } elseif ( wc_get_raw_referer() ) {
                $redirect = wc_get_raw_referer();
                }

                $msg             = \ChatOnDesk\SmsAlertUtility::_create_json_response( 'Register successful', 'success' );
                $redirect        = apply_filters( 'cod_woocommerce_regwithmob_redirect', $redirect, $new_customer );
                $msg['redirect'] = $redirect;
                wp_send_json( $msg ); */
                exit();
            } else {
                // $validation_error->add('Error', __('Please try again', 'chat-on-desk'));
                wp_send_json(\ChatOnDesk\SmsAlertUtility::_create_json_response('Please try again', 'success'));
                exit();
            }
            // }
            // unset($_POST);
        }
    }

    /**
     * Generate random number function.
     *
     * @return string
     */
    public static function generate_random_number()
    {
        $length       = 12;
        $return_string = wp_rand(1, 9);
        $srtlength    = strlen($return_string);
        while ( $srtlength < $length ) {
            $return_string .= wp_rand(0, 9);
        }
        return $return_string;
    }
}
new WCSignupWithOTp();
?>
