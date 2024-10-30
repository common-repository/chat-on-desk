<?php
class SMSAlertWidgets extends \WP_Widget
{
    function __construct()
    {
        parent::__construct(
            'chatondesk_widget',
            esc_html__('ChatOnDesk', 'chat-on-desk'),
            array('description' => esc_html__('Add smsalert form', 'chat-on-desk'),)
        );
    }

    public function widget($args, $instance)
    {
        $selectedForm = empty($instance['cod_shortcode']) ? '' : $instance['cod_shortcode'];
        if(!$selectedForm) {
            return;
        }
        echo isset($args['before_widget'])?$args['before_widget']:'';
        if ($selectedForm != '') {
            echo ($selectedForm==1)?do_shortcode("[cod_signupwithmobile]"):(($selectedForm==2)?do_shortcode("[cod_loginwithotp]"):do_shortcode("[cod_sharecart]"));
        }
        echo isset($args['after_widget'])?$args['after_widget']:'';

    }

    public function form($instance)
    {
        $selectedForm = empty($instance['cod_shortcode']) ? '' : $instance['cod_shortcode'];
        $forms = array(''=>'Select Form','1'=>'Signup With Mobile','2'=>'Login With Otp','3'=>'Share Cart Button');
        ?>
        
        <label for="<?php echo $this->get_field_id('cod_shortcode'); ?>">Form:
            <select style="margin-bottom: 12px;" class='widefat' id="<?php echo $this->get_field_id('cod_shortcode'); ?>"
                    name="<?php echo $this->get_field_name('cod_shortcode'); ?>" type="text"
            >
                <?php
                foreach ($forms as $key=>$item) {
                    ?>
                    <option <?php if ($key == $selectedForm) {
                        echo 'selected';
} ?> value='<?php echo $key; ?>'>
                        <?php echo $item; ?>
                    </option>
                    <?php
                }
                ?>
            </select>
        </label>
            <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['cod_shortcode'] = intval($new_instance['cod_shortcode']);
        return $instance;
    }
}
function chatondesk_register_widgets()
{
    register_widget('SMSAlertWidgets');
}

add_action('widgets_init', 'chatondesk_register_widgets');
