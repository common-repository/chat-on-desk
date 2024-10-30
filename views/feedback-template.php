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
global $pagenow;
if (empty($pagenow) || 'plugins.php' != $pagenow ) {
    return false;
}

    $form_fields = apply_filters('chatondesk_deactivation_form_fields', array());
?>
<?php if (! empty($form_fields) ) : ?>
    <div class="codf-onboarding-section">
        <div class="codf-on-boarding-wrapper-background">
        <div class="codf-on-boarding-wrapper">
            <div class="codf-on-boarding-close-btn">
                <a href="javascript:void(0);">
                    <span class="close-form">x</span>
                </a>
            </div>
            <h3 class="codf-on-boarding-heading"></h3>
            <p class="codf-on-boarding-desc"><?php esc_html_e('May we have a little info about why you are deactivating?', 'chat-on-desk'); ?></p>
            <form action="#" method="post" class="codf-on-boarding-form">
                <?php foreach ( $form_fields as $key => $field_attr ) : ?>
                    <?php $this->renderFieldHtml($field_attr, 'deactivating'); ?>
                <?php endforeach; ?>
                <div class="codf-on-boarding-form-btn__wrapper">
                    <div class="codf-on-boarding-form-submit codf-on-boarding-form-verify ">
                    <button type="submit" class="codf-on-boarding-submit codf-on-boarding-verify button button-danger"><span class="button__text">SUBMIT AND DEACTIVATE</span></button>
                </div>
                <div class="codf-on-boarding-form-no_thanks">
                    <a href="javascript:void(0);" class="codf-deactivation-no_thanks"><?php esc_html_e('Skip and Deactivate Now', 'chat-on-desk'); ?></a>
                </div>
                </div>
            </form>
        </div>
    </div>
    </div>
<?php endif; ?>
