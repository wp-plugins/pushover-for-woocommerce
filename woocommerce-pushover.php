<?php
/*
Plugin Name: WooCommerce Pushover Integration
Plugin URI: http://www.growdevelopment.com
Description: Integrates <a href="http://www.woothemes.com/woocommerce" target="_blank" >WooCommerce</a> with the <a href="https://pushover.net/" target="_blank">Pushover</a> notifications app for Android and iOS.
Version: 1.0.0
Author: Daniel Espinoza
Author URI: http://www.growdevelopment.com
*/
/* 
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

/**
 * Required functions
 */
 
 
if ( is_woocommerce_active() ) {

	if ( class_exists( 'WC_Pushover' ) ) return;

	/**
	 * Localisation
	 */
	load_plugin_textdomain( 'wc_pushover', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );


	define( 'WC_PUSHOVER_DIR', plugin_dir_path(__FILE__) );

	/**
	 * Plugin activation check
	 */
	function wc_pushover_activation_check(){
		if ( ! function_exists( 'simplexml_load_string' ) ) {
	        deactivate_plugins( basename( __FILE__ ) );
	        wp_die( "Sorry, but you can't run this plugin, it requires the SimpleXML library installed on your server/hosting to function." );
		}
	}
	
	register_activation_hook( __FILE__, 'wc_pushover_activation_check' );
	

	/**
	 * wc_pushover_init function.
	 *
	 * @access public
	 * @return void
	 */
	function wc_pushover_init() {
		include_once( 'classes/class-wc-pushover.php' );
	}

	add_action( 'woocommerce_integrations_init', 'wc_pushover_init' );


	function add_pushover_integration( $integrations ) {
		$integrations[] = 'WC_Pushover';
		return $integrations;
	}
	
	add_filter('woocommerce_integrations', 'add_pushover_integration' );


	/**
	 * Plugin page links
	 */
	function wc_pushover_plugin_links( $links ) {
	
		$plugin_links = array(
			'<a href="' . admin_url( 'admin.php?page=woocommerce_settings&tab=integration' ) . '">' . __( 'Settings', 'wc_pushover' ) . '</a>',
			'<a href="http://www.growdevelopment.com/support">' . __( 'Support', 'wc_pushover' ) . '</a>',
			'<a href="http://www.growdevelopment.com/support">' . __( 'Docs', 'wc_pushover' ) . '</a>',
		);
	
		return array_merge( $plugin_links, $links );
	}
	
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wc_pushover_plugin_links' );


} /* is_woocommerce_active() */