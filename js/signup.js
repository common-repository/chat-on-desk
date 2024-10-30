$cod  =jQuery;
$cod(
    function () {
        $cod('.woocommerce-address-fields [name=billing_phone]').on(
            "change", function (e) {
                if(chatondesk_mdet.update_otp_enable=='on') {
                    var new_phone = $cod('[name=billing_phone]:last-child').val();
                    var old_phone = $cod('#old_billing_phone').val();
                    if(new_phone != '' && new_phone != old_phone) {
                          $cod(this).parents('form').find('[id^="cod_verify_"]').removeClass("cod-default-btn-hide");
                          $cod('[name="save_address"]').addClass("cod-default-btn-hide");
                    }
                    else{
                        $cod('[name="save_address"]').removeClass("cod-default-btn-hide");
                        $cod(this).parents('form').find('[id^="cod_verify_"]').addClass("cod-default-btn-hide");
                    }
                }
            }
        );
        /* $cod('.cod-default-btn-hide[name="save_address"]').each(function(index) {
        $cod(this).removeClass('cod-default-btn-hide');
        $cod(this).parents('form').find('#cod_verify').addClass("cod-default-btn-hide");
        }); */
        
        $cod('input[id="reg_email"]').each(
            function (index) {
                //if(chatondesk_mdet.mail_accept==0)
                {
                     //$cod(this).closest(".form-required").removeClass("form-required").find(".description").remove();
                     //$cod(this).parent().hide();
                }
                /* else if(chatondesk_mdet.mail_accept==1){
                $cod(this).parent().children("label").html("Email");
                $cod(this).closest(".form-required").removeClass("form-required").find(".description").remove();
                } */
            }
        );
        var register = $cod("#chatondesk_name").closest(".register");
        register.find(".woocommerce-Button, button[name='register']").each(
            function () {
                if ($cod(this).attr("name") == "register") {
                    if (!$cod(this).text()!=chatondesk_mdet.signupwithotp) {
                        //$cod(this).val(chatondesk_mdet.signupwithotp);
                        //$cod(this).find('span').text(chatondesk_mdet.signupwithotp);
                    }
                }
            }
        );
    }
);
// login js
$cod(
    function ($) {
        function isEmpty(el)
        {
            return !$cod.trim(el)
        }
        var tokenCon;
        var akCallback = -1;
        var body = $cod("body");
        var modcontainer = $cod(".chatondesk-modal");
        var noanim = false;
        /* $.fn.chatondesk_login_modal = function($this) {
        show_chatondesk_login_modal($this);
        return false
        }; */
        $cod(document).on(
            "click", ".chatondesk-login-modal", function () {
                //$cod('.chatondesk-modal').show();
                // if (!$cod(this).attr("attr-disclick")) {
                show_chatondesk_login_modal($cod(this))
                // }
                return false
            }
        );
        function getUrlParams(url)
        {
            var params = {};
            url.substring(0).replace(
                /[?&]+([^=&]+)=([^&]*)/gi,
                function (str, key, value) {
                    params[key] = value;
                }
            );
            return params;
        }
    
        function show_chatondesk_login_modal($this)
        {
            //$cod(".u-column2").css("display",'none');
            var windowWidth = $cod(window).width();
            var params         = getUrlParams($this.attr("href"));
            var def         = params["default"];
            var showonly     = params["showonly"];
            var modal_id     = params["modal_id"];
        
            $cod("#"+modal_id+".chatondesk-modal").show();
        
            if (showonly == 'login,register' || showonly == 'register,login') {
        
                if(def == 'login') {
                     $cod("#"+modal_id+" .u-column2").css("display",'none');
                     $cod("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").css("display",'block');
                }
                else{
                    $cod("#"+modal_id+" .backtoLoginContainer, #"+modal_id+" .u-column2").css("display",'block');
                    $cod("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").css("display",'none');
                    //$cod("#"+modal_id+" #slide_form").css("transform","translateX(-373px)");
                }
            }
            else if ((def == 'register' && showonly=='') || showonly=='register') {
                $cod("#"+modal_id+" .u-column1,#"+modal_id+" .signdesc").css("display",'none');
                $cod("#"+modal_id+" .u-column2").css("display",'block');
                //$cod("#slide_form").css("transform","translateX(-373px)");
            }
            else if ((def == 'register' && showonly=='') || showonly=='register_with_otp') {
                $cod("#"+modal_id+" .u-column1,#"+modal_id+" .signdesc").css("display",'none');
                $cod("#"+modal_id+" .u-column2").css("display",'block');
                $cod("#"+modal_id+" .cod_myaccount_btn[name=cod_myaccount_btn_signup]").trigger("click");
                //$cod("#slide_form").css("transform","translateX(-373px)");
            }
            else if ((def == 'login' && showonly=='') || showonly=='login') {
                $cod("#"+modal_id+" .u-column1").css("display",'block');
                $cod("#"+modal_id+" .u-column2,#"+modal_id+" .signdesc").css("display",'none');
            }
        
            var display = $this.attr('data-display');
        
            $cod("#"+modal_id+".chatondesk-modal.chatondeskModal").removeClass("from-left from-right");
            $cod("#"+modal_id+".chatondesk-modal.chatondeskModal").addClass(display);
        
            if(display == 'from-right') {
                $cod("#"+modal_id+".from-right > .modal-content").animate(
                    {
                        right:'0',
                        opacity:'1',
                        padding: '15px'
                                                                       }, 
                    {
                        easing: 'swing',
                        duration: 200,
                        complete: function () { 
                            var wc_width = $cod("#"+modal_id+" .chatondesk_validate_field").width();
                            if($cod("#"+modal_id+" #slide_form .u-column1").length==0) {
                                $cod("#"+modal_id+" #slide_form .woocommerce").css({"width":wc_width});
                            }
                            else
                            {
                                $cod("#"+modal_id+" #slide_form .u-column1, #"+modal_id+" #slide_form .u-column2").css({"width":wc_width});
                            }
                        }
                             }
                );
            }
            if(display == 'from-left') {
                $cod("#"+modal_id+".from-left > .modal-content").animate(
                    {
                        left:'0',
                        opacity:'1',
                        padding: '15px'
                                                                       }, 
                    {
                        easing: 'swing',
                        duration: 200,
                        complete: function () { 
                            if($cod("#"+modal_id+" #slide_form .u-column1").length==0) {
                                var wc_width = $cod("#"+modal_id+" .chatondesk_validate_field").width();
                                $cod("#"+modal_id+" #slide_form .woocommerce").css({"width":wc_width});
                            }
                            else
                            {
                                $cod("#"+modal_id+" #slide_form .u-column1, #"+modal_id+" #slide_form .u-column2").css({"width":wc_width});
                            }
                        }
                             }
                );
            }
        
        
        
        
        
        
            /* modcontainer.css({
            display: "block"
            }); */
            return false
        }
    

        $cod(document).on(
            "click", ".chatondesk-modal .backtoLogin", function () {
                var modal_id = $cod(this).parents(".chatondesk-modal").attr("id");
                $cod("#"+modal_id+" .backtoLoginContainer").css("display",'none');
                $cod("#"+modal_id+" .signdesc").css("display",'block');
        
                //if($cod("#"+modal_id+".from-left #slide_form").length || $cod("#"+modal_id+".from-right #slide_form").length || $cod("#"+modal_id+".center #slide_form").length){
        
                if($cod("#"+modal_id+" #slide_form").length) {
            
        
                    $cod("#"+modal_id+" #slide_form").css("transform","translateX(0)");
                    $cod("#"+modal_id+" .u-column1, #"+modal_id+" .signdesc").show();
                }else{
                    $cod("#"+modal_id+" .u-column2").css("display",'none');
                    $cod("#"+modal_id+" .u-column1").css("display",'block');
                    $cod("#"+modal_id+" .signupbutton").css("display",'block');
                }
            }
        );
    
        $cod(document).on(
            "click", ".chatondesk-modal .signupbutton", function () {
    
                var modal_id = $cod(this).parents(".chatondesk-modal").attr("id");
                $cod("#"+modal_id+" .backtoLoginContainer").css("display",'block');
                $cod("#"+modal_id+" .signdesc").css("display",'none');
                //if($cod("#"+modal_id+".from-left #slide_form").length || $cod("#"+modal_id+".from-right #slide_form").length || $cod("#"+modal_id+".center #slide_form").length){
        
                //if($cod("#"+modal_id+" #slide_form").length){
                $cod("#"+modal_id+" .u-column2").show();
                $cod("#"+modal_id+" .u-column1").css("display",'none');
                //$cod("#"+modal_id+" #slide_form").css("transform","translateX(-373px)");
                //}else{
            
                //$cod("#"+modal_id+" .u-column2").css("display",'block');
                //$cod("#"+modal_id+" .u-column1").css("display",'none');
                //}
            }
        );
    }
);

/* $cod(document).on("click", ".chatondesk-login-modal", function(){
    
    var modal_id = $cod(this).attr('data-modal-id');
    var display = $cod(this).attr('data-display');
    
    $cod(".chatondesk-modal.chatondeskModal").removeClass("from-left from-right");
    $cod(".chatondesk-modal.chatondeskModal").addClass(display);
    if(display == 'from-right'){
        $cod(".from-right > .modal-content").animate({right:'0',opacity:'1'}, 100);
    }
    if(display == 'from-left'){
        $cod(".from-left > .modal-content").animate({left:'0',opacity:'1'}, 100);;
    }
}); */

$cod(document).on(
    "click",".from-right > .modal-content > .close,.from-left > .modal-content > .close",function () {
        $cod(".modal-content").removeAttr("style");
        $cod(".chatondesk-modal.chatondeskModal").hide('slow');
    }
);

$cod('body').click(
    function (e) {
        var container = $cod(".modal-content");
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            $cod('.chatondesk-modal > .modal-content > .close').trigger('click');
        }
    }
);