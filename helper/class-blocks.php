<?php
/**
 * CodBlocks helper.
 *
 * PHP version 5
 *
 * @category Handler
 * @package  ChatOnDesk
 * @author   Chat On Desk <support@cozyvision.com>
 * @license  URI: http://www.gnu.org/licenses/gpl-2.0.html
 * @link     https://www.chatondesk.com/
 */

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
 * CodBlocks class
 */
class CodBlocks {

		/**
		 * Blocks constructor.
		 */
		public function __construct() {
			add_action( 'init', array( &$this, 'block_editor_render' ) );
		}

		/**
		 * Register Chat On Desk Blocks.
		 *
		 * @uses register_block_type_from_metadata()
		 */
		public function block_editor_render() {
			
			$blocks = array(
				'chat-on-desk/cod-loginwithotp'     => array(
					'render_callback' => array( $this, 'cod_loginwithotp_render' ),
				),
				'chat-on-desk/cod-sharecart'     => array(
					'render_callback' => array( $this, 'cod_sharecart_render' ),
				),
				'chat-on-desk/cod-signupwithmobile'     => array(
					'render_callback' => array( $this, 'cod_signupwithmobile_render' ),
				)
			);

			foreach ( $blocks as $k => $block_data ) {
				$block_type = str_replace( 'chat-on-desk/', '', $k );
				register_block_type_from_metadata( COD_MOV_DIR . 'blocks/' . $block_type, $block_data );
			}
		}

		/**
		 * Renders Chat On Desk Login With OTP form block.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function cod_loginwithotp_render() {
			$shortcode = '[cod_loginwithotp]';

			return apply_shortcodes( $shortcode );
		}
		
		/**
		 * Renders Chat On Desk Share Cart block.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function cod_sharecart_render() {
			$shortcode = '[cod_sharecart]';

			return apply_shortcodes( $shortcode );
		}
		
		/**
		 * Renders Chat On Desk Signup With Mobile form block.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function cod_signupwithmobile_render() {
			$shortcode = '[cod_signupwithmobile]';

			return apply_shortcodes( $shortcode );
		}
	}
new CodBlocks();
?>