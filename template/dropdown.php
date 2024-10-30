<div class="chatondeskModal preview-template">
	<div class="modal-content">
		<div class="close"><span></span></div>
		<div class="modal-body" style="padding:1em">
			<div style="margin:1.7em 1.5em;">
				<div style="position:relative" class="cod-message preview-message"></div>
				</div>
        </div>
	</div>
</div>
<?php
    $template = json_decode($sms_text,true);
	$url = add_query_arg(
		array(
			'action'    => 'foo_modal_box',
			'TB_iframe' => 'true',
			'width'     => '800',
			'height'    => '500'
		),
		admin_url('admin.php?page=preview-cod-template')
	);
	$add_url = add_query_arg(
		array(
			'action'    => 'foo_modal_box',
			'TB_iframe' => 'true',
			'width'     => '800',
			'height'    => '500'
		),
		admin_url('admin.php?page=add-cod-template')
	);
	?>
	<span class="temp_name"><?php echo !empty($template['Structuredtemplate']['name'])?$template['Structuredtemplate']['name']:''; ?></span> <a href="<?php echo esc_url($url); ?>" class="thickbox cod-browse-btn editbtn <?php echo empty($template['Structuredtemplate']['name'])?'hide':''; ?>">(<?php esc_html_e('Edit Template', 'chat-on-desk'); ?>)</a><a href="<?php echo esc_url($add_url); ?>" class="thickbox cod-browse-btn addbtn <?php echo !empty($template['Structuredtemplate']['name'])?'hide':''; ?>"> <?php esc_html_e('Select Template', 'chat-on-desk'); ?></a>
	<select name="cod_token" class="cod-token hide">
	<option value="">Select Token</option>
	<option value="custom">Custom</option>
	<?php
	foreach ( $token as $vk => $vv ) {
		echo  "<option value='".esc_attr($vk)."'>".esc_attr($vv)."</option>";
	}
	?>
	</select>
	<input type="hidden" name="<?php echo esc_attr($name); ?>" class="cod_template_text" value="<?php echo esc_textarea($sms_text); ?>">
	<span class="dashicons dashicons-search codpreview <?php echo empty($template['Structuredtemplate']['name'])?'hide':''; ?>" onclick="previewTemplate(this);" style="margin-left: 10px; cursor:pointer" title="Preview Template"></span>
	<script>
	jQuery(".cod-browse-btn").on("click", function() {
	   jQuery('.cod-browse-btn').removeClass('active');	
	   jQuery(this).addClass('active');
	});
	window.addEventListener('message', receiveMessage, false);
	function receiveMessage(evt) {
		if (evt.data.type=='chatondesk_template_data')
		{
			var temp = JSON.parse(evt.data.template);
			jQuery('.cod-browse-btn.active').parent().find('.temp_name').text(temp.Structuredtemplate.name);
			jQuery('.cod-browse-btn.active').parent().find('.cod_template_text').val(evt.data.template);
			tb_remove();
			if(jQuery('.cod-browse-btn.active').hasClass('addbtn'))
			{
				jQuery('.cod-browse-btn.active.addbtn').parent().find('.editbtn,.codpreview').removeClass('hide');
				jQuery('.cod-browse-btn.active.addbtn').addClass('hide');
			}
		}
	}
	function previewTemplate(obj) {
		var template = (jQuery(obj).parent().find('.cod_template_text').val() != '')?JSON.parse(jQuery(obj).parent().find('.cod_template_text').val()):'';
		var str_temp = (template!='')?JSON.parse(template.Structuredtemplate.template):'';
		var msg_text = '';
		if(str_temp == '')
		{
			msg_text = "<?php esc_html_e('No template is selected', 'chat-on-desk'); ?>";
		}
		else{
			if(typeof str_temp.header != 'undefined')
			{
				msg_text+=str_temp.header.message;
			}
			if(typeof str_temp.body != 'undefined')
			{
				msg_text+=(msg_text!='')?'</br>'+str_temp.body.message:str_temp.body.message;
			}
			if(typeof str_temp.footer != 'undefined')
			{
				msg_text+='</br>'+str_temp.footer.message;
			}
		}
		if(typeof template.data != 'undefined')
		{
			jQuery.each(template.data,function(key,val){
				msg_text = msg_text.replace('##'+key+'##',val);	
			});
		}
        jQuery(obj).parent().find('.chatondeskModal.preview-template .cod-message').html(msg_text);
        jQuery(obj).parent().find(".chatondeskModal.preview-template").show();
    }
	jQuery(document).on("click", ".close",function(){
        jQuery(".blockUI").hide();
        jQuery(this).parents(".chatondeskModal.preview-template").not('.chatondesk-modal').hide('slow');
    });
	</script>