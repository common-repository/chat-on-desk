(function ($) {

    $.fn.saIntel={
        initIntellinput:function (options) {
            var default_cc = (typeof cod_country_settings !='undefined' && cod_country_settings['cod_default_countrycode'] && cod_country_settings['cod_default_countrycode']!='') ? cod_country_settings['cod_default_countrycode'] : '';
                            
            var selected_countries             = (typeof cod_intl_warning !=  'undefined' && cod_intl_warning['whitelist_countries']) ? cod_intl_warning['whitelist_countries'] : new Array();
        
            var whitelist_countries = [];
        
            for(var c=0;c<selected_countries.length;c++)
            {
                var v = getCodCountryByCode(selected_countries[c]);
            
                whitelist_countries.push(v[0].iso2.toUpperCase());
            }
        
            var country= $("#billing_country").val();
        
        
            var default_opt = {
                "initialCountry": country,
                "separateDialCode": true,
                "nationalMode": true,
                "formatOnDisplay": false,
                "hiddenInput": "billing_phone",
                "utilsScript": "/utils.js?v=3.3.1",
                "onlyCountries": whitelist_countries
            };
            if(default_cc!='') {
                var object = $.extend({},default_opt, options);
            }
            else
            {
                var object = $.extend(
                    default_opt, {initialCountry: "auto",geoIpLookup: function (success, failure) {
                        $.get("https://ipapi.co/json/").always(
                            function (resp) {
                                var countryCode = (resp && resp.country) ? resp.country : "US";
                                success(countryCode);
                    
                            }
                        ).fail(
                            function () {
                                console.log("ip lookup is not working.");
                            }
                        );
                    }},options
                );
            }        
            return object;
        }
    };
    
    jQuery.fn.saIntellinput = $.fn.saIntel.initIntellinput;
}(jQuery)); 

jQuery(window).on(
    "load",function () {
        var $ = jQuery;
        var country= $("#billing_country").val();
    
        //var input = document.querySelector("#billing_phone, .phone-valid");
    
        var invalid_no         = (typeof cod_intl_warning  !=  'undefined' && cod_intl_warning['invalid_no']) ? cod_intl_warning ['invalid_no'] : "Invalid number";
        var invalid_country = (typeof cod_intl_warning  !=  'undefined' && cod_intl_warning['invalid_country']) ? cod_intl_warning['invalid_country'] : "Invalid country code";
        var ppvn             = (typeof cod_intl_warning !=  'undefined' && cod_intl_warning['ppvn']) ? cod_intl_warning['ppvn'] : "Please provide a valid Number";
    
        var errorMap = [invalid_no, invalid_country, ppvn, ppvn, invalid_no];
        $("#billing_phone").after("<p class='error cod_phone_error' style='display:none'></p>");
        $(document).find(".phone-valid").after("<span class='error cod_phone_error' style='display:none'></span>");

        var vars = {};
        var default_cc = (typeof cod_country_settings !='undefined' && cod_country_settings['cod_default_countrycode'] && cod_country_settings['cod_default_countrycode']!='') ? cod_country_settings['cod_default_countrycode'] : '';
        var enter_here = (typeof cod_notices !=  'undefined' && cod_notices['enter_here']) ? cod_notices['enter_here'] : "Enter Number Here";
    
        jQuery("#billing_phone, .phone-valid").each(
            function (i,item) {
                jQuery(item).attr('data-id','cod_intellinput_'+i)
                .attr("placeholder", enter_here)
                .intlTelInput("destroy");
                var field_name = jQuery(this).attr('name');
                var object = jQuery(this).saIntellinput({hiddenInput:false});
                vars['cod_intellinput_'+i] = jQuery(this).intlTelInput(object);
        
                var itis = vars['cod_intellinput_'+i];
        
                if(default_cc!='') {
                    var selected_cc = getCodCountryByCode(default_cc);
                    var show_default_cc = selected_cc[0].iso2.toUpperCase();
                    itis.intlTelInput("setCountry",show_default_cc);
                }
        
                jQuery(this).parents(".iti--separate-dial-code").append('<input type="hidden" name="'+field_name+'">');
        
                itis.on(
                    'countrychange', function (e, countryData) {
                        var fullnumber =  jQuery(this).intlTelInput("getNumber");
                        var field_name = jQuery(this).attr('name');
                        jQuery(this).parents("form").find('[name="'+field_name+'"]:hidden').val(fullnumber);
            
                        if (jQuery(this).intlTelInput('isValidNumber')) {
                            jQuery(this).intlTelInput("setNumber",fullnumber);
                            reset(this);
                            jQuery(this).parents("form").find("button, input[type=submit], input[type=button]").attr("disabled",false);
                            jQuery(this).parents("form").find(".chatondesk_otp_btn_submit").css("cursor","pointer");
                            jQuery(this).parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",false);
                        }
                        else
                        {    
                
                            var iti = jQuery(this);
                            if(iti.val()!='') {
                                var errorCode = iti.intlTelInput('getValidationError');
                                iti.parents(".iti--separate-dial-code").next(".cod_phone_error").text(errorMap[errorCode]);
                                jQuery("#chatondesk_otp_token_submit,#sc_cod_btn").attr("disabled",true);
                                iti.parents(".iti--separate-dial-code").next(".cod_phone_error").removeAttr("style");
                                iti.parents("form").find(".chatondesk_otp_btn_submit").css("cursor","not-allowed");
                                iti.parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",true);
                                jQuery("#cod_bis_submit").attr("disabled",true);
                            }
                        }
                    }
                );
            }
        );    
    
        //get all country data        
        function getCodCountryByCode(code)
        {
            return window.intlTelInputGlobals.getCountryData().filter(
                function (data) {
                    return (data.dialCode == code) ? data.iso2 : ''; }
            );
        }

        jQuery('#billing_country').change(
            function () {
                var iti = vars[jQuery("#billing_phone").attr('data-id')];
                iti.intlTelInput("setCountry",$(this).val());
                onChangeCheckValidno(document.querySelector("#billing_phone"));
            }
        );

        var reset = function (obj) {
              // jQuery(".cod_phone_error").text("");
              jQuery(obj).parents("form").find(".cod_phone_error").hide();
        
        };    

        function onChangeCheckValidno(obj)
        {
            reset(obj);
            var input     = obj;
            //var iti     = vars[jQuery(obj).attr('data-id')]; // 04/01/2020
            var iti     = jQuery(obj);
            if (input.value.trim()) {
                if (iti.intlTelInput('isValidNumber')) {
                     jQuery("#chatondesk_otp_token_submit,#sc_cod_btn").attr("disabled",false);
                     jQuery("#cod_bis_submit").attr("disabled",false);
                     //iti.parents("form").find(".cod-otp-btn-init").attr("disabled",false);
                     iti.parents("form").find("button, input[type=submit], input[type=button]").attr("disabled",false);
                     iti.parents("form").find(".chatondesk_otp_btn_submit").css("cursor","pointer");
                     iti.parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",false);

                } else{
                    var errorCode = iti.intlTelInput('getValidationError');
                    //input.focus();
                    iti.parents(".iti--separate-dial-code").next(".cod_phone_error").text(errorMap[errorCode]);
                    jQuery("#chatondesk_otp_token_submit,#sc_cod_btn").attr("disabled",true);
                    iti.parents(".iti--separate-dial-code").next(".cod_phone_error").removeAttr("style");
                    // iti.parents("form").find(".cod-otp-btn-init").attr("disabled",true);
                    //iti.parents("form").find("button, input[type=submit], input[type=button]").attr("disabled",true);
                    iti.parents("form").find(".chatondesk_otp_btn_submit").css("cursor","not-allowed");
                    iti.parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",true);
                    jQuery("#cod_bis_submit").attr("disabled",true);
                }
            
            }
        }

        jQuery(document).on(
            "blur","#billing_phone, .phone-valid",function () {
                onChangeCheckValidno(this);
            }
        );
        
        jQuery(".phone-valid,#billing_phone").keyup(
            function () {
                setPhoneNumber(this);
            }
        );
        jQuery(document).on(
            "keyup","#billing_phone, .phone-valid",function () {
                setPhoneNumber(this);
            }
        );
    
        function setPhoneNumber(obj)
        {
            var fullnumber =  jQuery(obj).intlTelInput("getNumber");
            //get number with std code
            if(typeof(fullnumber)!='object' && fullnumber!='') {
                var field_name = jQuery(obj).attr('name');
                jQuery(obj).intlTelInput("setNumber",fullnumber);
                //jQuery(this).next('[name=billing_phone]').val(fullnumber);
        
                jQuery(obj).parents("form").find('[name="'+field_name+'"]:hidden').val(fullnumber);
            }
        
            if (jQuery(obj).intlTelInput('isValidNumber')) {
                jQuery('#billing_phone_field .fl-wrap-input').addClass('fl-is-active');
                reset(obj);
                // jQuery(this).parents("form").find(".cod-otp-btn-init").attr("disabled",false);
                jQuery(obj).parents("form").find("button, input[type=submit], input[type=button]").attr("disabled",false);
                jQuery(obj).parents("form").find(".chatondesk_otp_btn_submit").css("cursor","pointer");
                jQuery(obj).parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",false);
            }
            else{
                var iti     = jQuery(obj);
                var errorCode = iti.intlTelInput('getValidationError');
                if(iti.val()!='') {
                    jQuery('#billing_phone_field .fl-wrap-input').addClass('fl-is-active');
                    iti.parents(".iti--separate-dial-code").next(".cod_phone_error").text(errorMap[errorCode]);
                    jQuery("#chatondesk_otp_token_submit,#sc_cod_btn").attr("disabled",true);
                    iti.parents(".iti--separate-dial-code").next(".cod_phone_error").removeAttr("style");
                    // iti.parents("form").find(".cod-otp-btn-init").attr("disabled",true);
                    //iti.parents("form").find("button, input[type=submit], input[type=button]").attr("disabled",true);
                    iti.parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",true);
                    jQuery("#cod_bis_submit").attr("disabled",true);    
                }
                else{
                    jQuery('#billing_phone_field .fl-wrap-input').removeClass('fl-is-active');
                }
                   iti.parents("form").find(".chatondesk_otp_btn_submit").attr("disabled",true).css("cursor","not-allowed");                
            }    
        }
    
        jQuery(".phone-valid,#billing_phone").trigger('keyup');

        // on keyup / change flag: reset
        jQuery("#billing_phone").change(
            function () {
    
                var iti     = jQuery(this);
                if (iti.intlTelInput('isValidNumber')) {
                    reset(this);
                }
            }
        );
    }
);