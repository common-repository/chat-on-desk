<?php
/**
 * Otp register template.
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
        echo '</head>
				<body>
					<div class="cod-modal-backdrop">
						<div class="cod_customer_validation-modal" tabindex="-1" role="dialog" id="cod_site_otp_form">
							<div class="cod_customer_validation-modal-backdrop"></div>
							<div class="cod_customer_validation-modal-dialog cod_customer_validation-modal-md">
								<div class="login cod_customer_validation-modal-content">
									<div class="cod_customer_validation-modal-header">
										<b>' . esc_html__('Validate OTP (One Time Passcode)', 'chat-on-desk') . '</b>
										<a class="go_back" href="#" onclick="cod_validation_goback();" style="box-shadow: none;">&larr; ' . esc_html__('Go Back', 'chat-on-desk') . '</a>
									</div>
									<div class="cod_customer_validation-modal-body center">
										<div>' . esc_attr($message) . '</div><br /> ';
if (! \ChatOnDesk\SmsAlertUtility::isBlank($user_email) || ! \ChatOnDesk\SmsAlertUtility::isBlank($phone_number) ) {
    echo '								<div class="cod_customer_validation-login-container">
												<form name="f" id="cod-form" method="post" action="">
													<input type="hidden" name="option" value="chatondesk-validate-otp-form" />
													<input type="text" name="chatondesk_customer_validation_otp_token"  autofocus="true" placeholder="" id="chatondesk_customer_validation_otp_token" required="true" class="cod_customer_validation-textbox" autofocus="true" pattern="[0-9]{4,8}" title="' . esc_attr(SmsAlertMessages::showMessage('OTP_RANGE')) . '" />
													<br /><input type="submit" name="chatondesk_otp_token_submit" id="chatondesk_otp_token_submit" class="chatondesk_otp_token_submit"  value="' . esc_html__('Validate OTP', 'chat-on-desk') . '" />
													<input type="hidden" name="otp_type" value="' . esc_attr($otp_type) . '">';
    if (! $from_both ) {
        echo '											<input type="hidden" id="from_both" name="from_both" value="false" />
														<a style="float:right" id="verify_otp" onclick="cod_otp_verification_resend();">' . esc_attr(SmsAlertMessages::showMessage('RESEND_OTP')) . '</a>
														<span id="timer" style="min-width:80px; float:right;margin-right: 5px;"><span id="stimer">00:00</span> ' . esc_html__('sec', 'chat-on-desk') . '</span>';
    } else {
        echo '											<input type="hidden" id="from_both" name="from_both" value="true" />
														<a style="float:right" id="verify_otp" onclick="cod_select_goback();">' . esc_attr(SmsAlertMessages::showMessage('RESEND_OTP')) . '</a>
														<span id="timer" style="min-width:80px; float:right;margin-right: 5px;"><span id="stimer">00:00</span> ' . esc_html__('sec', 'chat-on-desk') . '</span>';
    }

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
					
					<form name="f" method="post" action="" id="verification_resend_otp_form">
						<input id="verification_resend_otp" name="option" value="verification_resend_otp_' . esc_attr($otp_type) . '" type="hidden" />' . PHP_EOL;
if (! $from_both ) {
    echo '				<input type="hidden" id="from_both" name="from_both" value="false" />' . PHP_EOL;
} else {
    echo '				<input type="hidden" id="from_both" name="from_both" value="true" />' . PHP_EOL;
}

                        cod_extra_post_data();

        echo '		</form>

					<form name="f" method="post" action="" id="goBack_choice_otp_form">
						<input id="verification_resend_otp" name="option" value="verification_resend_otp_both" type="hidden" />
						<input type="hidden" id="from_both" name="from_both" value="true" />';

                        cod_extra_post_data();

        echo '		</form>

					<style> 
					.cod_customer_validation-modal{ display: block !important; } 
					
					#verify_otp{pointer-events: none; cursor: not-allowed; opacity: .5;text-decoration:none;box-shadow: none;}
					.displaynone{display:none;}
					input[type="text"].cod_customer_validation-textbox {background: #FBFBFB none repeat scroll 0% 0%;font-family: "Open Sans",sans-serif;font-size: 24px;width: 100%;border: 1px solid #DDD;padding: 3px;margin: 2px 6px 16px 0px;}
					</style>
					<script>
					jQuery("#cod-form").on("submit", function () {
						jQuery("#chatondesk_otp_token_submit").attr("disabled", "disabled");
					});
						function cod_validation_goback(){
							document.getElementById("validation_goBack_form").submit();
						}
						
						function cod_otp_verification_resend(){
							document.getElementById("verification_resend_otp_form").submit();
						}

						function cod_select_goback(){
							document.getElementById("goBack_choice_otp_form").submit();
						}
						var timer = function(secs){
							var sec_num = parseInt(secs, 10)    
							var hours   = Math.floor(sec_num / 3600) % 24
							var minutes = Math.floor(sec_num / 60) % 60
							var seconds = sec_num % 60    
							
							hours = hours < 10 ? "0" + hours : hours;
							minutes = minutes < 10 ? "0" + minutes : minutes;
							seconds = seconds < 10 ? "0" + seconds : seconds;
							return [hours,minutes,seconds].join(":")
						};
						//counter otp
						var counter = ' . esc_attr($otp_resend_timer) . ';
						var interval = setInterval(function() {
							counter--;
							document.getElementById("stimer").innerHTML = timer(counter);
							if (counter == 0) {
								clearInterval(interval);
								document.getElementById("timer").style.display = "none";
								var cssString = "pointer-events: auto; cursor: pointer; opacity: 1; float:right"; 
								document.getElementById("verify_otp").style.cssText = cssString;
							}
						}, 1000);
					</script>
				</body>
		    </html>';
