<div class="cod-modal cod-fade" id="<?php echo esc_attr($modal_id); ?>">
    <div id="confirm" class="cod-modal-dialog">
        <div class="cod-modal-header">
            <h5 class="cod-modal-title"><?php echo esc_attr($modal_title); ?></h5>
            <button type="button" class="cod-close" data-dismiss="cod-modal" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="cod-modal-body">
            <?php echo wp_kses_post($modal_body); ?>
        </div>
        <div class="cod-modal-footer">
            <?php echo wp_kses_post($modal_footer); ?>
        </div>
    </div>
</div>
