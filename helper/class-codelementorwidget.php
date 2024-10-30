<?php
/**
 * Emementer Widget helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

use Elementor\Plugin as Elementor;

/**
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 *
 * ElementorWidget class
 */
class CodElementorWidget
{
    private $app = null;
    
    /**
     * Construct function
     *
     * @param $app app.
     *
     * @return array
     */
    public function __construct($app)
    {
        $this->app = $app;
        add_action('elementor/widgets/register', [$this, 'initWidgets']);
    }

    /**
     * Init widgets function
     *
     * @return array
     */
    public function initWidgets()
    {
        $widgets_manager = Elementor::instance()->widgets_manager;
        if (file_exists(plugin_dir_path(__DIR__) . 'helper/class-codforms.php')) {            
            include_once plugin_dir_path(__DIR__) . 'helper/class-codforms.php';
            $widgets_manager->register(new CodForms());           
        }
    }
}
new CodElementorWidget("chatondeskapp");















