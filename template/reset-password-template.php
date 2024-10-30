<?php
/**
 * Reset password template.
 * PHP version 5
 *
 * @category Template
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

if (! headers_sent() ) {
    header('Content-Type: text/html; charset=utf-8');
}
        echo '<html>
				<head>
					<meta http-equiv="X-UA-Compatible" content="IE=edge">
					<meta name="viewport" content="width=device-width, initial-scale=1">';
        wp_head();
        echo '<body>
					<div class="cod-modal-backdrop">
						<div class="cod_customer_validation-modal" tabindex="-1" role="dialog" id="cod_site_otp_form">
							<div class="cod_customer_validation-modal-backdrop"></div>
							<div class="cod_customer_validation-modal-dialog cod_customer_validation-modal-md">
								<div class="login cod_customer_validation-modal-content">
									<div class="cod_customer_validation-modal-header">
										<b>' . esc_html__('Change Password', 'chat-on-desk') . '</b>
										<a class="go_back" href="#" onclick="cod_validation_goback();" style="box-shadow: none;">&larr; ' . esc_html__('Go Back', 'chat-on-desk') . '</a>
									</div>
									<div class="cod_customer_validation-modal-body center">
										<div>' . esc_attr($message) . '</div><br /> ';
if (! \ChatOnDesk\SmsAlertUtility::isBlank($user_email) || ! \ChatOnDesk\SmsAlertUtility::isBlank($phone_number) ) {
    echo '								<div class="cod_customer_validation-login-container">
												<form name="f" method="post" action="">
													<input type="hidden" name="option" value="' . esc_attr($action) . '" />
													<label>New password</label>
													<input type="password" name="chatondesk_user_newpwd"  autofocus="true" placeholder="" id="chatondesk_user_pwd" required="true" title="Enter Your New password" />
													
													<label>Confirm password</label>
													<input type="password" name="chatondesk_user_cnfpwd"  autofocus="true" placeholder="" id="chatondesk_user_cnfpwd" required="true" title="Confirm password" />
													
													<br /><input type="submit" name="chatondesk_reset_password_btn" id="chatondesk_reset_password_btn" class="chatondesk_otp_token_submit" value="' . esc_html__('Change Password', 'chat-on-desk') . '" />
													<input type="hidden" name="otp_type" value="' . esc_attr($otp_type) . '">';


    cod_extra_post_data();
    echo '									</form>
											</div>';
}
        echo '						</div>
								</div>
							</div>
						</div>
					</div>
					
					<form name="f" method="post" action="" id="validation_goBack_form">
						<input id="validation_goBack" name="option" value="validation_goBack" type="hidden"></input>
					</form>
					
					<style> 
						.cod_customer_validation-modal{ display: block !important; } 
						input[type="password"]{background: #FBFBFB none repeat scroll 0% 0%;font-family: "Open Sans",sans-serif;font-size: 24px;width: 100%;border: 1px solid #DDD;padding: 3px;margin: 2px 6px 16px 0px;}
					</style>
					<script>
						function cod_validation_goback(){
							document.getElementById("validation_goBack_form").submit();
						}
					</script>
				</body>
		    </html>';
