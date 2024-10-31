<?php
/*
* Plugin name: PAYHOW
* Plugin uri: https://payhow.com.br/ecommerce
* Description: Plugin de integrações de pagamentos e recursos especiais no checkout da sua loja através da plataforma PAYHOW.
* Version: 1.0.1
* Author: PAYHOW Team
* Author uri: https://profiles.wordpress.org/payhow/
* License: GNU General Public License v3.0
* License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/
include_once(ABSPATH . 'wp-includes/pluggable.php');

class Payhow_WC
{

    private $user;
    private $protocol;
    private $cart;
    public function __construct()
    {
        $this->user = wp_get_current_user();
        $this->protocol = stripos($_SERVER['SERVER_PROTOCOL'],'https') === 0 ? 'https://' : 'http://';
        $this->cart =  function (){
            global $woocommerce;
            $woocommerce->cart->empty_cart();

        };

        if (!class_exists('WooCommerce')) {
            return;
        }

        if(isset($_GET['phow_callback'])){
            if((int)$_GET['phow_callback'] == (int)$_COOKIE['payhow_cart']){
                setcookie('payhow_cart','',time() - 3600);
                add_action( 'init', 'payhow_woocommerce_clear_cart_url' );
                function payhow_woocommerce_clear_cart_url() {
                        global $woocommerce;
                        $woocommerce->cart->empty_cart();
                }
            }
        }
        if(!isset($_COOKIE['payhow_cart'])){
            setcookie("payhow_cart",time().rand(22222,99999), time()+(3600*24)); // 1hr = 3600 secs
        }
        add_action('woocommerce_before_cart', [$this, 'add_cart_script']);
        add_action('woocommerce_checkout_before_customer_details', [$this, 'add_checkout_script'],10);
        add_action('rest_api_init', [$this, 'api']);


    }

    public function api()
    {
		
    }

	function is_checkout() {
		$page_id = wc_get_page_id( 'checkout' );

		return ( $page_id && is_page( $page_id ) ) || wc_post_content_has_shortcode( 'woocommerce_checkout' ) || apply_filters('woocommerce_is_checkout', false ) || Constants::is_defined( 'WOOCOMMERCE_CHECKOUT' );
	}
 

    /**
     * add_checkout_script
     * Put the Payhow Snippet on WC template.
     *
     * @access        public
     * @return        void
     */
    public function add_checkout_script()
    {
        $this->script(true);
    }

    public function add_cart_script()
    {
        $this->script();
    }

    public function script($isCheckout = false)
    {
		
		
		
        ?>
        <style>
            .payhow-loader {display: none; position: fixed; width: 100%; height: 100%; background: #fff; left: 0; top: 0; z-index:99999}
            .payhow-loading{position:fixed;overflow:show;margin:auto;top:0;left:0;bottom:0;right:0;width:50px;height:50px}.payhow-loading:before{content:'';display:block;position:fixed;top:0;left:0;width:100%;height:100%;background-color:#fff}.payhow-loading:not(:required){font:0/0 a;color:transparent;text-shadow:none;background-color:transparent;border:0}.payhow-loading:not(:required):after{content:'';display:block;font-size:10px;width:50px;height:50px;margin-top:-.5em;border:5px solid #f50272;border-radius:100%;border-bottom-color:transparent;-webkit-animation:spinner 1s linear 0s infinite;animation:spinner 1s linear 0s infinite}@-webkit-keyframes spinner{0%{-webkit-transform:rotate(0);-moz-transform:rotate(0);-ms-transform:rotate(0);-o-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-moz-keyframes spinner{0%{-webkit-transform:rotate(0);-moz-transform:rotate(0);-ms-transform:rotate(0);-o-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@-o-keyframes spinner{0%{-webkit-transform:rotate(0);-moz-transform:rotate(0);-ms-transform:rotate(0);-o-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}@keyframes spinner{0%{-webkit-transform:rotate(0);-moz-transform:rotate(0);-ms-transform:rotate(0);-o-transform:rotate(0);transform:rotate(0)}100%{-webkit-transform:rotate(360deg);-moz-transform:rotate(360deg);-ms-transform:rotate(360deg);-o-transform:rotate(360deg);transform:rotate(360deg)}}
        </style>

        <div class="payhow-loader">
            <div class="payhow-loading"></div>
        </div>

        <script type='text/javascript'>
            window.Payhow = {
                page: <?php echo is_checkout() ? '"checkout"' : '"cart"'; ?>,
                merchant_url: "<?php echo  esc_js($this->protocol.$_SERVER['HTTP_HOST']); ?>",
                cart: <?php echo $this->format_cart(); ?>,
                cart_hash : "<?php echo esc_js($_COOKIE['payhow_cart']);?>",
                shop_url : "<?php echo esc_js(wc_get_page_permalink('shop')); ?>",
				myaccount_url : "<?php echo esc_js(wc_get_page_permalink('myaccount')); ?>",
                <?php
                    if($this->user->user_login): ?>
                     user: {
                        user_login : "<?php echo  esc_js($this->user->user_login); ?>",
                        user_email : "<?php echo  esc_js($this->user->user_email); ?>",
                        user_nicename : "<?php echo  esc_js($this->user->user_nicename); ?>",
                        user_id : "<?php echo  esc_js($this->user->ID); ?>",
						user_firstname : "<?php echo  esc_js($this->user->first_name); ?>",
						user_lastname : "<?php echo  esc_js($this->user->last_name); ?>",
						user_address_1 : "<?php echo  esc_js(get_user_meta( $this->user->ID, 'shipping_address_1', true )); ?>",
						user_address_2 : "<?php echo esc_js(get_user_meta( $this->user->ID, 'shipping_address_2', true )); ?>",
						user_city : "<?php echo esc_js(get_user_meta( $this->user->ID, 'shipping_city', true )); ?>",
						user_state : "<?php echo esc_js(get_user_meta( $this->user->ID, 'shipping_state', true )); ?>",
						user_postcode : "<?php echo esc_js(get_user_meta( $this->user->ID, 'shipping_postcode', true )); ?>",
						user_country : "<?php echo esc_js(get_user_meta( $this->user->ID, 'shipping_country', true )); ?>"
                    }

                <?php endif; ?>
            };

            (function() {
                var ch = document.createElement('script'); ch.type = 'text/javascript'; ch.async = true;
				ch.src = 'https://plugins.payhow.com.br/woocommerce/wc_payhow.js?v=1';
                var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(ch, x);
            })();
        </script>
        <?php
    }

    /**
     * format_cart
     *
     * Format cart payload.
     *
     * @access        public
     * @return        string
     */
    public function format_cart()
    {
        $cartData = WC()->cart->get_cart();
        $cart = [];

        foreach ($cartData as $key => $item) {
            $cart['items'][] = [
                'product_id' => $item['variation_id'] ? $item['variation_id'] : $item['product_id'],
                'quantity' => $item['quantity'],
				'sku' => get_post_meta($item['product_id'] , '_sku', true ) ? get_post_meta($item['product_id'] , '_sku', true ) : ""
            ];
        }

        return json_encode($cart);
    }

}

/**
 * Load Payhow
 */
function payhow_plugins_loaded() {
    new Payhow_WC();
	payhow_validUserLogon(); //Valid user logged
}
function payhow_disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'payhow_disable_shipping_calc_on_cart', 99 );

function payhow_disable_shipping_calc_on_checkout( $show_shipping ) {
    if( is_checkout() ) {
        return false;
    }
    return $show_shipping;
}
add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'payhow_disable_shipping_calc_on_checkout', 998 );

// hide coupon field on cart page
function payhow_hide_coupon_field_on_cart( $enabled ) {

	if ( is_cart() ) {
		$enabled = false;
	}

	return $enabled;
}
add_filter( 'woocommerce_coupons_enabled', 'payhow_hide_coupon_field_on_cart' );

// hide coupon field on checkout page
function payhow_hide_coupon_field_on_checkout( $enabled ) {

	if ( is_checkout() ) {
		$enabled = false;
	}

	return $enabled;
}

add_filter( 'woocommerce_coupons_enabled', 'payhow_hide_coupon_field_on_checkout' );
add_action('plugins_loaded', 'payhow_plugins_loaded');


//set payhow to menu
function payhow_register_top_level_menu(){
    $icon = plugin_dir_url( __FILE__ ) . 'images/payhow_icon_menu.png';
    add_menu_page(
        'PAYHOW - Plugin de Integração',
        'PAYHOW',
        'manage_options',
        'payhow_wc_integration',
        'payhow_display_page_config',
        $icon
    );
}
add_action( 'admin_menu', 'payhow_register_top_level_menu' );

//load page config
function payhow_display_page_config(){
    require __DIR__ . '/page_config.php';
}

//load css
function payhow_load_style() {
    wp_register_style( 'custom_wp_admin_css', plugin_dir_url( __FILE__ ) . 'css/style.css', false, '1.0.0' );
    wp_enqueue_style( 'custom_wp_admin_css' );
}
add_action( 'admin_enqueue_scripts', 'payhow_load_style' );

//add link to page settings
function payhow_link_settings( $links ) {

    $links = array_merge( array(
        '<a href="' . esc_url( admin_url( '?page=payhow_wc_integration' ) ) . '">' . __( 'Configurações', '' ) . '</a>'
    ), $links );

    return $links;

}
add_action( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'payhow_link_settings' );

//valid user logon
function payhow_validUserLogon(){
	$user = wp_get_current_user();
	if(empty($user->user_login)){
		setcookie('wp_user_logged_in', 'null', time() + 31556926, '/');
		$_COOKIE['wp_user_logged_in'] = 'null';
		
		//Redirect to checkout
		setcookie('wp_user_require_logon_checkout', 1, time() + 31556926, '/');
		$_COOKIE['wp_user_require_logon_checkout'] = 1;
	
	}else{
		setcookie('wp_user_logged_in', $user->user_login, time() + 31556926, '/');
		$_COOKIE['wp_user_logged_in'] = $user->user_login;
			
	}
	
}

//function erase cart after sales
function payhow_woocommerce_clear_cart_url() {
	$user = wp_get_current_user();
	if ( isset( $_GET['sales-ok'] ) ) {
		global $woocommerce;
		$woocommerce->cart->empty_cart();
	}
	
}

//function auto redirect to checkout valid new user
function payhow_auto_redirect_checkout(){
	$user = wp_get_current_user();
	$allowed_roles = array('editor', 'administrator', 'author');
	if(($_COOKIE['wp_user_require_logon_checkout'] == 1)&&(!empty($user->user_login))&&(!array_intersect($allowed_roles, $user->roles))){
		setcookie('wp_user_require_logon_checkout', 'null', time() + 31556926, '/');
		$_COOKIE['wp_user_require_logon_checkout'] = 'null';
		wp_redirect( wc_get_checkout_url() );
		exit();
	}
	
}

add_action( 'init', 'payhow_woocommerce_clear_cart_url' );
add_action( 'init', 'payhow_auto_redirect_checkout' );

?>
