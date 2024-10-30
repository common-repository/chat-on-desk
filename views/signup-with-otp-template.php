<div class="cod-accordion" style="padding: 0px 10px 10px 10px;"><div class="accordion-section">
    <?php
        $shortcodes = array(
            array(
                'label' => __('Signup With Mobile', 'chat-on-desk'),
                'value' => 'cod_signupwithmobile',
            ),
            array(
                'label' => __('Login With Otp', 'chat-on-desk'),
                'value' => 'cod_loginwithotp',
            ), 
            array(
                'label' => __('Share Cart', 'chat-on-desk'),
                'value' => 'cod_sharecart',
            ),
            array(
                'label' => __('Verify OTP', 'chat-on-desk'),
                'value' => 'cod_verify phone_selector="#phone" submit_selector= ".btn"',
            )
        );

        foreach ( $shortcodes as $key => $shortcode ) {

            echo '<table class="form-table">';
            $id = 'chatondesk_' . esc_attr($shortcode['value']) . '_short';
            ?>
            <tr class="top-border">
                <th scope="row">
                    <label for="<?php echo esc_attr($id); ?>"><?php echo esc_attr($shortcode['label']); ?> </label>
                </th>
                <td>
                    <div>
                        <input type="text" class="cod-shortcode-input" value="[<?php echo esc_attr($shortcode['value']); ?>]" readonly/>    <span class="dashicons dashicons-admin-page copy_shortcode" onclick="copyToClipboard('[<?php echo esc_attr($shortcode['value']); ?>]',this)" style="
                            margin-left: -25px;  cursor: pointer;"></span>
                        <span class="clip-msg" style="color:#da4722; margin-left: 1.5pc;"></span>
                        <?php 
                        if ('cod_verify phone_selector="#phone" submit_selector= ".btn"'===$shortcode['value']) {
                            ?>
                        <!--optional attribute-->
                        <br/><br/>
                        <b><?php esc_html_e('Attributes', 'chat-on-desk'); ?></b><br />
                        <ul>
                        <li><b>phone_selector</b> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; - <?php esc_html_e('set phone field selector', 'chat-on-desk'); ?></li>
                        <li><b>submit_selector</b> &nbsp;&nbsp;&nbsp;&nbsp; - <?php esc_html_e('set submit button selector.', 'chat-on-desk'); ?></li>
                        </ul>
                        <b>eg</b> : <code>[cod_verify phone_selector="#phone" submit_selector= ".btn"]</code></span>
                    <!--/-optional attribute-->
                            <?php
                        }
                        ?>
                    </div>
                </td>
            </tr>
    </table>   
        <?php } ?>
    </div>
</div>
<script>
jQuery(document).ready(function(){
jQuery("#user_group").trigger('change');
});
jQuery("#user_group").change(function() {
        var grp_name = jQuery(this).val();
        jQuery(this).next().val('[cod_subscribe group_name="'+grp_name+'"]');
        jQuery(this).parent().find('.copy_shortcode').attr('onclick',"copyToClipboard('[cod_subscribe group_name=\""+grp_name+"\"]',this)")
});
function copyToClipboard(val,element) {
  var temp = jQuery("<input>");
  jQuery("body").append(temp);
  temp.val(val).select();
  document.execCommand("copy");
  temp.remove();
  jQuery(element).next(".clip-msg").text("Copied to Clipboard").fadeIn().fadeOut();
}
</script>
