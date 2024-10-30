<?php
/**
 * Backin stock template.
* PHP version 5
 *
 * @category Template
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */
	use Elementor\Frontend;
	$post = get_page_by_path( 'cod_notifyme_style', OBJECT, 'chat-on-desk' );
	if ( is_plugin_active('elementor/elementor.php') && !empty($post)) {  
	 $post_id= $post->ID;	
	 $frontent = new Frontend();
	 $content =  $frontent->get_builder_content($post_id);	
	} else {
	 $content = CodPopup::getNotifyMeStyle();		
	}		
	echo $content;
?>
 <input type="hidden" id="cod-product-id" name="cod-product-id" value="<?php echo esc_attr($product_id); ?>"/>
 <input type="hidden" id="cod-variation-id" name="cod-variation-id" value="<?php echo esc_attr($variation_id); ?>"/>
<div style="clear:both;"></div>