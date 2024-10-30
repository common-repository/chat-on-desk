$cod  =jQuery;
$cod(document).ready(
    function ($) {

        // if device is mobile.
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
              $cod('body').addClass('mobile-device');
        }

        var deactivate_url = '';

        // Add Deactivation id to all deactivation links.
        embed_id_to_deactivation_urls();

        // On click of deactivate.
        if('plugins.php' == codf.current_screen ) {

            add_deactivate_slugs_callback(codf.current_supported_slug);

            $cod(document).on(
                'change','.on-boarding-radio-field' ,function (e) {

                    e.preventDefault();
                    if ('other' == $cod(this).attr('id') ) {
                        $cod('#deactivation-reason-text').removeClass('codf-keep-hidden');
                    } else {
                        $cod('#deactivation-reason-text').addClass('codf-keep-hidden');
                    }
                }
            );
        }

        // Close Button Click.
        $cod(document).on(
            'click','.codf-on-boarding-close-btn a',function (e) {
                e.preventDefault();
                codf_hide_onboard_popup();
            }
        );

        // Skip and deactivate.
        $cod(document).on(
            'click','.codf-deactivation-no_thanks',function (e) {

                window.location.replace(deactivate_url);
                codf_hide_onboard_popup();
            }
        );

        // Submitting Form.
        $cod(document).on(
            'submit','form.codf-on-boarding-form',function (e) {

                $cod('.codf-on-boarding-submit').addClass('button--loading').attr('disabled',true);
                e.preventDefault();
                var form_data = $cod('form.codf-on-boarding-form').serializeArray(); 

                $cod.ajax(
                    {
                        type: 'post',
                        dataType: 'json',
                        url: codf.ajaxurl,
                        data: {
                            nonce : codf.auth_nonce, 
                            action: 'cod_send_onboarding_data' ,
                            form_data: form_data,  
                        },
                        success: function ( msg ) {
                            $cod(document).find('#codf_wgm_loader').hide();
                            if('plugins.php' == codf.current_screen ) {
                                window.location.replace(deactivate_url);
                            }
                            codf_hide_onboard_popup();
                            $cod('.codf-on-boarding-submit').removeClass('button--loading').attr('disabled',false);
                        }
                    }
                );
            }
        );

        // Open Popup.
        function codf_show_onboard_popup()
        {
              $cod('.codf-onboarding-section').show();
              $cod('.codf-on-boarding-wrapper-background').addClass('onboard-popup-show');

            if(! $cod('body').hasClass('mobile-device') ) {
                $cod('body').addClass('codf-on-boarding-wrapper-control');
            }
        }

        // Close Popup.
        function codf_hide_onboard_popup()
        {
            $cod('.codf-on-boarding-wrapper-background').removeClass('onboard-popup-show');
            $cod('.codf-onboarding-section').hide();
            if(! $cod('body').hasClass('mobile-device') ) {
                $cod('body').removeClass('codf-on-boarding-wrapper-control');
            }
        }

        // Apply deactivate in all the codf plugins.
        function add_deactivate_slugs_callback( all_slugs )
        {
        debugger;
            for ( var i = all_slugs.length - 1; i >= 0; i-- ) {

                $cod(document).on(
                    'click', '#deactivate-' + all_slugs[i] ,function (e) {
                        e.preventDefault();
                        deactivate_url = $cod(this).attr('href');
                        plugin_name = $cod(this).attr('aria-label');
                        $cod('#plugin-name').val(plugin_name.replace('Deactivate ', ''));
                        plugin_name = plugin_name.replace('Deactivate ', '');
                        $cod('#plugin-name').val(plugin_name);
                        $cod('.codf-on-boarding-heading').text(plugin_name + ' Feedback');
                        var placeholder = $cod('#deactivation-reason-text').attr('placeholder');
                        $cod('#deactivation-reason-text').attr('placeholder', placeholder.replace('{plugin-name}', plugin_name));
                        codf_show_onboard_popup();
                    }
                );
            }
        }

        // Add deactivate id in all the plugins links.
        function embed_id_to_deactivation_urls()
        {
            $cod('a').each(
                function () {
                    if ('Deactivate' == $cod(this).text() && 0 < $cod(this).attr('href').search('action=deactivate') ) {
                        if('undefined' == typeof $cod(this).attr('id') ) {
                            var slug = $cod(this).closest('tr').attr('data-slug');
                            $cod(this).attr('id', 'deactivate-' + slug);
                        }
                    }
                }
            );    
        }
    }
);