<div class="cod-accordion">
    <div class="accordion-section">        
        <div class="cod-accordion-body-title" data-href="#accordion_Abandoned_cart_cust_0">
            <input type="checkbox" name="chatondesk_abandoned_cart[customer_notify]" id="chatondesk_abandoned_cart[customer_notify]" class="notify_box" <?php echo ( 'on' === $templates[0]['enabled'] ) ? "checked='checked'" : ''; ?> /><label><?php echo esc_html($templates[0]['title']); ?></label>
            <span class="expand_btn"></span>
        </div>
        <div id="accordion_Abandoned_cart_cust_0" class="cod-accordion-body-content">
            <?php
            $count = 0;
            $total_frequency = array();
            $enable_quiet_hours   = ChatOnDesk\chatondesk_get_option('enable_quiet_hours', 'chatondesk_abandoned_cart', '0');
            foreach ( $templates as $template ) {
                if($template['text-body'] == '') {
                    continue;
                }
                ?>
            <table class="form-table ab_cart_sche bottom-border" id="scheduler_<?php echo esc_attr($count); ?>">
                <tr valign="top">
                    <th>
                        <label><?php esc_html_e('Send sms to abandoned cart', 'chat-on-desk'); ?></label>
                    </th>
                    <td>
                <?php
                $hours = $template['frequency'];
                    
                array_push($total_frequency, $hours);
                if ('' === $hours ) {
                    $hours = 60;
                }
                ?>
                        <select id="<?php echo esc_attr($template['selectNameId']); ?>" name="<?php echo esc_attr($template['selectNameId']); ?>" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>" class="chatondesk_abandoned_cart_scheduler">
                            <option value='10' <?php selected($hours, 10); ?>><?php esc_html_e('After 10 minutes', 'chat-on-desk'); ?></option>
                            <option value='20' <?php selected($hours, 20); ?>><?php esc_html_e('After 20 minutes', 'chat-on-desk'); ?></option>
                            <option value='30' <?php selected($hours, 30); ?>><?php esc_html_e('After 30 minutes', 'chat-on-desk'); ?></option>
                            <option value='60' <?php selected($hours, 60); ?>><?php esc_html_e('After 1 hour', 'chat-on-desk'); ?></option>
                            <option value='120' <?php selected($hours, 120); ?>><?php esc_html_e('After 2 hours', 'chat-on-desk'); ?></option>
                            <option value='180' <?php selected($hours, 180); ?>><?php esc_html_e('After 3 hours', 'chat-on-desk'); ?></option>
                            <option value='240' <?php selected($hours, 240); ?>><?php esc_html_e('After 4 hours', 'chat-on-desk'); ?></option>
                            <option value='300' <?php selected($hours, 300); ?>><?php esc_html_e('After 5 hours', 'chat-on-desk'); ?></option>
                            <option value='360' <?php selected($hours, 360); ?>><?php esc_html_e('After 6 hours', 'chat-on-desk'); ?></option>
                            <option value='720' <?php selected($hours, 720); ?>><?php esc_html_e('After 12 hours', 'chat-on-desk'); ?></option>
                            <option value='1440' <?php selected($hours, 1440); ?>><?php esc_html_e('After 24 hours', 'chat-on-desk'); ?></option>
                            <option value='2880' <?php selected($hours, 2880); ?>><?php esc_html_e('After 48 hours', 'chat-on-desk'); ?></option>
                            <option value='0' <?php selected($hours, 0); ?>><?php esc_html_e('Disable notifications', 'chat-on-desk'); ?></option>
                        </select>                        
                        <a href="javascript:void(0)" class="cod-delete-btn alignright"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e('Remove', 'chat-on-desk'); ?></a>
                    </td>
                </tr>
                <tr valign="top">
                    <td colspan="2">
					<?php
					$params = array(
						'name'  => $template['textareaNameId'],
						'sms_text'  => $template['text-body'],
						'data_parent_id'  => $template['checkboxNameId'],
						'menu_id'  => 'menu_abandoned_cart'.$count,
						'token'     => $template['token'],
					);
					ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
					?> 
                    </td>
                </tr>
            </table>
                <?php $count++; 
            } ?>
            <div style="padding: 10px 0px 0px 10px;">
                <button class="button action" id="addNew" type="button" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>">
                <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e('Add New', 'chat-on-desk'); ?></button>
            </div>
        </div>
        <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">
             <tr>
                    <td class="td-heading">
                    <input id="enable_quiet_hours" type="checkbox" name="chatondesk_abandoned_cart[enable_quiet_hours]" value="1" <?php echo checked(1, $enable_quiet_hours, false); ?> data-parent_id="chatondesk_abandoned_cart[customer_notify]" />
                        <label for="enable_quiet_hours"><?php esc_html_e('Quiet Hours:', 'chat-on-desk'); ?><span class="tooltip" data-title="Quiet Hours"><span class="dashicons dashicons-info"></span></span></label>
                    </td>
                    <td>
                    <input type="time" data-parent_id="enable_quiet_hours" name="chatondesk_abandoned_cart[from_quiet_hours]" id="chatondesk_abandoned_cart[from_quiet_hours]" value="<?php echo esc_attr(ChatOnDesk\chatondesk_get_option('from_quiet_hours', 'chatondesk_abandoned_cart', '22:00')); ?>" >
                    </td>
                    <td>
                    <input type="time" data-parent_id="enable_quiet_hours" name="chatondesk_abandoned_cart[to_quiet_hours]" id="chatondesk_abandoned_cart[to_quiet_hours]" value="<?php echo esc_attr(ChatOnDesk\chatondesk_get_option('to_quiet_hours', 'chatondesk_abandoned_cart', '08:00')); ?>" >
                    </td>
                </tr>
                </table>
                </div>
        <?php
        $exit_intent_on   = ChatOnDesk\chatondesk_get_option('cart_exit_intent_status', 'chatondesk_abandoned_cart', '0');
        $test_mode_on     = ChatOnDesk\chatondesk_get_option('cart_exit_intent_test_mode', 'chatondesk_abandoned_cart', '0');
        ?>            
        <div class="cod-accordion-body-title">
            <input type="checkbox" id="chatondesk_abandoned_cart[cart_exit_intent_status]" name="chatondesk_abandoned_cart[cart_exit_intent_status]" data-parent_id="chatondesk_abandoned_cart[customer_notify]" class="notify_box" value="1" <?php echo checked(1, $exit_intent_on, false); ?> /><label><?php esc_html_e('Enable Exit Intent', 'chat-on-desk'); ?></label>
        </div>        
        <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">    
                <tr>
                    <th scope="row">
                        <?php esc_html_e('Exit Intent Style:', 'chat-on-desk'); ?>
                    </th>
                    <td>
                       <?php
                $disabled = (! is_plugin_active('elementor/elementor.php')) ? "anchordisabled" : "";
				$post = get_page_by_path( 'cod_exitintent_style', OBJECT, 'chat-on-desk' ); 
                ?>              
                <a href= <?php get_admin_url() ?>"edit.php?post_name=cod_exitintent_style" data-parent_id="chatondesk_abandoned_cart[cart_exit_intent_status]" class="button <?php echo $disabled; ?> action" target="_blank" style="float:left;"><?php esc_html_e('Edit With Elementor', 'chat-on-desk'); ?></a>
                <?php if(!empty($post->post_type)){?>
                <a href="javascript:void(0)" data-parent_id="chatondesk_abandoned_cart[cart_exit_intent_status]" id="cod_btn_reset_style" temp-style="cod_exitintent_style" class="cod_btn_reset_style btn-outline" style="float:left;"><?php esc_html_e('Reset', 'chat-on-desk'); ?></a>
                <?php
				}
				?>
				<span class="cod_reset_style"></span>	
			<?php
			if($disabled!='')
			{
            ?>		
            <span><?php esc_html_e('To edit, please install elementor plugin', 'chat-on-desk'); ?>	</span>
			<?php
			}
			?>
                    </td>
                </tr>
                <tr class="top-border">
                    <th scope="row">
                        <label for="cart-exit-intent-test-mode"><?php esc_html_e('Enable test mode:', 'chat-on-desk'); ?></label>
                    </th>
                    <td style="position: relative;">
                        <input id="chatondesk_abandoned_cart[cart-exit-intent-test-mode]" type="checkbox" name="chatondesk_abandoned_cart[cart_exit_intent_test_mode]" data-parent_id="chatondesk_abandoned_cart[cart_exit_intent_status]" value="1" <?php echo checked(1, $test_mode_on, false); ?> >    
                        <span style="top: 16px;" class="tooltip" data-title="<?php esc_html_e('If Enabled, go to your store and add a product to your shopping cart. Please note that only users with Admin rights will be able to see the Exit Intent and appearance limits have been removed - it will be shown each time you try to leave your shop.', 'chat-on-desk'); ?>"><span class="dashicons dashicons-info"></span></span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
<!-- /-cod-accordion -->
<div class="submit alignright">
    <a href="https://kb.smsalert.co.in/knowledgebase/abandoned-cart/" target="_blank" class="btn-outline" style="float:left;"><span class="dashicons dashicons-format-aside"></span> Documentation</a>
    <a href="https://youtu.be/YVfFnbug0HE" target="_blank" class="btn-outline" style="float:left;"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>
    <a href="admin.php?page=cod-ab-cart" class="button action"><?php esc_html_e('View List', 'chat-on-desk'); ?></a>
    <a href="admin.php?page=cod-ab-cart-reports" class="button action"><?php esc_html_e('View Reports', 'chat-on-desk'); ?></a>
</div>
<script>
jQuery( window ).load(function() {
    jQuery('#enable_exit_intent_custom_page').change(function () {
    if(jQuery(this).is(':checked'))
    {
        jQuery(".cart-exit-intent-colors,#cart-upload-image").addClass('anchordisabled');
    }
    else{
        jQuery(".cart-exit-intent-colors,#cart-upload-image").removeClass('anchordisabled');
    }
    });
    jQuery('#enable_exit_intent_custom_page').trigger('change');
    });
    jQuery("#select_custom_page").attr('data-parent_id','enable_exit_intent_custom_page');
    jQuery("#cart-upload-image").on("click", replaceExitIntentImage );
    jQuery("#cart-remove-image").on("click", removeExitIntentImage );
    jQuery("#addNew").on("click", addScheduler );
    function replaceExitIntentImage(e){
        e.preventDefault();
        var button = jQuery(this),
        custom_uploader = wp.media({
            title: 'Add custom Exit Intent image',
            library : {
                type : 'image'
            },
            button: {
                text: 'Use image'
            },
            multiple: false
        }).on('select', function(){ //It also has "open" and "close" events
            var attachment = custom_uploader.state().get('selection').first().toJSON();
            var image_url = attachment.url;
            if(typeof attachment.sizes.thumbnail !== "undefined"){ //Checking if the selected image has a thumbnail image size
                var thumbnail = attachment.sizes.thumbnail.url;
                image_url = thumbnail;
            }
            button.html('<img src="' + image_url + '">');
            jQuery('#cart_exit_intent_image').val(attachment.id);
            jQuery('#cart-remove-image').show();
        }).open();
    }

    function removeExitIntentImage(e){ //Removing Exit Intent image
        e.preventDefault();
        var button = jQuery(this).hide();
        jQuery('#cart_exit_intent_image').val('');
        jQuery('#cart-upload-image').html('<input type="button" class="button" value="Add custom image">');
    }    
    function addScheduler(){
        var last_scheduler_no = jQuery('#accordion_Abandoned_cart_cust_0').find('.form-table:last').attr("id").split('_')[1];        
        jQuery("#accordion_Abandoned_cart_cust_0 .form-table:last").clone().insertAfter("#accordion_Abandoned_cart_cust_0 .form-table:last");        
        var new_scheduler_no = +last_scheduler_no + 1;        
        jQuery('#accordion_Abandoned_cart_cust_0 .form-table:last').attr('id', 'scheduler_' + new_scheduler_no);        
        var scheduler_last = jQuery("#scheduler_"+new_scheduler_no).html().replace(  /\[cron\]\[\d+\]/g,  "[cron]["+new_scheduler_no+"]");        
        jQuery('#scheduler_'+new_scheduler_no).html(scheduler_last);
    }    
    jQuery(document).on('click',".cod-delete-btn",function(){
        var last_item     = (jQuery(".ab_cart_sche").length==1) ? true : false;
        if(last_item)
        {
            showAlertModal(alert_msg.last_item);
            return false;
        }
        else
        {
            jQuery(this).parents(".ab_cart_sche").remove();
        }
    });
    jQuery(document).ready(function(){
        var frequency_arr = <?php echo json_encode($total_frequency) ?>;
		var service = 'chatondesk';
        
        var frequency_sch = jQuery("."+service+"_abandoned_cart_scheduler").length;
        
        jQuery('.'+service+'_abandoned_cart_scheduler').each(function(index) {
            
            var selected_freq = jQuery("#scheduler_"+index+" ."+service+"_abandoned_cart_scheduler").find(":selected").val();
            
            jQuery.each(frequency_arr, function (i, elem) {                
                if( selected_freq != elem ){
                    jQuery("#scheduler_"+index+" option[value='"+elem+"']").attr("disabled", "disabled");
                }
            });
        });
    })
</script>
