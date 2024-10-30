$cod  =jQuery;
$cod(document).on(
    "click", "#cod_bis_submit", function () {
        var self          = this;
        var waiting_txt  = (typeof cod_notices !=  'undefined' && cod_notices['waiting_txt']) ? cod_notices['waiting_txt'] : "Please wait...";
        $cod(self).val(waiting_txt).attr("disabled", "disabled");
    
        var phone_number = $cod("[name=cod_bis_phone_phone]:hidden").val()?$cod("[name=cod_bis_phone_phone]:hidden").val():$cod("[name=cod_bis_phone_phone]").val();
    
        if(cod_otp_settings['show_countrycode']=='off') {
            $cod(".cod_phone_error").remove();
            $cod(".phone-valid").after("<span class='error cod_phone_error' style='display:none'></span>");
        }
    
        if(phone_number == '') {
            $cod(".cod_phone_error").html("Please fill the number").fadeIn().css({"color":"red"});
            $cod("#cod_bis_submit").val("Notify Me").removeAttr("disabled",false);
            return false;
        }
    
        if($cod(self).is("input")) {
            $cod(self).val(waiting_txt).attr("disabled",true);
        }else{
            $cod(self).text(waiting_txt).attr("disabled",true);
        }
    
        var product_id      = $cod("#cod-product-id").val();
        var var_id          = $cod("#cod-variation-id").val();
        var data = {
            product_id: product_id,
            variation_id: var_id,
            user_phone: phone_number,
            action: "chatondeskbackinstock"
        };
        $cod.ajax(
            {
                type: "post",
                data: data,
                success: function (msg) {
                    var r= $cod.parseJSON(msg);
                    $cod(".chatondesk_instock-panel-primary fieldset").hide();
                    if(r.status == "success") {
                        $cod(".codstock_output").html(r.description).fadeIn().css({"color":"#fff", 'background-color':'green'});
                    }else{
                        $cod(".codstock_output").html(r.description).fadeIn().css({"color":"#fff",'background-color':'red'});
                    }
                    $cod(".codstock_output").css({'padding':'10px','border-radius':'4px','margin-bottom':'10px'});
                },
                error: function (request, status, error) {    }
            }
        );                            
        return false;
    }
);
$cod(".single_variation_wrap").on(
    "show_variation", function (event, variation) {
        $cod(".phone-valid").after("<span class='error cod_phone_error' style='display:none'></span>");
        // Fired when the user selects all the required dropdowns / attributes
        // and a final variation is selected / shown
        var vid = variation.variation_id;
        $cod(".chatondesk_instock-subscribe-form").hide(); //remove existing form
        $cod(".chatondesk_instock-subscribe-form-" + vid).fadeIn(
            1000,'linear',function () {
    
                if(cod_otp_settings['show_countrycode']=='on') {
                    var default_cc = (typeof cod_default_countrycode !='undefined' && cod_default_countrycode!='') ? cod_default_countrycode : '91';
                    $cod(this).find('.phone-valid').intlTelInput("destroy");
        
                    var parent_field_name = $cod(this).find('.phone-valid').attr("name");
                    var object = $cod(this).saIntellinput({hiddenInput:false});
        
        
                    var iti = $cod(this).find(".phone-valid").intlTelInput(object);
                    if(default_cc!='') {
                        var selected_cc = getCodCountryByCode(default_cc);
                        var show_default_cc = selected_cc[0].iso2.toUpperCase();
                        iti.intlTelInput("setCountry",show_default_cc);
                    }
        
        
        
        
        
                    $cod(this).parents("form").find(".iti--separate-dial-code").append('<input type="hidden" name="'+parent_field_name+'">');
        
        
                    iti.on(
                        'countrychange', function (e, countryData) {
                            var fullnumber =  $cod(this).intlTelInput("getNumber");
                            var field_name = $cod(this).attr('name');
                            $cod(this).intlTelInput("setNumber",fullnumber);
                            $cod(this).parents("form").find('[name="'+field_name+'"]:hidden').val(fullnumber);
            
                            if ($cod(this).intlTelInput('isValidNumber')) {
                                reset(this);
                                $cod(this).parents("form").find(".cod-otp-btn-init").attr("disabled",false);
                                $cod(this).parents("form").find("#sign_with_mob_btn").attr("disabled",false);
                            }
                            else
                            {    
                
                                var iti = $cod(this).intlTelInput("setNumber",fullnumber);
                                var errorCode = iti.intlTelInput('getValidationError');
                                iti.parents(".iti--separate-dial-code").next(".cod_phone_error").text(errorMap[errorCode]);
                                $cod("#chatondesk_otp_token_submit,#sc_cod_btn").attr("disabled",true);
                                iti.parents(".iti--separate-dial-code").next(".cod_phone_error").removeAttr("style");
                                iti.parents("form").find(".cod-otp-btn-init").attr("disabled",true);
                                iti.parents("form").find("#sign_with_mob_btn").attr("disabled",true);
                                $cod("#cod_bis_submit").attr("disabled",true);
                            }
            
            
            
                        }
                    );
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
                }
            }
        ); //add subscribe form to show
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