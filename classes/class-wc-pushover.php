<?php
/**
 * WC_Pushover class.
 *
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
class WC_Pushover extends WC_Integration {

   /**
    * __consturct()
    *
    * @access public
    * @return void
    */
	public function __construct() {
	
        $this->id					= 'pushover';
        $this->method_title     	= __( 'Pushover', 'wc_pushover' );
        $this->method_description	= __( 'Pushover makes it easy to send real-time notifications to your Android and iOS devices.', 'wc_pushover' );

		// Load the form fields.
		$this->init_form_fields();

		// Load the settings.
		$this->init_settings();

		// Define user set variables
		
		$this->enabled			= isset( $this->settings['enabled'] ) && $this->settings['enabled'] == 'yes' ? true : false;
		$this->site_api			= isset( $this->settings['site_api'] ) ? $this->settings['site_api'] : '';
		$this->user_api			= isset( $this->settings['user_api'] ) ? $this->settings['user_api'] : '';
		$this->device			= isset( $this->settings['device'] ) ? $this->settings['device'] : '';
		$this->priority			= isset( $this->settings['priority'] ) ? $this->settings['priority'] : '';
		$this->debug			= isset( $this->settings['debug'] ) && $this->settings['debug'] == 'yes' ? true : false;
		
		// Notices
		$this->notify_new_order	= isset( $this->settings['notify_new_order'] ) && $this->settings['notify_new_order'] == 'yes' ? true : false;    		
		$this->notify_backorder	= isset( $this->settings['notify_backorder'] ) && $this->settings['notify_backorder'] == 'yes' ? true : false;    		
		$this->notify_no_stock	= isset( $this->settings['notify_no_stock'] )  && $this->settings['notify_no_stock'] == 'yes' ? true : false;    		
		$this->notify_low_stock	= isset( $this->settings['notify_low_stock'] ) && $this->settings['notify_low_stock'] == 'yes' ? true : false;    		
    		
		// Actions
		add_action( 'woocommerce_update_options_integration_pushover', array( &$this, 'process_admin_options') );
		add_action( 'init', array( $this, 'wc_pushover_init' ), 10 );

		if ( $this->notify_new_order )
			add_action( 'woocommerce_thankyou', array( $this, 'notify_new_order' ) );
		if ( $this->notify_backorder )
			add_action( 'woocommerce_product_on_backorder', array( $this, 'notify_backorder' ) );
		if ( $this->notify_no_stock )
			add_action( 'notify_no_stock', array( $this, 'notify_no_stock' ) );
		if ( $this->notify_low_stock )
			add_action( 'notify_low_stock', array( $this, 'notify_low_stock' ) );

	}

    /**
     * Initialise Settings Form Fields
     *
     * @access public
     * @return void
     */
    function init_form_fields() {
	    global $woocommerce;

    	$this->form_fields = array(
    		'enabled' => array(
    			'title' 			=>  __( 'Enable/Disable', 'wc_pushover' ),
    			'label'	 			=>  __( 'Enable sending of notifications', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'site_api' => array(
    			'title' 			=>  __( 'Site API Token', 'wc_pushover' ),
    			'description'	 	=>  __( '', 'wc_pushover' ),
    			'type' 				=>  'text',
    			'default' 			=>  '',
    		),
    		'user_api' => array(
    			'title' 			=>  __( 'User API Token', 'wc_pushover' ),
    			'description'	 	=>  __( '', 'wc_pushover' ),
    			'type' 				=>  'text',
    			'default' 			=>  '',
    		),
    		'device' => array(
    			'title' 			=>  __( 'Device', 'wc_pushover' ),
    			'description'	 	=>  __( 'Optional: Name of device to send notifications', 'wc_pushover' ),
    			'type' 				=>  'text',
    			'default' 			=>  '',
    		),
    		'debug' => array(
    			'title' 			=>  __( 'Debug', 'wc_pushover' ),
    			'description'	 	=>  __( 'Enable debug logging', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'notifications' => array(
    			'title' 			=>  __( 'Notifications', 'wc_pushover' ),
    			'type' 				=>  'title',
    		),
    		'notify_new_order' => array(
    			'title' 			=>  __( 'New Order', 'wc_pushover' ),
    			'label'	 			=>  __( 'Sent notification when a new order is received', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'notify_backorder' => array(
    			'title' 			=>  __( 'Back Order', 'wc_pushover' ),
    			'label'	 			=>  __( 'Sent notification when a product is back ordered', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'notify_no_stock' => array(
    			'title' 			=>  __( 'No Stock', 'wc_pushover' ),
    			'label'	 			=>  __( 'Sent notification when a product has no stock', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'notify_low_stock' => array(
    			'title' 			=>  __( 'Low Stock', 'wc_pushover' ),
    			'label'	 			=>  __( 'Sent notification when a product hits the low stock ', 'wc_pushover' ),
    			'type' 				=>  'checkbox',
    			'default' 			=>  'no',
    		),
    		'test_button' => array(
    			'type' 				=>  'test_button',
    		),

		);

    } // End init_form_fields()


    /**
     * wc_pushover_init
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function wc_pushover_init() {
	
		if ( isset($_GET['wc_test']) && ($_GET['wc_test']==1)){
			$title 		= __( 'Test Notification', 'wc_pushover');
			$message 	= sprintf(__( 'This is a test notification from %s', 'wc_pushover'), get_bloginfo('name'));
			$url 		=  get_admin_url();
			
			$this->send_notification( $title, $message, $url);
		
			wp_safe_redirect( get_admin_url() . 'admin.php?page=woocommerce_settings&tab=integration&section=pushover' ); 
		}
	}
	
	
    /**
     * notify_new_order
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function notify_new_order( $order_id ) {
		global $woocommerce; 
			
		$order = new WC_Order( $order_id );

		// get order details
		$title 		= sprintf( __( 'New Order %d', 'wc_pushover'), $order_id );
		$message 	= sprintf( __( 'You have a new order from %s for $%s ', 'wc_pushover'), $order->billing_first_name . " " . $order->billing_last_name, $order->order_total );
		$url 		= get_admin_url();
		
		$this->send_notification( $title, $message, $url);
	
	}
	
    /**
     * notify_backorder
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function notify_backorder( $args ) {
		global $woocommerce; 
			
		$product = $args['product'];
		$title 		= sprintf( __( 'Product Backorder', 'wc_pushover'), $order_id );
		$message 	= sprintf( __( 'Product (#%d %s) is on backorder.', 'wc_pushover'), $product->id, $product->get_title() );
		$url 		= get_admin_url();
		
		$this->send_notification( $title, $message, $url);
	
	}

    /**
     * notify_no_stock
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function notify_no_stock( $product ) {
		global $woocommerce; 
	
		$title 		= __( 'Product Out of Stock', 'wc_pushover');
		$message 	= sprintf( __( 'Product %s %s is now out of stock.', 'wc_pushover'), $product->id, $product->get_title()  );
		$url 		= get_admin_url();
		
		$this->send_notification( $title, $message, $url);
	
	}

    /**
     * notify_low_stock
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function notify_low_stock( $product ) {
		global $woocommerce; 
			
		// get order details
		$title 		= __( 'Product Low Stock', 'wc_pushover');
		$message 	= sprintf( __( 'Product %s %s now has low stock.', 'wc_pushover'), $product->id, $product->get_title() );
		$url 		= get_admin_url();
		
		$this->send_notification( $title, $message, $url);
	
	}


    /**
     * send_notification
     *
     * Send notification when new order is received
     *
     * @access public
     * @return void
     */
	function send_notification( $title, $message, $url='' ) {
	
	  	if ( ! class_exists( 'Pushover_Api' ) )
			include_once( 'class-pushover-api.php' );

		$pushover = new Pushover_Api(); 	

		// check settings, if not return
		if ( ( '' == $this->site_api ) || ( '' == $this->user_api ) ) {
			$this->add_log( __('Site API or User API setting is missing.  Notification not sent.', 'wc_pushover') );
			return; 
		}
			
		// Setup settings
		$pushover->setSiteApi( $this->site_api );
		$pushover->setUserApi( $this->user_api );
		if ( '' != $this->device ) { 
			$pushover->setDevice( $this->device );
		}

		// Setup message
		$pushover->setTitle ( $title );
		$pushover->setMessage( $message );
		$pushover->setUrl( $url );
		
		$this->add_log( __('Sending: ', 'wc_pushover') . 
							"\nTitle: ". $title .
							"\nMessage: ". $message .
							"\nURL: " . $url ); 
		
		try {
			$response = $pushover->send();
			$this->add_log( __('Response: ', 'wc_pushover') . "\n" . $response );
			
		} catch ( Exception $e ) {
			$this->add_log( sprintf(__('Error: Caught exception from send method: %s', 'wc_pushover'), $e->getMessage() ) );
		}
		
		$this->add_log( __('Pushover response', 'wc_pushover') . $response ); 

	}

    /**
     * generate_test_button_html()
     *
     * @access public
     * @return void
     */
	function generate_test_button_html() {
		ob_start();
		?>
		<tr valign="top" id="service_options">
			<th scope="row" class="titledesc"><?php _e( 'Send Test', 'wc_ups' ); ?></th>
			<td >
			<p><a href="<?php echo get_admin_url(); ?>admin.php?page=woocommerce_settings&tab=integration&section=pushover&wc_test=1" class="button" ><?php _e('Send Test Notification', 'wc_pushover'); ?></a></p>
			</td>
		</tr>
		<?php
		return ob_get_clean();
	}
	
	
    /**
     * add_log
     *
     * @access public
     * @return void
     */
	function add_log( $message ) {

		if ( ! $this->debug ) return; 		

		$time = date_i18n( 'm-d-Y @ H:i:s -' );
		$handle = fopen( WC_PUSHOVER_DIR . 'debug_pushover.log', 'a' );	
		if ( $handle ) {
			fwrite( $handle, $time . " " . $message . "\n" );
			fclose( $handle );
		}

	}

	

} /* class WC_Pushover */