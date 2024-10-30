<?php
/**
 * This file handles gravity form smsalert notification
 * This file handles gravity form smsalert notification
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

if (! defined('ABSPATH') ) {
    exit;
}

if (! is_plugin_active('gravityforms-master/gravityforms.php') 
    && ! is_plugin_active('gravityforms/gravityforms.php') 
) {
    return; 
}

GFForms::include_feed_addon_framework();

/**
 * This file handles gravity form smsalert notification
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * GF_SMS_Alert class.
 */
class GF_SMS_Alert extends GFFeedAddOn
{

    /**
     * Add on version
     *
     * @var stirng
     */
    protected $_version = '2.0.0';

    /**
     * Add on min_gravityforms_version
     *
     * @var stirng
     */
    protected $_min_gravityforms_version = '1.8.20';

    /**
     * Add on gravity and smsalert slug
     *
     * @var stirng
     */
    protected $_slug = 'gravity-forms-sms-alert';

    /**
     * Add full path
     *
     * @var stirng
     */
    protected $_full_path = __FILE__;

    /**
     * Addon title
     *
     * @var stirng
     */
    protected $_title = 'Chat On Desk';

    /**
     * Addon short title for addon.
     *
     * @var stirng
     */
    protected $_short_title = 'Chat On Desk';

    /**
     * Check mutliple feed allowed or not.
     *
     * @var bool
     */
    protected $_multiple_feeds = false;

    /**
     * Instance for smsalert addon.
     *
     * @var object
     */
    private static $_instance = null;
    
    /**
     * ErrorMsg for smsalert form setting.
     *
     * @var string
     */
    private $_errorMsg = null;

    /**
     * Get instance for gravity form.
     *
     * @return object
     */
    public static function get_instance()
    {

        if (null === self::$_instance ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set feed setting title.
     *
     * @return object
     */
    public function feed_settings_title()
    {
        return __('Chat On Desk', 'chatondesk-gravity-forms');
    }

    /**
     * Set feed setting fields.
     *
     * @return array
     */
    public function feed_settings_fields()
    {   
        $options = array(
        array(
        'title'  => 'Customer SMS Settings',
        'fields' => array(
        array(
                    'label'   => 'Enable Mobile Verification',
                    'type'    => 'checkbox',
                    'name'    => 'chatondesk_gForm_otp',
                    'class'   => 'mt-position-right',
                    'tooltip' => 'Enable otp',
                    'choices' => array(
                        array(
                           'label' => '',
                            'name'  => 'chatondesk_gForm_otp'
                        )
                     )
                ),
        array(
        'label'             => 'Customer Numbers',
        'type'              => 'text',
        'name'              => 'chatondesk_gForm_cstmer_nos',
        'tooltip'           => 'Enter Customer Numbers',
        'class'             => 'medium merge-tag-support mt-position-right',
        'feedback_callback' => array( $this, 'is_valid_setting'),
        ),
            array(
        'label'   => 'Customer Templates',
        'type'    => 'textarea',
        'name'    => 'chatondesk_gForm_cstmer_text',
        'tooltip' => 'Enter your Customer SMS Content',
        'default_value' => SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE'),
        'class'   => 'medium merge-tag-support mt-position-right',
        ),                    
        ),
        ),
        array(
        'title'  => 'Admin SMS Settings',
        'fields' => array(
        array(
        'label'             => 'Admin Numbers',
        'type'              => 'text',
        'name'              => 'chatondesk_gForm_admin_nos',
        'tooltip'           => 'Enter admin Numbers',
        'class'             => 'medium merge-tag-support mt-position-right',
        'feedback_callback' => array( $this, 'is_valid_setting' ),
        ),
        array(
         'label'         => 'Admin Templates',
         'type'          => 'textarea',
         'name'          => 'chatondesk_gForm_admin_text',
         'tooltip'       => 'Enter your admin SMS Content',
        'default_value' => SmsAlertMessages::showMessage(
            'DEFAULT_CONTACT_FORM_ADMIN_MESSAGE'
        ),
         'class'         => 'medium merge-tag-support mt-position-right',
                        
        ),
        ),
        ));
        if (is_plugin_active('gravityview/gravityview.php')) {
            $gf_form = new GF_Smsalert_Form();
            $statuses = $gf_form->getEnum();
            $cst_fields = $admin_fields = array();
            foreach ($statuses as $ks => $vs) {
                $cst_fields[] =  array(
                'label'   => $vs,
                'type'    => 'checkbox',
                'name'    => 'chatondesk_gform_status_ '. strtolower($vs),
                'class'   => 'mt-position-right',
                'tooltip' => 'chatondesk_gform_status_'. strtolower($vs),
                'choices' => array(
                array(
                 'label' => '',
                'name'  => 'chatondesk_gform_status_'. strtolower($vs)
                )
                )
                );        
                $cst_fields[] =    array(            
                'type'    => 'textarea',
                'name'    => 'chatondesk_gform_cstmer_'. strtolower($vs) .'_text',
                'tooltip' => 'Enter your Customer SMS Content',
                'default_value' => SmsAlertMessages::showMessage(
                    'DEFAULT_GRAVITY_NEW_USER'
                ),
                'class'   => 'medium merge-tag-support mt-position-right',
                );
                $admin_fields[] =  array(
                'label'   => $vs,
                'type'    => 'checkbox',
                'name'    => 'chatondesk_gform_status_'. strtolower($vs),
                'class'   => 'mt-position-right',
                'tooltip' => 'chatondesk_gform_status_'. strtolower($vs),
                'choices' => array(
                array(
                'label' => '',
                'name'  => 'chatondesk_gform_status_'. strtolower($vs)
                )
                )
                );        
                $admin_fields[] =    array(            
                'type'    => 'textarea',
                'name'    => 'chatondesk_gform_admin_'. strtolower($vs) .'_text',
                'tooltip' => 'Enter your Admin SMS Content',
                'default_value' => SmsAlertMessages::showMessage(
                    'DEFAULT_GRAVITY_NEW_ADMIN'
                ),
                'class'   => 'medium merge-tag-support mt-position-right',
                );                 
            }    
            
            
            $options[] =  array(
            'title'  => 'Customer notification when entry status change to',
            'fields' => $cst_fields, 
            );
            $options[] =  array(
            'title'  => 'Admin notification when entry status change to',
            'fields' => $admin_fields, 
            );
        }
        
        return $options;
    }
    
    
    /**
     * Handle form submission at gravity smsalert setting.
     *
     * @param array $feed_id  form feed_id. 
     * @param array $form_id  form form_id.
     * @param array $settings form settings.
     *
     * @return void
     */
    public function save_feed_settings( $feed_id, $form_id, $settings ) 
    {
        if (empty($settings['chatondesk_gForm_cstmer_nos']) 
            && !empty($settings['chatondesk_gForm_cstmer_text'])
        ) {
            $this->_errorMsg = true;
            GFCommon::add_error_message(
                __(
                    "Please enter
			your customer number.", 'chat-on-desk'
                )
            );
            $result = false;
        } else if (!empty($settings['chatondesk_gForm_otp']) 
            && empty($settings['chatondesk_gForm_cstmer_nos'])
        ) {
            $this->_errorMsg = true;
            GFCommon::add_error_message(
                __(
                    "Please enter
			your customer number.", 'chat-on-desk'
                )
            );
            $result = false;
        } else if (empty($settings['chatondesk_gForm_admin_nos']) 
            && !empty($settings['chatondesk_gForm_admin_text'])
        ) {
            $this->_errorMsg = true;        
            GFCommon::add_error_message(
                __(
                    "Please enter
			your admin number.", 'chat-on-desk'
                )
            );
            $result = false;
        } else {
            parent::save_feed_settings($feed_id, $form_id, $settings);
            $result = true;
        }
        return $result;
    }
    
    /**
     * Handle form submission at gravity smsalert setting save error message.
     *
     * @param array $sections form sections. 
     *
     * @return void
     */
    public function get_save_error_message( $sections ) 
    {
        return !empty($this->_errorMsg) ? '' : esc_html__(
            'There
		was an error while saving your settings.', 'chat-on-desk'
        );
    }
    
    /**
     * Handle form submission and send message to customer and admin.
     *
     * @param array $entry form entry. 
     * @param array $form  form form.
     *
     * @return void
     */
    public static function do_gForm_processing( $entry, $form )
    {    
        $entry_id = $entry['id'];
        $message    = '';
        $$cstmer_nos_pattern = '';
        $admin_nos  = '';
        $admin_msg  = '';
        $meta       = RGFormsModel::get_form_meta($entry['form_id']);       
        $feeds      = GFAPI::get_feeds(
            null, $entry['form_id'],
            'gravity-forms-sms-alert'
        );
        foreach ( $feeds as $feed ) {
            if (count($feed) > 0 && array_key_exists('meta', $feed) ) {
                $admin_msg          = $feed['meta']
                ['chatondesk_gForm_admin_text'];
                $admin_nos          = $feed['meta']
                ['chatondesk_gForm_admin_nos'];
                $cstmer_nos_pattern = $feed['meta']
                ['chatondesk_gForm_cstmer_nos'];
                $message            = $feed['meta']
                ['chatondesk_gForm_cstmer_text'];
            }
        }
        $cstmer_nos ='';
        foreach ( $meta['fields'] as $meta_field ) {            
            if (is_object($meta_field) ) {
                $field_id = $meta_field->id; 
                
                if (isset($entry[ $field_id ]) ) {
                    $label     = $meta_field->label;
                    $search    = '{' . $label . ':' . $field_id . '}';
                    $replace   = $entry[ $field_id ];
                    if ($cstmer_nos_pattern === $search ) {
                        $cstmer_nos = $replace;                        
                    }                    
                }            
            }            
        }
        
        
        
        
        if (! empty($cstmer_nos) && ! empty($message) ) {
            $gf_sms = new GF_Smsalert_Form();          
            $message = $gf_sms->parseSmsBody(
                $entry_id,
                $message
            );
            do_action('cod_send_sms', $cstmer_nos, $message);
        }
        if (! empty($admin_nos) && ! empty($admin_msg) ) {
            
            $gf_admin_sms = new GF_Smsalert_Form();          
            $admin_msg = $gf_admin_sms->parseSmsBody(
                $entry_id,
                $admin_msg
            );
            do_action('cod_send_sms', $admin_nos, $admin_msg);
        }
    }    
        
    
}
new GF_SMS_Alert();

add_action(
    'gform_after_submission', array( 'GF_SMS_Alert',
    'do_gForm_processing' ), 10, 2
); 




 /**
  * This file handles gravity form smsalert notification
  *
  * PHP version 5
  *
  * @category Handler
  * @package  ChatOnDesk
  * @author   Chat On Desk <support@cozyvision.com>
  * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
  * @link     https://www.chatondesk.com/
  * GF_Smsalert_Form class.
  */
class GF_Smsalert_Form extends \ChatOnDesk\FormInterface
{
    
    /**
     * Form Session Variable.
     *
     * @var stirng
     */
    private $form_session_var = \ChatOnDesk\FormSessionVars::GRAVITY_FORM;
    
    /**
     * Handle OTP form
     *
     * @return void
     */
     
    public $enum = array(
    '1' => 'approved',
    '2' => 'disapproved',
    '3' => 'unapproved',
    );     
    
    
    /**
     * GetEnum
     *
     * @return void
     */
    public function getEnum()
    {
        return $this->enum;
    }
    
    /**
     * Handle OTP form
     *
     * @return void
     */
    public function handleForm()
    {
        add_filter(
            'gform_submit_button', array( $this, 
            'add_otp_btn' ), 10, 2
        );
        add_action(
            'gform_preview_footer', array( $this, 
            'load_chatondesk_modal_html' ), 10, 1
        );

        if (is_plugin_active('gravityview/gravityview.php')) {
            foreach ($this->enum as $status) {
                add_action(
                    'gravityview/approve_entries/'.$status, array( $this,
                    'trigger_notifications' ), 10
                );
            } 
        }        
        ChatOnDesk\SAVerify::enqueue_otp_js_script();
    }
    
    
    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public static function isFormEnabled()
    {
        $user_authorize = new chatondesk_Setting_Options();
        $islogged       = $user_authorize->is_user_authorised();
        return ( $islogged &&
        (is_plugin_active('gravityforms-master/gravityforms.php') ||
        is_plugin_active('gravityforms/gravityforms.php') )) ? true : false;
    }
    
    /**
     * Handle smsalert gravity shortcode.
     *
     * @param int $form_id form id.
     *
     * @return string
     */
    function load_chatondesk_modal_html($form_id)
    {
        ChatOnDesk\SAVerify::add_shortcode_popup_html();
    }    
    
    /**
     * Process gravity form submission and send sms
     *
     * @param array $entry_id entry id.
     *
     * @return void
     */
    public function trigger_notifications( $entry_id = '')
    {
        $entry = GFAPI::get_entry($entry_id);
        $form_id = $entry['form_id'];    
        $cst_message             = '';
        $cst_notification    = '';
        $admin_message      = '';
        $cstmer_nos_pattern  = '';             
        $admin_nos  = '';             
        $admin_notification = '';        
        $meta       = RGFormsModel::get_form_meta($entry['form_id']);      
        $feeds      = GFAPI::get_feeds(
            null, $entry['form_id'],
            'gravity-forms-sms-alert'
        );        
        foreach ( $feeds as $feed ) {        
            if (count($feed) > 0 && array_key_exists('meta', $feed) ) {
                $status = $this->enum[$entry['is_approved']];
                
                $cst_message              = $feed['meta']
                ['chatondesk_gform_cstmer_'. $status .'_text'];
                
                $cst_notification         = $feed['meta']
                ['chatondesk_gform_status_'. $status];
                $admin_message          = $feed['meta']                
                ['chatondesk_gform_admin_'. $status .'_text'];                
                $admin_notification     = $feed['meta']
                ['chatondesk_gform_status_'.$status];                
                $cstmer_nos_pattern             = $feed['meta']
                ['chatondesk_gForm_cstmer_nos']; 
                $admin_nos                      = $feed['meta']
                ['chatondesk_gForm_admin_nos'];                
            }
        }         
        $cstmer_nos ='';
        
        foreach ( $meta['fields'] as $meta_field ) {            
            if (is_object($meta_field) ) {
                $field_id = $meta_field->id; 
                
                if (isset($entry[ $field_id ]) ) {
                    $label     = $meta_field->label;
                    $search    = '{' . $label . ':' . $field_id . '}';
                    $replace   = $entry[ $field_id ];
                    $cst_message   = str_replace(
                        $search,
                        $replace, $cst_message
                    );                     
                    $admin_message   = str_replace(
                        $search,
                        $replace, $admin_message
                    );
            
                    if ($cstmer_nos_pattern === $search ) {
                        $cstmer_nos = $replace;                        
                    }                    
                }            
            }            
        }     
        if (! empty($cstmer_nos) 
            && ! empty($cst_message) 
            && !empty($cst_notification)
        ) {
            $message = $this->parseSmsBody(
                $entry_id,
                $cst_message
            );
            do_action('cod_send_sms', $cstmer_nos, $message);
        }
        /* Admin  SMS Notification */        
        if (! empty($admin_nos) 
            && ! empty($admin_message) 
            && ! empty($admin_notification) 
        ) {
            
            $admin_msg = $this->parseSmsBody(
                $entry_id,
                $admin_message                
            );
            do_action('cod_send_sms', $admin_nos, $admin_msg);
        }      
    }    
    
    /**
     * Handle smsalert gravity shortcode.
     *
     * @param object $button get button.
     * @param object $form   get form array.
     *
     * @return string
     */     
    function add_otp_btn( $button, $form )
    {
        $form_id          = $form["fields"][0]->formId;
        $feeds            = GFAPI::get_feeds(
            null, $form_id,
            'gravity-forms-sms-alert'
        );
        if (!empty($feeds->errors)) {
            return $button;
        } 
        $phone_field     = !empty(
            $feeds[0]['meta']
            ['chatondesk_gForm_cstmer_nos']
        )? $feeds[0]
        ['meta']['chatondesk_gForm_cstmer_nos']:'';
        if (empty($phone_field) 
            || empty($feeds[0]['meta']['chatondesk_gForm_otp'])
        ) {
            return $button; 
        }
        $phone_field_id  = preg_replace('/[^0-9]/', '', $phone_field);
        return $button .= do_shortcode(
            '[cod_verify id=""
		phone_selector="input_'.$phone_field_id.'"
		submit_selector= "#gform_submit_button_'.$form_id.'" ]'
        );
    }

    /**
     * Replace variables for sms contennt
     *
     * @param int    $entry_id entry_id.
     * @param string $content  sms content to be sent.
     *
     * @return string
     */
    public function parseSmsBody( $entry_id = '', $content = null)
    {
        $search        = array();
        $replace       = array();
        $entry = GFAPI::get_entry($entry_id);        
        $status = !empty($entry['is_approved'])? $this->enum[$entry['is_approved']]:'';        
        $form_id = $entry['form_id'];
        $meta       = RGFormsModel::get_form_meta($entry['form_id']);        
        foreach ($meta['fields'] as $field) {             
            if (is_object($field) ) {                
                $name = $field->label;                
                if (is_array($field['inputs'])) {
                    foreach ($field['inputs'] as $vss) {
                        $id          = $vss['id'];                        
                        $label       = $vss['label'];
                        if (!empty($entry[$id])) {
                            $search[]    = '{' . $name .' '.
                            '('. $label .')' . ':' . $id . '}';
                            $replace[]   = $entry[$id];                        
                        }                    
                    }
                } else {
                    $id =$field->id;
                    $label = $field->label;                    
                    $search[]    = '{' . $label . ':' . $id . '}';                
                    $replace[]   = $entry[$id];
                } 
            }
        }  
        
        $replace[] = $status;
        $search[]    = '[user_status]';
        $content = str_replace($search, $replace, $content);        
        return $content;               
    }    
    
    /**
     * Handle after failed verification
     *
     * @param object $user_login   users object.
     * @param string $user_email   user email.
     * @param string $phone_number phone number.
     *
     * @return void
     */
    public function handle_failed_verification($user_login,$user_email,$phone_number )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        if (! empty($_REQUEST['option'])
            && sanitize_text_field(
                wp_unslash($_REQUEST['option'])
            ) ===        'chatondesk-validate-otp-form' 
        ) {
            wp_send_json(
                \ChatOnDesk\SmsAlertUtility::_create_json_response(
                    SmsAlertMessages::showMessage(
                        'INVALID_OTP'
                    ), 'error'
                )
            );
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'verification_failed';
        }
    }
    
    /**
     * Handle after post verification
     *
     * @param string $redirect_to  redirect url.
     * @param object $user_login   user object.
     * @param string $user_email   user email.
     * @param string $password     user password.
     * @param string $phone_number phone number.
     * @param string $extra_data   extra hidden fields.
     *
     * @return void
     */
    public function handle_post_verification( $redirect_to,$user_login,$user_email,$password,$phone_number,$extra_data )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        if (! isset($_SESSION[ $this->form_session_var ]) ) {
            return;
        }
        if (! empty($_REQUEST['option'])  
            && sanitize_text_field(wp_unslash($_REQUEST['option'])) === 'chatondesk-validate-otp-form' 
        ) {
            wp_send_json(
                \ChatOnDesk\SmsAlertUtility::_create_json_response(
                    'OTP
				Validated Successfully.', 'success'
                )
            );
            exit();
        } else {
            $_SESSION[ $this->form_session_var ] = 'validated';
        }
    }
    
    /**
     * Check current form submission is ajax or not
     *
     * @param bool $is_ajax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play($is_ajax )
    {
        \ChatOnDesk\SmsAlertUtility::checkSession();
        return isset($_SESSION[ $this->form_session_var ]) ? true : $is_ajax;
    }

    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
        unset($_SESSION[ $this->form_session_var ]);
    }
    
    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {
    }
}
 new GF_Smsalert_Form();