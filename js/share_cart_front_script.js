$cod  =jQuery;
$cod(document).ready(
    function () {
        $cod('body').on(
            'click','#chatondesk_share_cart',function (e) {    
                e.preventDefault();
                $cod(this).addClass('button--loading');
                $cod.ajax(
                    {
                        url:ajax_url.ajaxurl,
                        type:'POST',
                        data:'action=cod_check_cart_data',
                        success : function (response) {
                            if(response === '0') {
                                $cod('#chatondesk_scp_ul').addClass('woocommerce-error').css({"padding":"1em 1.618em"});
                                $cod('#chatondesk_scp_ul').html('<li>Sorry, You cannot share your cart, Your cart is empty</li>');
                            }
                            $cod('body').addClass("chatondesk_sharecart_popup_body");
                            $cod("#chatondesk_sharecart_popup").css("display","block");
                            $cod('#chatondesk_share_cart').removeClass('button--loading');
                            $cod('#sc_cod_umobile').trigger('keyup');
                        },
                        error: function () {
                            alert('Error occured');
                        }
                    }
                );
                return false;
            }
        );

        $cod(document).on(
            'click','.close',function () {
                var modal_style = $cod('.chatondeskModal').attr('data-modal-close');
                $cod('.chatondeskModal').addClass(modal_style+'Out');
                $cod("#chatondesk_sharecart_popup").css("display","none");
                $cod('body').removeClass("chatondesk_sharecart_popup_body");
                setTimeout(
                    function () {
                        $cod('.chatondeskModal').removeClass(modal_style+'Out');
                    }, 500
                );
                $cod('#chatondesk_scp_ul').removeClass('woocommerce-error').css({"padding":"0"});
            }
        );

        $cod('body').on(
            'click','#sc_cod_btn',function (e) {
                e.preventDefault();
                $cod('#sc_cod_btn').attr("disabled",true);
                var uname     = $cod("#sc_cod_uname").val();
                var umobile = $cod("#sc_cod_umobile").val();
                var fname     = $cod("#sc_cod_fname").val();
                var fmobile = $cod("#sc_cod_fmobile").val();
                var intRegex = /^\d+$/;
        
                if((!intRegex.test(umobile) && umobile != '') || (!intRegex.test(fmobile) && fmobile != '')) {
                    $cod('#sc_cod_btn').before('<li class="sc_error" style="color:red">*Invalid Mobile Number</li>');
                    setTimeout(
                        function () {
                            $cod('.sc_error').remove();
                        }, 2000
                    );
                    $cod('#sc_cod_btn').attr("disabled",false);
                    return false;
                }
        
                if(uname != '' && umobile != '' && fname != '' && fmobile != '') {
                    $cod(this).addClass('button--loading');
                    var formdata = $cod(".sc_cod_form").serialize();
                    if(formdata.search("sc_cod_uname") == -1) {
                            formdata = formdata+'&sc_cod_uname='+encodeURI(uname);
                    }
                    $cod.ajax(
                        {
                            url:ajax_url.ajaxurl,
                            type:'POST',
                            data:'action=cod_save_cart_data&'+formdata,
                            success : function (response) {
                                $cod('#sc_cod_btn').removeClass('button--loading');
                                $cod('.sc_cod_form').hide();
                                $cod('#sc_cod_response').html(response);
                                setTimeout(
                                    function () {
                                              $cod("#chatondesk_sharecart_popup").css("display","none"); 
                                              $cod('body').removeClass("chatondesk_sharecart_popup_body");
                                              $cod('.sc_cod_form').show();
                                              $cod('#sc_cod_response').html('');
                                    }, 2000
                                );
                            },
                            error: function (errorMessage) {
                                $cod('#sc_cod_btn').removeClass('button--loading');
                                alert('Error occured');
                            }
                        }
                    );
                }
                else {
                    $cod('#sc_cod_btn').attr("disabled",false);
                    $cod('#sc_cod_btn').before('<li class="sc_error" style="color:red">*Please fill all fields</li>');
                    setTimeout(
                        function () {
                            $cod('.sc_error').remove();
                        }, 2000
                    );
                }
                return false;
            }
        );
    }
);