<div class="cod-accordion">
    <div class="accordion-section">    
    <?php $template = $templates['share_cart']; ?>
    <div class="cod-accordion-body-title">
             <input type="checkbox" name="<?php echo esc_attr($template['checkboxNameId']); ?>" id="<?php echo esc_attr($template['checkboxNameId']); ?>" class="notify_box" <?php echo ( 'on' === $template['enabled'] ) ? "checked='checked'" : ''; ?> <?php echo ( ! empty($template['chkbox_val']) ) ? "value='" . esc_attr($template['chkbox_val']) . "'" : ''; ?>  /><label><?php echo esc_html($template['title']); ?></label>
        </div>
    <div style="padding: 5px 10px 10px 10px;">    
            <table class="form-table">    
                <tr style="position: relative;">
                    <td colspan="2">
					<?php
					$params = array(
						'name'  => $template['textareaNameId'],
						'sms_text'  => $template['text-body'],
						'data_parent_id'  => $template['checkboxNameId'],
						'menu_id'  => 'menu_'.$checkTemplateFor.'_'.$template['status'],
						'token'     => $template['token'],
					);
					ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
					?> 
                    </td>
                </tr>                
                <tr class="top-border">
                <td class="td-heading">
                    <label><?php esc_html_e('Share button position', 'chat-on-desk')?></label>
                </td>
                <td>
                    <?php 
                        $share_btnpos = ChatOnDesk\chatondesk_get_option('share_btnpos', 'chatondesk_share_cart_general', 'after_cart_table');
                    ?>
                    <select class="min_width_200" name="chatondesk_share_cart_general[share_btnpos]" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>"  id="chatondesk_share_cart_general[share_btnpos]" tabindex="-1" aria-hidden="true">
                        <option value="before_cart_table" <?php if($share_btnpos == 'before_cart_table') { echo 'selected'; 
                                                          } ?>>Before Cart Table</option>
                        <option value="after_cart_table" <?php if($share_btnpos == 'after_cart_table') { echo 'selected'; 
                                                         } ?>>After Cart Table</option>
                        <option value="after_cart" <?php if($share_btnpos == 'after_cart') { echo 'selected'; 
                                                   } ?>>After Cart</option>
                        <option value="beside_update_cart" <?php if($share_btnpos == 'beside_update_cart') { echo 'selected'; 
                                                           } ?>>Beside Update Cart Button</option>
                    </select>    
                </td>
            </tr>
            <tr valign="top">
                <td class="td-heading">
                    <label><?php esc_html_e('Share cart button text', 'chat-on-desk') ?></label>
                </td>
                <td>
                    <input class="min_width_200" name="chatondesk_share_cart_general[share_btntext]" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>"  id="chatondesk_share_cart_general[share_btntext]" type="text" placeholder="Get Quote" value="<?php echo ChatOnDesk\chatondesk_get_option('share_btntext', 'chatondesk_share_cart_general') ? ChatOnDesk\chatondesk_get_option('share_btntext', 'chatondesk_share_cart_general') : 'Share cart'; ?>">
                </td>
            </tr>
			 <tr>
                <td class="td-heading">
                    <?php esc_html_e('Share Cart Style:', 'chat-on-desk'); ?>
                </td>
                <td>
                <?php
                $disabled = (! is_plugin_active('elementor/elementor.php')) ? "anchordisabled" : "";
				$post = get_page_by_path( 'cod_sharecart_style', OBJECT, 'chat-on-desk' ); 
                ?>              
                <a href= <?php get_admin_url() ?>"edit.php?post_name=cod_sharecart_style" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>" class="button <?php echo $disabled; ?> sharecart action" target="_blank" style="float:left;"><?php esc_html_e('Edit With Elementor', 'chat-on-desk'); ?></a>
                <?php if(!empty($post->post_type)){?>
                <a href="javascript:void(0)" data-parent_id="<?php echo esc_attr($template['checkboxNameId']); ?>" id="cod_btn_reset_style" temp-style="cod_sharecart_style" class="cod_btn_reset_style btn-outline" style="float:left;"><?php esc_html_e('Reset', 'chat-on-desk'); ?></a>
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
       </table>            
    </div>
    </div>
</div>
