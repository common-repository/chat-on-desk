<div class="cod-accordion" style="padding: 0px 10px 10px 10px;">
    <table class="form-table">
        <tr valign="top">
            <td>
                <h2><?php esc_html_e('We would be glad to help you. You can reach us through any of the three ways.', 'chat-on-desk'); ?></h2>
            </td>
        </tr>
        <tr valign="top">
            <td>
                <div class="col-lg-12 creditlist" >
                    <div class="col-lg-8 route">
                        <h3><span class="dashicons dashicons-welcome-learn-more"></span>
                        <?php
                        /* translators: %s: Ticketing Support URL */
                        echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Browse</a> Documentation', 'chat-on-desk'), esc_url('https://kb.smsalert.co.in/wordpress')));
                        ?>
                        </h3>
                    </div>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <td>
                <div class="col-lg-12 creditlist" >
                    <div class="col-lg-8 route">
                        <h3><span class="dashicons dashicons-tickets-alt"></span>
                        <?php
                        /* translators: %s: Ticketing Support URL */
                        echo wp_kses_post(sprintf(__('<a href="%s" target="_blank">Click Here</a> to generate a support ticket. ', 'chat-on-desk'), 'http://support.cozyvision.com/'));
                        ?>
                        </h3>
                    </div>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <td>
                <div class="col-lg-12 creditlist" >
                    <div class="col-lg-8 route">
                        <h3><span class="dashicons dashicons-email-alt"></span> <?php esc_html_e('Email Support', 'chat-on-desk'); ?>:
                        <a href="mailto:support@cozyvision.com" target="_blank">support@cozyvision.com</a>
                        </h3>
                    </div>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <td>
                <div class="col-lg-12 creditlist" >
                    <div class="col-lg-8 route">
                        <h3><span class="dashicons dashicons-phone"></span> <?php esc_html_e('Phone Support', 'chat-on-desk'); ?>: (+91)-80-1055-1055</h3>
                    </div>
                </div>
            </td>
        </tr>
        <tr valign="top">
            <td>
                <?php esc_html_e('If you like', 'chat-on-desk'); ?><strong> <?php esc_html_e('Chat On Desk', 'chat-on-desk'); ?></strong> <?php esc_html_e('please leave us a', 'chat-on-desk'); ?> <a href="https://wordpress.org/support/plugin/chat-on-desk/reviews/#postform" target="_blank" class="wc-rating-link">★★★★★</a> <?php esc_html_e('Thanks in advance.', 'chat-on-desk'); ?>
            </td>
        </tr>
    </table>
</div>
