<?php
/**
 * Shortcode helper.
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
    
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Shortcode class
 */
class Shortcode
{

    /**
     * Construct function.
     *
     * @return string
     */
    public function __construct()
    {
        $user_authorize = new chatondesk_Setting_Options();
        if ($user_authorize->is_user_authorised()) {           
            add_shortcode('cod_loginwithotp', array( $this, 'addSaLoginwithotp' ), 100);
            add_shortcode('cod_signupwithmobile', array( $this, 'addSaSignupwithmobile' ), 100);
            add_action('wp_ajax_save_subscribe', array( $this, 'saveSubscribeData' ));
            add_action('wp_ajax_nopriv_save_subscribe', array( $this, 'saveSubscribeData' ));
			add_action('cod_addTabs', array( $this, 'addTabs' ), 100);
        }
    }
	
	/**
     * Add tabs to smsalert settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs( $tabs = array() )
    {
        $tabs['signupwithotp']['nav'] = 'Shortcodes';
        $tabs['signupwithotp']['icon'] = 'dashicons-admin-users';
        $tabs['signupwithotp']['inner_nav']['signup']['title']       = 'Shortcodes';
        $tabs['signupwithotp']['inner_nav']['signup']['tab_section'] = 'signup_with_phone';
        $tabs['signupwithotp']['inner_nav']['signup']['first_active'] = true;
        $tabs['signupwithotp']['inner_nav']['signup']['tabContent']  = array();
        $tabs['signupwithotp']['inner_nav']['signup']['filePath']    = 'views/signup-with-otp-template.php'; 
        $tabs['signupwithotp']['help_links']                        = array(
        'youtube_link' => array(
        'href'   => 'https://youtu.be/mJ6IEFmmXhI',
        'target' => '_blank',
        'alt'    => 'Watch steps on Youtube',
        'class'  => 'btn-outline',
        'label'  => 'Youtube',
        'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

        ),
        'kb_link'      => array(
        'href'   => 'https://kb.smsalert.co.in/knowledgebase/chat-on-desk-shortcodes/',
        'target' => '_blank',
        'alt'    => 'Read how to use smsalert shortcodes',
        'class'  => 'btn-outline',
        'label'  => 'Documentation',
        'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
        ),
        );
        return $tabs;
    }
    
    /**
     * Save subscribe function.
     *
     * @return string
     */
    function saveSubscribeData()
    {
        $grp_name = $_POST['grp_name'];
        $datas[] = array('person_name'=>$_POST['name'],'number'=>$_POST['mobile']);
           $response = Chatondesk::createContact($datas, $grp_name);
        $response = json_decode($response, true);
        if ($response['status']=='success') {
            echo "<div class='codstock_output' style='color: rgb(255, 255, 255); background-color: green; padding: 10px; border-radius: 4px; margin-bottom: 10px;'>You have subscribed successfully.</div>";
        } else {
            $error = !is_array($response['description'])?$response['description']:$response['description']['desc'];
            echo '<div class="codstock_output" style="color: rgb(255, 255, 255); background-color: red; padding: 10px; border-radius: 4px; margin-bottom: 10px;">'.$error.'</div>';
        }
        die();
    }

    /**
     * Loginwithotp function.
     *
     * @return string
     */
    public function addSaLoginwithotp()
    {
        $enabled_login_with_otp = chatondesk_get_option('login_with_otp', 'chatondesk_general');
        $unique_class    = 'cod-lwo-'.mt_rand(1, 100);
        if (('on' !== $enabled_login_with_otp) || (is_user_logged_in() && !current_user_can('administrator')) ) {
            return;
        }    
        ob_start();
        global $wp;
        echo '<form class="cod-lwo-form cod_loginwithotp-form '.$unique_class.'" method="post" action="' . home_url($wp->request) . '/?option=chatondesk_verify_login_with_otp">';
        get_chatondesk_template('template/login-with-otp-form.php', array());
        echo wp_nonce_field('chatondesk_wp_loginwithotp_nonce', 'chatondesk_loginwithotp_nonce', true, false);
        echo '</form><style>.cod_default_login_form{display:none;}</style>';
        echo do_shortcode('[cod_verify phone_selector=".cod_mobileno" submit_selector= ".'.$unique_class.' .chatondesk_login_with_otp_btn"]');
        ?>
        <script>
        setTimeout(function() {
            if (jQuery(".modal.chatondeskModal").length==0) {            
            var popup = '<?php echo str_replace(array("\n","\r","\r\n"), '', (get_chatondesk_template("template/otp-popup.php", array(), true))); ?>';
            jQuery('body').append(popup);
            }
        }, 200);
        </script>
        <?php	
        $content = ob_get_clean();
        return $content;
    }
    
    /**
     * Signupwithmobile function.
     *
     * @return string
     */
    public function addSaSignupwithmobile()
    {
        $enabled_signup_with_mobile = chatondesk_get_option('signup_with_mobile', 'chatondesk_general');
        $unique_class    = 'cod-swm-'.mt_rand(1, 100);
        if (('on' !== $enabled_signup_with_mobile) || (is_user_logged_in() && !current_user_can('administrator')) ) {
            return;
        }    
        ob_start();
        global $wp;
        echo '<form class="cod-lwo-form cod-signupwithotp-form '.$unique_class.'" method="post" action="' . home_url($wp->request) . '/?option=codsignwthmob">';
        get_chatondesk_template('template/sign-with-mobile-form.php', array());
        echo wp_nonce_field('chatondesk_wp_signupwithmobile_nonce', 'chatondesk_signupwithmobile_nonce', true, false);
        echo '</form><style>.cod_default_signup_form{display:none;}</style>';
        echo do_shortcode('[cod_verify phone_selector="#reg_with_mob" submit_selector= ".'.$unique_class.' .chatondesk_reg_with_otp_btn"]');
        ?>
        <script>
        setTimeout(function() {
            if(jQuery(".modal.chatondeskModal").length==0)    
            {            
            var popup = '<?php echo str_replace(array("\n","\r","\r\n"), '', (get_chatondesk_template("template/otp-popup.php", array(), true))); ?>';
            jQuery('body').append(popup);
            }
        }, 200);
        </script>
        <?php	    
        $content = ob_get_clean();
        return $content;
    }

}
new Shortcode();
?>
