<?php

/**
 * Groundhogg helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

if (defined('ABSPATH') === false) {
    exit;
}

if (is_plugin_active('groundhogg/groundhogg.php') === false) {
    return;
}
use Groundhogg\Preferences;

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Groundhogg CRM class
 */
class Groundhoggcrm extends \ChatOnDesk\FormInterface
{


    /**
     * Construct function.
     *
     * @return void
     */
    public function handleForm()
    {
        add_action('groundhogg/contact/preferences/updated', [$this, 'contactStatusChanged' ], 10, 4);
      
      
    }//end handleForm()


    /**
     * Add default settings to savesetting in setting-options.
     *
     * @param array $defaults defaults.
     *
     * @return array
     */
    public static function add_default_setting($defaults=[])
    {
        $bookingStatuses = Preferences::get_preference_names();
        foreach ($bookingStatuses as $ks => $vs) {
            $vs = str_replace(' ', '_', strtolower($vs));
            $defaults['chatondesk_gdh_general']['customer_gdh_notify_'.$vs]   = 'off';
            $defaults['chatondesk_gdh_message']['customer_sms_gdh_body_'.$vs] = '';
            $defaults['chatondesk_gdh_general']['admin_gdh_notify_'.$vs]      = 'off';
            $defaults['chatondesk_gdh_message']['admin_sms_gdh_body_'.$vs]    = '';
        }
        return $defaults;

    }//end add_default_setting()


    /**
     * Add tabs to smsalert settings at backend.
     *
     * @param array $tabs tabs.
     *
     * @return array
     */
    public static function addTabs($tabs=[])
    {
        $customerParam = [
            'checkTemplateFor' => 'gdh_customer',
            'templates'        => self::getCustomerTemplates(),
        ];

        $admin_param = [
            'checkTemplateFor' => 'gdh_admin',
            'templates'        => self::getAdminTemplates(),
        ];

        $tabs['groundhogg_crm']['nav']  = 'Groundhogg CRM';
        $tabs['groundhogg_crm']['icon'] = 'dashicons-id-alt';

        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_cust']['title']        = 'Customer Notifications';
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_cust']['tab_section']  = 'groundhoggcrmcusttemplates';
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_cust']['first_active'] = true;
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_cust']['tabContent']   = $customerParam;
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_cust']['filePath']     = 'views/message-template.php';

        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_admin']['title']       = 'Admin Notifications';
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_admin']['tab_section'] = 'groundhoggcrmadmintemplates';
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_admin']['tabContent']  = $admin_param;
        $tabs['groundhogg_crm']['inner_nav']['groundhogg_crm_admin']['filePath']    = 'views/message-template.php';
        $tabs['groundhogg_crm']['help_links'] = [
            /* 'youtube_link' => [
                'href'   => 'https://youtu.be/4BXd_XZt9zM',
                'target' => '_blank',
                'alt'    => 'Watch steps on Youtube',
                'class'  => 'btn-outline',
                'label'  => 'Youtube',
                'icon'   => '<span class="dashicons dashicons-video-alt3" style="font-size: 21px;"></span> ',

            ], */
            'kb_link'      => [
                'href'   => 'https://kb.smsalert.co.in/knowledgebase/groundhogcrm-sms-integration/',
                'target' => '_blank',
                'alt'    => 'Read how to integrate with groundhoggcrm',
                'class'  => 'btn-outline',
                'label'  => 'Documentation',
                'icon'   => '<span class="dashicons dashicons-format-aside"></span>',
            ],
        ];
        return $tabs;

    }//end addTabs()


    
    /**
     * Get customer templates.
     *
     * @return array
     */
    public static function getCustomerTemplates()
    {
        $bookingStatuses = Preferences::get_preference_names();
        $templates = [];
        foreach ($bookingStatuses as $ks  => $vs) {
            $title = $vs;
            $vs = str_replace(' ', '_', $vs);
            $currentVal = chatondesk_get_option('customer_gdh_notify_'.strtolower($vs), 'chatondesk_gdh_general', 'on');
            $checkboxNameId = 'chatondesk_gdh_general[customer_gdh_notify_'.strtolower($vs).']';
            $textareaNameId = 'chatondesk_gdh_message[customer_sms_gdh_body_'.strtolower($vs).']';

            $defaultTemplate = chatondesk_get_option('admin_sms_gdh_body_'.strtolower($vs), 'chatondesk_gdh_message', sprintf(__('Hello %1$s, status of your contact with %2$s has been changed to %3$s.%4$sPowered by%5$swww.chatondesk.com', 'chat-on-desk'), '[first_name]', '[store_name]', $title, PHP_EOL, PHP_EOL));

            $textBody = chatondesk_get_option('customer_sms_gdh_body_'.strtolower($vs), 'chatondesk_gdh_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When contact status changed to '.$title;
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $vs;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::getGroundhoggCrmvariables();
        }

        return $templates;

    }//end getCustomerTemplates()


    /**
     * Get admin templates.
     *
     * @return array
     */
    public static function getAdminTemplates()
    {
        $bookingStatuses = Preferences::get_preference_names();

        $templates = [];
        foreach ($bookingStatuses as $ks  => $vs) {
            $title = $vs;
            $vs = str_replace(' ', '_', $vs);
            $currentVal     = chatondesk_get_option('admin_gdh_notify_'.strtolower($vs), 'chatondesk_gdh_general', 'on');
            $checkboxNameId = 'chatondesk_gdh_general[admin_gdh_notify_'.strtolower($vs).']';
            $textareaNameId = 'chatondesk_gdh_message[admin_sms_gdh_body_'.strtolower($vs).']';

            $defaultTemplate = chatondesk_get_option('admin_sms_gdh_body_'.strtolower($vs), 'chatondesk_gdh_message', sprintf(__('Hello admin, status of your contact with %1$s has been changed to %2$s. %3$sPowered by%4$swww.chatondesk.com', 'chat-on-desk'), '[store_name]', $title, PHP_EOL, PHP_EOL));

            $textBody = chatondesk_get_option('admin_sms_gdh_body_'.strtolower($vs), 'chatondesk_gdh_message', $defaultTemplate);

            $templates[$ks]['title']          = 'When contact status changed to '.$title;
            $templates[$ks]['enabled']        = $currentVal;
            $templates[$ks]['status']         = $vs;
            $templates[$ks]['text-body']      = $textBody;
            $templates[$ks]['checkboxNameId'] = $checkboxNameId;
            $templates[$ks]['textareaNameId'] = $textareaNameId;
            $templates[$ks]['token']          = self::getGroundhoggCrmvariables();
        }

        return $templates;

    }//end getAdminTemplates()


    /**
     * Convert Optin Status.
     *
     * @param string $optinStatus optinStatus
     *
     * @return void
     */
    private function convert_optin_status($optinStatus)
    {
        $bookingStatuses = Preferences::get_preference_names();
        return str_replace(' ', '_', strtolower($bookingStatuses[$optinStatus]));

    }//end convert_optin_status()


    /**
     * Send sms subscription renew.
     *
     * @param array  $contact_id contact_id
     * @param int    $new_status new_status
     * @param string $old_status old_status
     * @param string $contact    contact
     *
     * @return void
     */
    public function contactStatusChanged($contact_id, $new_status, $old_status, $contact)
    {
        $status     = $this->convert_optin_status($contact->get_optin_status());
        $userPhone = $contact->get_mobile_number();
       
        $customerMessage  = chatondesk_get_option('customer_sms_gdh_body_'.$status, 'chatondesk_gdh_message', '');
        $customerRrNotify = chatondesk_get_option('customer_gdh_notify_'.$status, 'chatondesk_gdh_general', 'on');
       
        if ($customerRrNotify === 'on' && $customerMessage !== '') {
            $buyerMessage = $this->parseSmsBody($contact, $customerMessage);
            do_action('cod_send_sms', $userPhone, $buyerMessage);
        }

        // Send msg to admin.
        $adminPhoneNumber = chatondesk_get_option('sms_admin_phone', 'chatondesk_message', '');
        $nos = explode(',', $adminPhoneNumber);
        $adminPhoneNumber = array_diff($nos, ['postauthor', 'post_author']);
        $adminPhoneNumber = implode(',', $adminPhoneNumber);

        if (empty($adminPhoneNumber) === false) {
            $adminRrNotify = chatondesk_get_option('admin_gdh_notify_'.$status, 'chatondesk_gdh_general', 'on');
            $adminMessage  = chatondesk_get_option('admin_sms_gdh_body_'.$status, 'chatondesk_gdh_message', '');
            if ('on' === $adminRrNotify && '' !== $adminMessage) {
                $adminMessage = $this->parseSmsBody($contact, $adminMessage);
                do_action('cod_send_sms', $adminPhoneNumber, $adminMessage);
            }
        }

    }//end contactStatusChanged()


    /**
     * Parse sms body.
     *
     * @param array  $contact contact.
     * @param string $content content.
     *
     * @return string
     */
    public function parseSmsBody($contact, $content=null)
    {
            $firstName       = $contact->get_first_name();
            $lastName        = $contact->get_last_name();
            $fullName        = $contact->get_full_name();
            $email            = $contact->get_email();
            $optinStatus     = $this->convert_optin_status($contact->get_optin_status());
            $streetAddress_1 = !empty($address['street_address_1']) ? $address['street_address_1'] : '';
            $streetAddress_2 = !empty($address['street_address_2']) ? $address['street_address_2'] : '';
            $postalZip       = !empty($address['postal_zip']) ? $address['postal_zip '] : '';
            $city          = !empty($address['city']) ? $address['city'] : '';
            $country       = !empty($address['country']) ? $address['country'] : '';
            $primaryPhone = $contact->get_phone_number();
            $primaryPhoneExt = $contact->get_phone_extension();
            $mobilePhone      = $contact->get_mobile_number();
            $age           = $contact->get_age();
            $company       = $contact->get_company();
            $jobTitle     = $contact->get_job_title();
            $dateOfBirth = $contact->get_meta("birthday") ? $contact->get_meta("birthday") : '';

        $find = [
            '[first_name]',
            '[last_name]',
            '[full_name]',
            '[email]',
            '[optin_status]',
            '[street_address_1]',
            '[street_address_2]',
            '[postal_zip]',
            '[city]',
            '[country]',
            '[primary_phone]',
            '[primary_phone_ext]',
            '[mobile_phone]',
            '[age]',
            '[company]',
            '[job_title]',
            '[date_of_birth]',

        ];

        $replace = [
            $firstName,
            $lastName,
            $fullName,
            $email,
            $optinStatus,
            $streetAddress_1,
            $streetAddress_2,
            $postalZip,
            $city,
            $country,
            $primaryPhone,
            $primaryPhoneExt,
            $mobilePhone,
            $age,
            $company,
            $jobTitle,
            $dateOfBirth,

        ];

        $content = str_replace($find, $replace, $content);
        return $content;

    }//end parseSmsBody()


    /**
     * Get Groundhogg crm variables.
     *
     * @return array
     */
    public static function getGroundhoggCrmvariables()
    {
        $variable['[first_name]']       = 'First Name';
        $variable['[last_name]']        = 'Last Name';
        $variable['[full_name]']        = 'Full Name';
        $variable['[email]']            = 'Email';
        $variable['[optin_status]']     = 'Optin Status';
        $variable['[street_address_1]'] = 'Street Address_1';
        $variable['[street_address_2]'] = 'Street Address_2';
        $variable['[postal_zip]']       = 'Postal Zip';
        $variable['[city]']          = 'City';
        $variable['[country]']       = 'Country';
        $variable['[primary_phone]'] = 'Primary Phone';
        $variable['[primary_phone_ext]'] = 'Primary Phone Ext';
        $variable['[mobile_phone]']      = 'Mobile Phone';
        $variable['[age]']           = 'Age';
        $variable['[company]']       = 'Company';
        $variable['[job_title]']     = 'Job Title';
        $variable['[date_of_birth]'] = 'Date Of Birth';

        return $variable;

    }//end getGroundhoggCrmvariables()


    /**
     * Handle form for WordPress backend
     *
     * @return void
     */
    public function handleFormOptions()
    {
        if (is_plugin_active('groundhogg/groundhogg.php') === true) {
            add_filter('codDefaultSettings', __CLASS__.'::add_default_setting', 1);
            add_action('cod_addTabs', [$this, 'addTabs'], 10);
        }

    }//end handleFormOptions()


    /**
     * Check your otp setting is enabled or not.
     *
     * @return bool
     */
    public function isFormEnabled()
    {
        $userAuthorize = new chatondesk_Setting_Options();
        $islogged      = $userAuthorize->is_user_authorised();
        if ((is_plugin_active('groundhogg/groundhogg.php') === true) && ($islogged === true)) {
            return true;
        } else {
            return false;
        }

    }//end isFormEnabled()


    /**
     * Handle after failed verification
     *
     * @param object $userLogin   users object.
     * @param string $userEmail   user email.
     * @param string $phoneNumber phone number.
     *
     * @return void
     */
    public function handle_failed_verification($userLogin, $userEmail, $phoneNumber)
    {

    }//end handle_failed_verification()


    /**
     * Handle after post verification
     *
     * @param string $redirectTo  redirect url.
     * @param object $userLogin   user object.
     * @param string $userEmail   user email.
     * @param string $password    user password.
     * @param string $phoneNumber phone number.
     * @param string $extraData   extra hidden fields.
     *
     * @return void
     */
    public function handle_post_verification($redirectTo, $userLogin, $userEmail, $password, $phoneNumber, $extraData)
    {

    }//end handle_post_verification()


    /**
     * Clear otp session variable
     *
     * @return void
     */
    public function unsetOTPSessionVariables()
    {
    
    }//end unsetOTPSessionVariables()


    /**
     * Check current form submission is ajax or not
     *
     * @param bool $isAjax bool value for form type.
     *
     * @return bool
     */
    public function is_ajax_form_in_play($isAjax)
    {
            return $isAjax;
    }//end is_ajax_form_in_play()


}//end class
new Groundhoggcrm();
