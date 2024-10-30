<?php
/**
 * Constants helper.
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
 * SmsAlertConstants class
 */
class SmsAlertConstants
{

    const SUCCESS                = 'SUCCESS';
    const FAILURE                = 'FAILURE';
    const TEXT_DOMAIN            = 'chat-on-desk';
    const PATTERN_PHONE          = '/^(\+)?(country_code)?0?\d+$/'; // '/^\d{10}$/';//'/\d{10}$/';
    const ERROR_JSON_TYPE        = 'error';
    const SUCCESS_JSON_TYPE      = 'success';
    const USERPRO_VER_FIELD_META = 'verification_form';
    const SA_VERSION             = '1.0.1';
    
    
    
    /**
     * Construct function.
     * 
     * @return string
     */
    function __construct()
    {
        $this->defineGlobal();
    }

    /**
     * Get Phone Pattern.
     * 
     * @return string
     */
    public static function getPhonePattern()
    {
        $country_code      = chatondesk_get_option('default_country_code', 'chatondesk_general');
        $cod_mobile_pattern = chatondesk_get_option('cod_mobile_pattern', 'chatondesk_general', '/^(\+)?(country_code)?0?\d{10}$/');
        $pattern           = ( '' !== $cod_mobile_pattern ) ? $cod_mobile_pattern : self::PATTERN_PHONE;
        $country_code      = str_replace('+', '', $country_code);
        $pattern_phone     = str_replace('country_code', $country_code, $pattern);
        return $pattern_phone;
    }

    /**
     * Define global function.
     * 
     * @return void
     */
    function defineGlobal()
    {
        global $phoneCodLogic;
        $phoneCodLogic = new \ChatOnDesk\PhoneLogic();
        define('COD_MOV_DIR', plugin_dir_path(dirname(__FILE__)));
        define('COD_MOV_URL', plugin_dir_url(dirname(__FILE__)));
        define('COD_MOV_CSS_URL', COD_MOV_URL . 'css/sms_alert_customer_validation_style.css');
        define('COD_MOV_LOADER_URL', COD_MOV_URL . 'images/ajax-loader.gif');
    }
}
new \ChatOnDesk\SmsAlertConstants();
