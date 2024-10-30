<?php
/**
 * Backend helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

namespace ChatOnDesk;
if (! defined('ABSPATH') ) {
    exit;
}
/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 * Chat On Desk plugin backend class.
 */
class SA_Backend
{

    /**
     * Construct function.
     *
     * @return void
     */
    public function __construct()
    {
        add_action('admin_notices', array( $this, 'smsalertReview' ), 10);
        $this->routeData();
    }

    /**
     * Called from constructor.
     *
     * @return void
     */
    public function routeData()
    {
        if (! array_key_exists('option', $_GET) ) {
            return;
        }
        switch ( trim(sanitize_text_field(wp_unslash($_GET['option']))) ) {
        case 'not-show-again':
            add_option('chatondesk_review_not_show_again', 0);
            break;
        case 'remind-later':
            $chatondesk_admin_notice_user_meta = array(
            'date-dismissed' => date('Y-m-d'),
            );
            update_user_meta(get_current_user_id(), 'chatondesk_review_remind_later', $chatondesk_admin_notice_user_meta);
            break;
        }
    }

    /**
     * Request for review.
     *
     * @return void
     */
    public function smsalertReview()
    {
        $current_date = date('Y-m-d');
        $date         = get_option('chatondesk_activation_date', date('Y-m-d'));
        $show_date    = date('Y-m-d', strtotime('+1 month', strtotime($date)));
        $show         = get_option('chatondesk_review_not_show_again', 1);
        $user_meta    = get_user_meta(get_current_user_id(), 'chatondesk_review_remind_later');
        $remind       = 0;
        if (isset($user_meta[0]['date-dismissed']) ) {
            $date_1 = $user_meta[0]['date-dismissed'];
            $date_2 = date('Y-m-d', strtotime('+7 days', strtotime($date_1)));

            if ($current_date > $date_2 ) {
                $remind = 0;
            } else {
                $remind = 1;
            }
        }
        if ('1' === $show && '0' === $remind && $current_date > $show_date ) {
            $current_user = wp_get_current_user();
            ?>
            <div class="notice notice-info">
                <p>
            <?php
            $username = $current_user->user_firstname ? $current_user->user_firstname : $current_user->nickname
            ?>
                    <span>
            <?php
            /* translators: %1$s: Chat On Desk username, %2$s: WordPress directory rating URL */
            echo wp_kses_post(sprintf(__('Hi %1$s ! You\'ve been using the <b>Chat On Desk Order Notifications Plugin</b> for a while now. If you like the plugin please support our development by leaving a ★★★★★ rating : <a href="%2$s" target="_blank">Rate it!</a>', 'smsalert'), $username, 'https://wordpress.org/support/view/plugin-reviews/chat-on-desk?rate=5#postform'));
            ?>
                    </span>
                    <span>
                        <a href="javascript:void(0)" class="chatondesk-review cod-delete-btn alignright" option="remind-later"><span class="dashicons dashicons-dismiss"></span><?php esc_html_e('Dismiss', 'smsalert'); ?></a>
                    </span>
                </p>
                <p>
                    <span>
            <?php
             /* translators: %s: WordPress directory plugin URL */
             echo wp_kses_post(sprintf(__('Or else, please leave us a support question in the forum. We\'ll be happy to assist you: <a href="%s">Get support</a> &nbsp;&nbsp; <a href="javascript:void(0)" class="chatondesk-review" option="not-show-again">Don\'t show again</a>', 'chat-on-desk'), 'https://wordpress.org/support/plugin/chat-on-desk'));
            ?>
                    </span>
                </p>
            </div>
            <?php
            echo '
			<script>
				jQuery(".chatondesk-review").unbind("click").bind("click", function() {
					var type = jQuery(this).attr("option");
					var action_url = "' . esc_url(site_url()) . '/?option="+type;
					jQuery.ajax({
						url:action_url,
						type:"GET",
						crossDomain:!0,
						success:function(o){
							location.reload();
						}
					});
				});
			</script>';
        }
    }
}
new SA_Backend();
?>
