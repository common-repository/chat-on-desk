<?php
/**
 * Template.
  * PHP version 5
 *
 * @category View
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */
 namespace ChatOnDesk;
$admin_message = ( isset($values['admin_message']) ) ? trim($values['admin_message']) : SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_ADMIN_MESSAGE');
$visitor_msg = ( isset($values['visitor_message']) ) ? $values['visitor_message'] :SmsAlertMessages::showMessage('DEFAULT_CONTACT_FORM_CUSTOMER_MESSAGE');
$results = Formidable::getFormFields($values['id']);
$enable_otp = isset($values['chatondesk_enable_otp'])?$values['chatondesk_enable_otp']:'';
$enable_message = isset($values['chatondesk_enable_message'])?$values['chatondesk_enable_message']:'';
$admin_number = isset($values['admin_number'])?$values['admin_number']:'';
$visitor_phone = isset($values['visitor_phone'])?$values['visitor_phone']:'';
?>
<div class="frm_grid_container">
	<span>
	<a href="https://youtu.be/N6qQQqVbhlM" target="_blank" class="btn-outline"><span class="dashicons dashicons-video-alt3" style="font-size: 21px"></span>  Youtube</a>
	<a href="https://kb.smsalert.co.in/knowledgebase/integrate-with-formidable-forms/" target="_blank" class="btn-outline"><span class="dashicons dashicons-format-aside"></span> Documentation</a></span>
	<p class="frm6 frm_form_field">
		<label for="enable_message" class="frm_inline_block">
			<input type="checkbox" name="options[chatondesk_enable_message]" id="enable_message" value="1" <?php checked($enable_message, 1); ?> />
			<?php esc_html_e('Enable Message', 'chat-on-desk'); ?>
		</label>
	</p>
    <p class="frm6 frm_form_field">
    <label for="enable_otp" class="frm_inline_block">
        <input type="checkbox" name="options[chatondesk_enable_otp]" id="enable_otp" value="1" <?php checked($enable_otp, 1); ?> />
        <?php esc_html_e('Enable Mobile Verification', 'chat-on-desk'); ?>
    </label>
    </p>
	<div>
		<p class="frm12 frm_form_field">
		<label for="visitor_phone">
				Select Phone Field        </label>
		<select name="options[visitor_phone]" id="visitor_phone">
		<?php
		if(!empty($results)) {
			foreach($results as $result)
			{
				?>
				<option value="<?php echo $result->id; ?>" <?php echo ($result->id==$visitor_phone)?'selected':''; ?>>
				<?php echo $result->name; ?>
			</option>
				<?php
			}
		}
		?>
		</select>
		</p>
		<p>
			<label for="visitor_message">
				Visitor Message        </label>
		</p>
		<?php
				 $token = array();
				 foreach ( $results as $form_field ) {
					
					$id = $form_field->id;
					$key = $form_field->name;
					$token['['. $key.'_'.$id .']'] = $key;					
				}
						
				$params = array(
					'name'  => esc_attr('options[visitor_message]'),
					'data_parent_id'  => esc_attr('enable_message'),
					'menu_id'  => "menu_wc_visitor_message",
					'sms_text'  => $visitor_msg,
					'token'     => $token,
					'moreoption' => false,
				);
				
				echo \ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params); 
					
				 ?>
	</div>
	<div>
		<p class="frm12 frm_form_field">
			<label for="admin_number">
				Send Admin SMS To        </label>
			<input type="text" id="admin_number" name="options[admin_number]">
		</p>
		<p>
		</br>
		  <label for="admin_message">
				Admin Message        </label>
	   <?php
						
				$params = array(
					'name'  => esc_attr('options[admin_message]'),
					'data_parent_id'  => esc_attr('enable_message'),
					'menu_id'  => "menu_wc_admin_message",
					'sms_text'  => $admin_message,
					'token'     => $token,
					'moreoption' => false,
				);			
				echo \ChatOnDesk\get_chatondesk_template('template/dropdown.php', $params);
					
				 ?>
		</p>
	</div>
</div>
<script>
var adminnumber = '<?php echo $admin_number; ?>';
var tagInput1     = new TagsInput({
    selector: 'admin_number',
    duplicate : false,
    max : 10,
});
var number = (adminnumber!='') ? adminnumber.split(",") : [];
if(number.length > 0){
    tagInput1.addData(number);
}
jQuery(document).on("click", ".smsalerttokens a", function() {
        return insertAtText(jQuery(this).attr("data-val"), jQuery(this).parents(".tokens").find("textarea").attr("id"));
    });
function insertAtText(e, t) {
    var s = document.getElementById(t);
    if (document.all)
        if (s.createTextRange && s.caretPos) {
            var i = s.caretPos;
            i.text = " " == i.text.charAt(i.text.length - 1) ? e + " " : e
        } else s.value = s.value + e;
    else if (s.setSelectionRange) {
        var r = s.selectionStart,
            o = s.selectionEnd,
            n = s.value.substring(0, r),
            l = s.value.substring(o);
        s.value = n + e + l
    } else alert("This version of Mozilla based browser does not support setSelectionRange")
}
</script>