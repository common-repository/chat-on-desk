<?php
/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

if (! is_plugin_active('elementor/elementor.php')) {
    return; 
}


use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography as Scheme_Typography;
use Elementor\Group_Control_Background;
use Elementor\Core\Schemes\Color as Scheme_Color;

use Elementor\Plugin as Elementor;

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 *
 * CodPopup class
 */
class CodPopup
{
    private $app = null;
    
    /**
     * Construct function
     *
     * @param $app app.
     *
     * @return array
     */
    public function __construct($app)
    {        
        $this->app = $app;
        add_action('elementor/widgets/register', [$this, 'initWidget']);        
        add_action('admin_init', array( $this, 'addThemeCaps'));
        add_action('elementor/document/before_save', array( $this, 'checkChatondeskWidget' ), 100, 2);
        add_action('elementor/document/before_save', array( $this, 'checkChatondeskExitIntentWidget' ), 100, 2);
        add_action('elementor/document/before_save', array( $this, 'checkChatondeskShareCartWidget' ), 100, 2);
        add_action('elementor/document/before_save', array( $this, 'checkChatondeskNotifyMeWidget' ), 100, 2);
        $this->routeData();        
        $name = 'chat-on-desk';
        $args = array(
        'public'    => true,
        'show_in_menu'     => false,
        'label' => esc_html__('Chat On Desk', 'chat-on-desk'),
        'supports' => array('title', 'editor', 'elementor', 'permalink'),
        'capability_type' => array('chat-on-desk','chat-on-desk'),
        'capabilities' => array(
        'edit_post'          => 'edit_chat_on_desk', 
        'edit_posts' => 'edit_chat_on_desk',
        'read_post' => 'read_chat_on_desk',
        ),
        );
        add_action(
            'init', function () use ($name,$args) {
                register_post_type($name, $args);
                flush_rewrite_rules();
            }
        );        
    }
    
    /**
     * Add theme caps.
     *
     * @return void
     */
    function addThemeCaps()
    {
        // gets the administrator role
        $admins = get_role('administrator');
        $admins->add_cap('edit_chat_on_desk'); 
        $admins->add_cap('edit_chat_on_desks'); 
        $admins->add_cap('read_chat_on_desk'); 
    }
    
    /**
     * Init widgets function
     *
     * @return array
     */
    public function initWidget()
    {
        $widgets_manager = Elementor::instance()->widgets_manager;
        if (file_exists(plugin_dir_path(__DIR__) . 'helper/class-codpopupwidget.php')) {            
            include_once plugin_dir_path(__DIR__) . 'helper/class-codpopupwidget.php';
            $widgets_manager->register(new CodPopupWidget());
        }
        if (file_exists(plugin_dir_path(__DIR__) . 'helper/class-codexitintentwidget.php')) {      
            include_once plugin_dir_path(__DIR__) . 'helper/class-codexitintentwidget.php';
            $widgets_manager->register(new CodExitIntentWidget());
        } 
        if (file_exists(plugin_dir_path(__DIR__) . 'helper/class-codsharecartwidget.php')) {      
            include_once plugin_dir_path(__DIR__) . 'helper/class-codsharecartwidget.php';
            $widgets_manager->register(new CodShareCartWidget());
        }
        if (file_exists(plugin_dir_path(__DIR__) . 'helper/class-codnotifymewidget.php')) {      
            include_once plugin_dir_path(__DIR__) . 'helper/class-codnotifymewidget.php';
            $widgets_manager->register(new CodNotifyMeWidget());
        }         
    }

     /**
      * RouteData function
      *
      * @return array
      */
    public function routeData()
    {        
        if (!empty($_GET['post_name'])) {        
            switch ($_GET['post_name']) {
            case "cod_modal_style":
                    $otp_template_style = ChatOnDesk\chatondesk_get_option('otp_template_style', 'chatondesk_general', 'popup-1');                
                $post = get_page_by_path('cod_modal_style', OBJECT, 'chat-on-desk');
                if (!empty($post) ) {
                    $builder_form_id = $post->ID;
                } else {
                    $template_content = [
                    [
                    "id" => "a819417", 
                    "elType" => "section", 
                    "settings" => [
                    ], 
                    "elements" => [
                                               [
                                                  "id" => "cbffca4", 
                                                  "elType" => "column", 
                                                  "settings" => [
                                                     "_column_size" => 100, 
                                                     "_inline_size" => null 
                                                  ], 
                                                  "elements" => [
                                                        [
                                                           "id" => "df1545a", 
                                                           "elType" => "widget", 
                                                           "settings" => [
                                                              "form_list" => $otp_template_style, 
                                                              "cod_ele_f_mobile_lbl" => \ChatOnDesk\SmsAlertMessages::showMessage('OTP_SENT_PHONE'), 
                                                              "cod_ele_f_mobile_botton" => \ChatOnDesk\SmsAlertMessages::showMessage('VALIDATE_OTP'),
                                                              "cod_ele_f_otp_resend"=> esc_html__('Didn\'t receive the code?', 'chat-on-desk'),
                                                              "cod_ele_f_resend_btn"=> esc_html__('Resend', 'chat-on-desk'),
                                                              "cod_otp_re_send_timer"=>    '15',                     
                                                              "max_otp_resend_allowed"=>    '4'                                 
                                                           ], 
                                                           "elements" => [
                                                              ], 
                                                           "widgetType" => "chatondesk-modal-widget" 
                                                        ] 
                                                     ], 
                                                  "isInner" => false 
                                               ] 
                    ], 
                    "isInner" => false 
                    ] 
                    ];  //serialisedata 
                    $builder_form_id = $this->create_form('cod_modal_style', $template_content, $data = []);
                }            
                $this->get_editor($builder_form_id);
                
                break; 
            case "cod_exitintent_style":                    
                $post = get_page_by_path('cod_exitintent_style', OBJECT, 'chat-on-desk');
                if (!empty($post) ) {                
                    $builder_form_id = $post->ID;
                } else {
                    $template_content = [
                     [
                    "id" => "a819417", 
                    "elType" => "section", 
                    "settings" => [
                                        ], 
                                        "elements" => [
                                              [
                                                 "id" => "cbffca4", 
                                                 "elType" => "column", 
                                                 "settings" => [
                                                    "_column_size" => 100, 
                                                    "_inline_size" => null 
                                                 ], 
                                                 "elements" => [
                                                       [
                                                          "id" => "df1545a", 
                                                          "elType" => "widget", 
                                                          "settings" => [
                                                               "cod_ele_f_mobile_title"=> esc_html__('You were not leaving your cart just like that, right?', 'chat-on-desk'),
                                                               "cod_ele_f_mobile_description"=>esc_html__('Just enter your mobile number below to save your shopping cart for later. And, who knows, maybe we will even send you a sweet discount code :)', 'chat-on-desk'),
                                                               "cod_ele_f_mobile_label"=>esc_html__('Your Mobile No:', 'chat-on-desk'),    
                                                               "cod_submit_button"=> esc_html__('Save cart', 'chat-on-desk')
                                                           ], 
                                                          "elements" => [
                                                             ], 
                                                          "widgetType" => "chatondesk-exitintent-widget" 
                                                       ] 
                                                    ], 
                                                 "isInner" => false 
                                              ] 
                                           ], 
                                        "isInner" => false 
                     ]
                    ];  //serialisedata  
                    $builder_form_id = $this->create_form('cod_exitintent_style', $template_content, $data = []);
                }            
                $this->get_editor($builder_form_id);                
                break;
            case "cod_sharecart_style":                        
                    $post = get_page_by_path('cod_sharecart_style', OBJECT, 'chat-on-desk');
                if (!empty($post) ) {                
                    $builder_form_id = $post->ID;
                } else {
                    $template_content = [
                    [
                    "id" => "a819417", 
                    "elType" => "section", 
                    "settings" => [
                    ], 
                    "elements" => [
                    [
                    "id" => "cbffca4", 
                    "elType" => "column", 
                    "settings" => [
                    "_column_size" => 100, 
                    "_inline_size" => null 
                    ], 
                    "elements" => [
                    [
                    "id" => "df1545a", 
                    "elType" => "widget", 
                    "settings" => ["cod_ele_f_sharecart_title"=> esc_html__('Share cart', 'chat-on-desk'),
                    "cod_ele_f_user_placehoder"=>esc_html__('Your Name*', 'chat-on-desk'),
                    "cod_ele_f_user_phone_placeholder"=>esc_html__('Your Mobile No*', 'chat-on-desk'),    
                    "cod_ele_f_frnd_placeholder"=> esc_html__('Friend Name*', 'chat-on-desk'),   
                    "cod_ele_f_frnd_phone_placeholder"=> esc_html__('Friend Mobile No*', 'chat-on-desk'),   
                    "cod_submit_button"=> esc_html__('Share Cart', 'chat-on-desk')  ], 
                    "elements" => [
                    ], 
                    "widgetType" => "chatondesk-sharecart-widget" 
                    ] 
                    ], 
                    "isInner" => false 
                    ] 
                    ], 
                    "isInner" => false 
                    ]
                    ];  //serialisedata  
                    $builder_form_id = $this->create_form('cod_sharecart_style', $template_content, $data = []);
                }
                $this->get_editor($builder_form_id);
                break;
            case "cod_notifyme_style":                        
                     $post = get_page_by_path('cod_notifyme_style', OBJECT, 'chat-on-desk');
                if (!empty($post) ) {                
                    $builder_form_id = $post->ID;
                } else {
                    $template_content = [
                    [
                    "id" => "a819417", 
                    "elType" => "section", 
                    "settings" => [
                    ], 
                    "elements" => [
                    [
                    "id" => "cbffca4", 
                    "elType" => "column", 
                    "settings" => [
                    "_column_size" => 100, 
                    "_inline_size" => null 
                    ], 
                    "elements" => [
                    [
                    "id" => "df1545a", 
                    "elType" => "widget", 
                    "settings" => [
                    "cod_ele_f_notifyme_title"=> esc_html__('Notify Me when back in stock', 'chat-on-desk'),
                    "cod_ele_f_notifyme_placehoder"=>esc_html__('Enter Number Here', 'chat-on-desk'),
                    "cod_notifyme_button"=>esc_html__('Notify Me', 'chat-on-desk')
                        ], 
                        "elements" => [
                        ], 
                        "widgetType" => "chatondesk-notifyme-widget" 
                    ] 
                    ], 
                    "isInner" => false 
                    ] 
                    ], 
                    "isInner" => false 
                    ]
                    ];  //serialisedata  
                    $builder_form_id = $this->create_form('cod_notifyme_style', $template_content, $data = []);
                }            
                     $this->get_editor($builder_form_id);
                break;
            }
        }            
    }
    
    /**
     * Create form function
     *
     * @param string $title            title.
     * @param string $template_content template_content.
     * @param string $data             data.
     *
     * @return void
     */
    public function create_form($title,$template_content, $data = [])
    {        
        $user_id = get_current_user_id();        
        $defaults = array(
            'post_author'  => $user_id,
            'post_content' => '',
            'post_title'   => $title,
            'post_status'  => 'publish',
            'post_type'    => 'chat-on-desk',
            'post_name'    => $title,
        );        
        $builder_form_id = wp_insert_post($defaults);
        $default_settings = array();
        $default_settings['form_title'] = $defaults['post_title'];        
        if (isset($data['form_type']) && !empty($data['form_type'])) {
            $default_settings['form_type'] = $data['form_type'];
            // Unset form type from $data array
            unset($data['form_type']);
        }
        update_post_meta($builder_form_id, '_wp_page_template', 'elementor_header_footer');
        update_post_meta($builder_form_id, '_elementor_edit_mode', 'ElementorWidget');
        if ($template_content != null) {
            update_post_meta($builder_form_id, '_elementor_data', json_encode($template_content));
        }
        return $builder_form_id;
    }
    
    
    /**
     * CheckChatondeskWidget
     *
     * @param $obj   obj
     * @param $datas datas
     *
     * @return void
     */
    public function checkChatondeskWidget($obj, $datas)
    { 
        $post_title = !empty($datas['settings']['post_title'])?$datas['settings']['post_title']:'';
        if ($post_title == "cod_modal_style") {
            $chatondesk_widget_added = 0;
            $cod_otp_re_send_timer = '';
            $max_otp_resend_allowed = '';
            if (!empty($datas['elements'])) {          
                foreach ( $datas['elements'] as $data ) {
                    if (array_key_exists('elements', $data) ) {
                        foreach ( $data['elements'] as $element ) {
                            if (array_key_exists('elements', $element) ) {
                                foreach ( $element['elements'] as $setting ) {
                                    $widgetType = !empty($setting['widgetType'])?$setting['widgetType']:'';
                                    if (!empty($widgetType) && $widgetType == 'chatondesk-modal-widget') {
                                        $chatondesk_widget_added++;
                                        $cod_otp_re_send_timer = !empty($setting['settings']['cod_otp_re_send_timer'])?$setting['settings']['cod_otp_re_send_timer']:''; 
                                        $max_otp_resend_allowed = !empty($setting['settings']['max_otp_resend_allowed'])?$setting['settings']['max_otp_resend_allowed']:'';
                                    }
                                }
                            }
                        }
                    }
                }          
            }
            if ($chatondesk_widget_added==1) {
                if (empty($cod_otp_re_send_timer)) {
                    wp_send_json_error([ 'statusText' => esc_html__("OTP Re-send Timer field can't be empty.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
                } else if (empty($max_otp_resend_allowed)) {
                    wp_send_json_error([ 'statusText' => esc_html__("Max OTP Re-send Allowed field can't be empty.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
                }
            } else if ($chatondesk_widget_added==0) {
                wp_send_json_error([ 'statusText' => esc_html__('Please add chatondesk modal widget.', 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            } else if ($chatondesk_widget_added > 1) {
                wp_send_json_error([ 'statusText' => esc_html__("You can't add multiple chatondesk modal widget.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            }
        }
    }
    
    /**
     * Get editor function
     *
     * @param string $builder_form_id builder_form_id.
     * @param string $post_type       post_type.
     *
     * @return void
     */
    public function get_editor( $builder_form_id )
    {        
        $url = get_admin_url() . 'post.php?post='.$builder_form_id.'&action=elementor';
        wp_safe_redirect($url);
         exit;        
    }   
    
    /**
     * CheckChatondeskExitIntentWidget
     *
     * @param $obj   obj
     * @param $datas datas
     *
     * @return void
     */
    public function checkChatondeskExitIntentWidget($obj, $datas)
    { 
        $post_title = !empty($datas['settings']['post_title'])?$datas['settings']['post_title']:'';
        if ($post_title == "cod_exitintent_style") {
            $chatondesk_exitintent_widget_added = 0;
            if (!empty($datas['elements'])) {          
                foreach ( $datas['elements'] as $data ) {
                    if (array_key_exists('elements', $data) ) {
                        foreach ( $data['elements'] as $element ) {
                            if (array_key_exists('elements', $element) ) {
                                foreach ( $element['elements'] as $setting ) {
                                    $widgetType = !empty($setting['widgetType'])?$setting['widgetType']:'';
                                    if (!empty($widgetType) && $widgetType == 'chatondesk-exitintent-widget') {
                                        $chatondesk_exitintent_widget_added++;
                                    }
                                }
                            }
                        }
                    }
                }          
            }
            if ($chatondesk_exitintent_widget_added==0) {
                wp_send_json_error([ 'statusText' => esc_html__('Please add chatondesk exit intent widget.', 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            } else if ($chatondesk_exitintent_widget_added > 1) {
                wp_send_json_error([ 'statusText' => esc_html__("You can't add multiple chatondesk exit intent widget.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            }
        }
    }
    
    /**
     * CheckChatondeskShareCartWidget
     *
     * @param $obj   obj
     * @param $datas datas
     *
     * @return void
     */
    public function checkChatondeskShareCartWidget($obj, $datas)
    { 
        $post_title = !empty($datas['settings']['post_title'])?$datas['settings']['post_title']:'';
        if ($post_title == "cod_sharecart_style") {
            $chatondesk_sharecart_widget_added = 0;
            if (!empty($datas['elements'])) {          
                foreach ( $datas['elements'] as $data ) {
                    if (array_key_exists('elements', $data) ) {
                        foreach ( $data['elements'] as $element ) {
                            if (array_key_exists('elements', $element) ) {
                                foreach ( $element['elements'] as $setting ) {
                                    $widgetType = !empty($setting['widgetType'])?$setting['widgetType']:'';
                                    if (!empty($widgetType) && $widgetType == 'chatondesk-sharecart-widget') {
                                        $chatondesk_sharecart_widget_added++;
                                    }
                                }
                            }
                        }
                    }
                }          
            }
            if ($chatondesk_sharecart_widget_added==0) {
                wp_send_json_error([ 'statusText' => esc_html__('Please add chatondesk share cart widget.', 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            } else if ($chatondesk_sharecart_widget_added > 1) {
                wp_send_json_error([ 'statusText' => esc_html__("You can't add multiple chatondesk share cart widget.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            }
        }
    }
    /**
     * CheckChatondeskNotifyMeWidget
     *
     * @param $obj   obj
     * @param $datas datas
     *
     * @return void
     */
    public function checkChatondeskNotifyMeWidget($obj, $datas)
    { 
        $post_title = !empty($datas['settings']['post_title'])?$datas['settings']['post_title']:'';
        if ($post_title == "cod_notifyme_style") {
            $chatondesk_notifyme_widget_added = 0;
            if (!empty($datas['elements'])) {          
                foreach ( $datas['elements'] as $data ) {
                    if (array_key_exists('elements', $data) ) {
                        foreach ( $data['elements'] as $element ) {
                            if (array_key_exists('elements', $element) ) {
                                foreach ( $element['elements'] as $setting ) {
                                    $widgetType = !empty($setting['widgetType'])?$setting['widgetType']:'';
                                    if (!empty($widgetType) && $widgetType == 'chatondesk-notifyme-widget') {
                                        $chatondesk_notifyme_widget_added++;
                                    }
                                }
                            }
                        }
                    }
                }          
            }
            if ($chatondesk_notifyme_widget_added==0) {
                wp_send_json_error([ 'statusText' => esc_html__('Please add chatondesk Notify me widget.', 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            } else if ($chatondesk_notifyme_widget_added > 1) {
                wp_send_json_error([ 'statusText' => esc_html__("You can't add multiple chatondesk Notifyme widget.", 'chat-on-desk'),'readyState'=>4,'status'=>500 ]);
            }
        }
    }
    
    /**
     * Get getModelStyle
     *
     * @param string $callback callback.    
     *
     * @return void
     */
    public static function getModelStyle($callback=null)
    {
        $otp_length             = esc_attr(\ChatOnDesk\SmsAlertUtility::get_otp_length());
		$default_channel = ChatOnDesk\chatondesk_get_option('chatondesk_api', 'chatondesk_gateway', '');
        $alternate_channel   = (array)ChatOnDesk\chatondesk_get_option('alternate_channel', 'chatondesk_general', null);
        $cod_label        = ( ! empty($callback['cod_label']) ) ? $callback['cod_label'] :\ChatOnDesk\SmsAlertMessages::showMessage('OTP_SENT_PHONE');
        $placeholder     = ( ! empty($callback['placeholder']) ) ? $callback['placeholder'] : '';
        $otp_template_style     = ( ! empty($callback['otp_template_style']) ) ? $callback['otp_template_style'] : 'popup-1';
        $digit_class = ($otp_template_style!='popup-1')?(($otp_template_style=='popup-3')?'digit-group popup-3':'digit-group'):'';
        $hide_class = ($otp_template_style=='popup-1')?'hide':'';
        $cod_button       = (! empty($callback['cod_button']) ) ? $callback['cod_button'] :\ChatOnDesk\SmsAlertMessages::showMessage('VALIDATE_OTP');
        $cod_resend_otp   = ( ! empty($callback['cod_resend_otp']) ) ? $callback['cod_resend_otp'] :'Didn t receive the code?';
        $cod_resend_btns  = ( ! empty($callback['cod_resend_btns']) ) ? $callback['cod_resend_btns'] :'Resend';
        
                
        $content = '<div class="modal-body"><div style="margin:1.7em 1.5em;"><div style="position:relative" class="cod-message">'.esc_attr($cod_label).'</div></div>		
		<div class="chatondesk_validate_field '.esc_attr($digit_class).'" style="margin:1.5em">
		<input type="number" class="otp-number '.esc_attr($hide_class).'" id="digit-1" name="digit-1" oninput="codGroup(this)" onkeyup="codtabChange(1,this)" data-next="digit-2" style="margin-right: 5px!important;" data-max="1"  autocomplete="off"/>';
        
        $j = $otp_length - 1;
        $input = '';
        for ( $i = 1; $i < $otp_length; $i++ ) {
            $input.= '<input type="number" class="otp-number '.esc_attr($hide_class).'" id="digit-'.esc_attr($i + 1).'" name="digit-'.esc_attr($i + 1).'" oninput="codGroup(this)" onkeyup="codtabChange('.esc_attr($i + 1).',this)" data-next="digit-'.esc_attr($i + 2).'" data-previous="digit-'.esc_attr($otp_length - $j--).'" data-max="1" autocomplete="off">';
        }
        $content.= $input;
        $content.= '<input type="number" oninput="codGroup(this)" name="chatondesk_customer_validation_otp_token" autofocus="true" placeholder="'.esc_attr($placeholder).'" id="chatondesk_customer_validation_otp_token" class="input-text otp_input" pattern="[0-9]{'.esc_attr($otp_length).'}" title="Only digits within range 4-8 are allowed." data-max="' . esc_attr($otp_length) . '">';
        
        $content.= '<button type="button" name="chatondesk_otp_validate_submit" style="color:grey; pointer-events:none;" id="cod_verify_otp" class="button chatondesk_otp_validate_submit" value="Validate OTP">'.esc_attr($cod_button).'</button>';
		
		$content.= '<input type="hidden" id="cod_channel" value="'.esc_attr($default_channel).'">';
		if(sizeof($alternate_channel)>1)
		{
			//$content.= '<br/><select name="channel" class="sel_sendotp">';
			//$content.= '<option>Send Via</option>';
			//foreach($alternate_channel as $channel)
			//{
				//$content.= '<option value="'.esc_attr($channel).'">'.esc_attr($channel).'</option>';
			//}
			//$content.= '</select>';
		}
		elseif(sizeof($alternate_channel)==1)
		{
			//$content.= '</br><a href="javascript:void(0)" channel="'.esc_attr($alternate_channel[0]).'" style="float:right" class="sendotp">Send Via '.esc_attr($alternate_channel[0]).'</a>';
		}
		
		$content.= '<div style="display:flex;flex-direction:row-reverse;"><a class="cod_resend_btn" onclick="codResendOTP(this)">'.esc_attr($cod_resend_btns).'</a><span class="cod_timer"><span class="satimer">00:00:00</span> sec</span><span class="cod_forgot">'.esc_attr($cod_resend_otp).'</span></div></div></div>';    
        return $content;        
    }
    
    /**
     * Get ExitIntentStyle
     *
     * @param string $callback callback.    
     *
     * @return void
     */
    public static function getExitIntentStyle($callback=null)
    {        
         $cod_title          = !empty($callback['cod_title']) ? $callback['cod_title'] : esc_html__('You were not leaving your cart just like that, right?', 'chat-on-desk');
         
        $cod_description    = !empty($callback['cod_description']) ? $callback['cod_description'] : esc_html__('Just enter your mobile number below to save your shopping cart for later. And, who knows, maybe we will even send you a sweet discount code :)', 'chat-on-desk');
        $cod_label          = !empty($callback['cod_label']) ? $callback['cod_label'] : esc_html__('Your Mobile No:', 'chat-on-desk');
        $cod_placeholder    = !empty($callback['cod_placeholder']) ? $callback['cod_placeholder'] : "";
         $cod_button         = !empty($callback['cod_button']) ? $callback['cod_button'] : esc_html__('Save cart', 'chat-on-desk');
         
        
           $content = '<div id="cart-exit-intent-form-content-r">
               <h2 class ="cod_title">'.esc_attr($cod_title).'</h2>
			   <p class ="cod_description">'.esc_attr($cod_description).'</p> 
               <form>
                    <label for="cart-exit-intent-mobile" id="cod_label">'.esc_attr($cod_label).'</label>                   
                    <input type="text" id="cart-exit-intent-mobile" class="phone-valid" size="20" placeholder="'.esc_attr($cod_placeholder).'" required="">
                    <button type="submit" name="cart-exit-intent-submit" id="cart-exit-intent-submit" class="button" value="submit">'.esc_attr($cod_button).'</button>'.wp_nonce_field("chatondesk_wp_abcart_nonce", "chatondesk_abcart_nonce", true, false).'
			</form>
            </div>';
            
        return $content;
      
    }
    
    /**
     * Get SharCartStyle
     *
     * @param string $callback callback.    
     *
     * @return void
     */
    public static function getShareCartStyle($callback=null)
    {    
        $cod_title         = !empty($callback['cod_title']) ? $callback['cod_title'] : esc_html__('Share cart', 'chat-on-desk');         
        $cod_user_placeholder    = !empty($callback['cod_user_placeholder']) ? $callback['cod_user_placeholder'] : esc_html__('Your Name*', 'chat-on-desk');
        $cod_user_phone          = !empty($callback['cod_user_phone']) ? $callback['cod_user_phone'] : esc_html__('Your Mobile No*', 'chat-on-desk');
        $cod_frnd_placeholder    = !empty($callback['cod_frnd_placeholder']) ? $callback['cod_frnd_placeholder'] : esc_html__('Friend Name*', 'chat-on-desk');
        $cod_frnd_phone    = !empty($callback['cod_frnd_phone']) ? $callback['cod_frnd_phone'] : esc_html__('Friend Mobile No*', 'chat-on-desk');
        $cod_sharecart_button         = !empty($callback['cod_sharecart_button']) ? $callback['cod_sharecart_button'] : esc_html__('Share Cart', 'chat-on-desk');         
        $current_user_id = get_current_user_id();
        $phone = ( get_user_meta($current_user_id, 'billing_phone', true) !== '' ) ? \ChatOnDesk\SmsAlertUtility::formatNumberForCountryCode(get_user_meta($current_user_id, 'billing_phone', true)) : '';
        $uname = ( get_user_meta($current_user_id, 'first_name', true) !== '' ) ? ( get_user_meta($current_user_id, 'first_name', true) ) : '';
        
        $content = '<div class="chatondesk_scp_close_modal-content modal-content">
                <div class="chatondesk_scp_inner_div">
                    <div class="close"><span></span></div>
                    <form class="sc_cod_form">
                        <ul id="chatondesk_scp_ul">
                            <h2 class="box-title">'.esc_attr($cod_title).'</h2>
                            <li class="savecart_li">
                                <input type="text" name="sc_cod_uname" id="sc_cod_uname" placeholder="'.esc_attr($cod_user_placeholder).'" value="'.esc_attr($uname).'">
                            </li>
                            <li class="savecart_li">
                                <input type="text" name="sc_cod_umobile" id="sc_cod_umobile" placeholder="'.esc_attr($cod_user_phone).'" class="phone-valid" value="'.esc_attr($phone).'">
                            </li>
                            <li class="savecart_li">
                                <input type="text" name="sc_cod_fname" id="sc_cod_fname" placeholder="'.esc_attr($cod_frnd_placeholder).'">
                            </li>
                            <li class="savecart_li">
                                <input type="text" name="sc_cod_fmobile" id="sc_cod_fmobile" placeholder="'.esc_attr($cod_frnd_phone).'" class="phone-valid">
                            </li>
                            <li class="savecart_li">
                                <button class="button btn" id="sc_cod_btn" name="sc_cod_btn"><span class="button__text">'.esc_attr($cod_sharecart_button).'</span></button>
                            </li>
                        </ul>
						'.wp_nonce_field("chatondesk_wp_sharecart_nonce", "chatondesk_sharecart_nonce", true, false).'
						</form>
                    <div id="sc_cod_response"></div>
                </div>                
            </div>';
            
        return $content;
      
    }
    /**
     * Get NotifyMeStyle
     *
     * @param string $callback callback.    
     *
     * @return void
     */
    public static function getNotifyMeStyle($callback=null)
    { 
        $notify_title = !empty($callback['cod_notify_title']) ? $callback['cod_notify_title'] : esc_html__('Notify Me when back in stock', 'chat-on-desk');
        $notify_placeholder = !empty($callback['cod_notify_placeholder']) ? $callback['cod_notify_placeholder'] : esc_html__('Enter Number Here', 'chat-on-desk');
        $notify_button = !empty($callback['cod_notify_button']) ? $callback['cod_notify_button'] : esc_html__('Notify Me', 'chat-on-desk');
		$current_user_id = get_current_user_id();
        $phone  = ( get_user_meta($current_user_id, 'billing_phone', true) !== '' ) ? \ChatOnDesk\SmsAlertUtility::formatNumberForCountryCode(get_user_meta($current_user_id, 'billing_phone', true)) : '';
        $content = '  <section class="chatondesk_instock-subscribe-form ">
			<div class="panel panel-primary chatondesk_instock-panel-primary">
				<form class="panel-body">
					<div class="row">
						<fieldset class="chatondesk_instock_field">
							<div class="col-md-12 hide-success">
								<div class="panel-heading chatondesk_instock-panel-heading">
									<h4 class = "notify_title" style=""> '.esc_attr($notify_title).' </h4>
								</div>
								<div class="form-row">
									<input type="text" class="input-text phone-valid" id="cod_bis_phone" name="cod_bis_phone_phone" placeholder="'.esc_attr($notify_placeholder).'" value="'.esc_attr($phone).'">
								</div>
								<div class="form-group center-block" style="text-align:center;margin-top:10px">
									<button type="submit" id="cod_bis_submit" name="chatondesk_submit" class="button cod_bis_submit" style="width:100%">'.esc_attr($notify_button).'</button>
								</div>
							</div>						
						</fieldset>
						<div class="col-md-12">
							<div class="codstock_output"></div>
						</div>
					</div>
					<!-- End ROW -->
				</form>
			</div>
		</section>
				';
            
        return $content;
      
    }    
}
new CodPopup('chatondeskotp');