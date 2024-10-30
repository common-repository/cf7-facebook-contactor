<?php
/*
  Plugin Name: CF7 Facebook Contactor
  Plugin URI: https://wordpress.org/plugins/cf7-facebook-contactor/
  Description: Submit your Contact Form 7 using data from Facebook profile.
  Version: 1.0
  Author: Sebi O. (theselby)
  Author URI: https://chatlive.ro/
  Text Domain: facebookcf7
 */


if ( ! defined('ABSPATH') ) {
   exit; // Exit if accessed directly
}

// Declare some global constants
define('CF7FB_CONTACTOR_VERSION', '1.0');
define('CF7FB_CONTACTOR_DB_VERSION', '1.0');
define('CF7FB_CONTACTOR_ROOT', dirname(__FILE__));
define('CF7FB_CONTACTOR_URL', plugins_url('/', __FILE__));
define('CF7FB_CONTACTOR_BASE_FILE', basename(dirname(__FILE__)) . '/facebook-contactor.php');
define('CF7FB_CONTACTOR_BASE_NAME', plugin_basename(__FILE__));
define('CF7FB_CONTACTOR_PATH', plugin_dir_path(__FILE__)); //use for include files to other files
define('CF7FB_CONTACTOR_PRODUCT_NAME', 'Facebook Contactor');
define('CF7FB_CONTACTOR_CURRENT_THEME', get_stylesheet_directory());
load_plugin_textdomain('fbcontactor', false, basename(dirname(__FILE__)) . '/languages');

/*
 * include utility classes
 */
// now the utility
if ( ! class_exists('CF7FB_Contactor_Utility') ) {
   include( CF7FB_CONTACTOR_ROOT . '/includes/class-fb-utility.php' );
}
// and now, the core.
if ( ! class_exists('CF7FB_Contactor_Service') ) {
   //the next line will include the file that contains all the core-logic of dealing with the Facebook submit/etc
   include( CF7FB_CONTACTOR_ROOT . '/facebook-contactor-service.php' );
}







/*
 * Main FB cotactor class
 * @class CF7FB_Contactor_Init
 * @since 1.0
 */

class CF7FB_Contactor_Init {

   /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() {
      //run on activation of plugin
      register_activation_hook(__FILE__, array($this, 'cf7fb_contactor_activate'));

      //run on deactivation of plugin
      register_deactivation_hook(__FILE__, array($this, 'cf7fb_contactor_deactivate'));

      //run on uninstall
      register_uninstall_hook(__FILE__, array('CF7FB_Contactor_Init', 'cf7fb_contactor_uninstall'));

      // validate is contact form 7 plugin exist
      add_action('admin_init', array($this, 'validate_parent_plugin_exists'));

      // register admin menu under "Contact" > "Integration"
      add_action('admin_menu', array($this, 'register_fb_menu_pages'));

      // load the js and css files
      add_action('init', array($this, 'load_css_and_js_files'));

      // Add custom link for our plugin
      add_filter('plugin_action_links_' . CF7FB_CONTACTOR_BASE_NAME, array($this, 'cf7fb_contactor_plugin_action_links'));
   }

   public function load_css_and_js_files() {
      add_action('admin_print_styles', array($this, 'add_css_files'));
      add_action('admin_print_scripts', array($this, 'add_js_files'));
   }

   /**
    * enqueue CSS files
    * @since 1.0
    */
   public function add_css_files() {
      if ( is_admin() && ( isset($_GET['page']) && ( ( $_GET['page'] == 'wpcf7-new' ) || ( $_GET['page'] == 'wpcf7-facebook-contactor-config' ) || ( $_GET['page'] == 'wpcf7' ) ) ) ) {
         wp_enqueue_style('fb-contactor-css', CF7FB_CONTACTOR_URL . 'assets/css/gs-connector.css', CF7FB_CONTACTOR_VERSION, true);
      }
      
      wp_enqueue_style('fb-contactor-button-css', CF7FB_CONTACTOR_URL . 'assets/css/facebook-btn.css', CF7FB_CONTACTOR_VERSION, true);
   }

   /**
    * enqueue JS files
    * @since 1.0
    */
   public function add_js_files() {
      if ( is_admin() && ( isset($_GET['page']) && ( ( $_GET['page'] == 'wpcf7-new' ) || ( $_GET['page'] == 'wpcf7-facebook-contactor-config' ) ) ) ) {      
         wp_enqueue_script('fb-contactor-js', CF7FB_CONTACTOR_URL . 'assets/js/fb-contactor.js', CF7FB_CONTACTOR_VERSION, true);
      }
   }
   
   

   /**
    * Do things on plugin activation
    * @since 1.0
    */
   public function cf7fb_contactor_activate() {
      global $wpdb;
      $this->run_on_activation();
      if ( function_exists('is_multisite') && is_multisite() ) {
         // check if it is a network activation - if so, run the activation function for each blog id
         if ( $network_wide ) {
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");
            foreach ( $blogids as $blog_id ) {
               switch_to_blog($blog_id);
               $this->run_for_site();
               restore_current_blog();
            }
            return;
         }
      }

      // for non-network sites only
      $this->run_for_site();
   }

   /**
    * deactivate the plugin
    * @since 1.0
    */
   public function cf7fb_contactor_deactivate() {
      
   }

   /**
    *  Runs on plugin uninstall.
    *  a static class method or function can be used in an uninstall hook
    *
    *  @since 1.5
    */
   public static function cf7fb_contactor_uninstall() {
      global $wpdb;
      CF7FB_Contactor_Init::run_on_uninstall();
      if ( function_exists('is_multisite') && is_multisite() ) {
         //Get all blog ids; foreach of them call the uninstall procedure
         $blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->base_prefix}blogs");

         //Get all blog ids; foreach them and call the install procedure on each of them if the plugin table is found
         foreach ( $blog_ids as $blog_id ) {
            switch_to_blog($blog_id);
            CF7FB_Contactor_Init::delete_for_site();
            restore_current_blog();
         }
         return;
      }
      CF7FB_Contactor_Init::delete_for_site();
   }

   /**
    * Validate parent Plugin Contact Form 7 exist and activated
    * @access public
    * @since 1.0
    */
   public function validate_parent_plugin_exists() {
      $plugin = plugin_basename(__FILE__);
      if ( ( ! is_plugin_active('contact-form-7/wp-contact-form-7.php') ) && ( ! is_plugin_active('facebook-contactor/facebook-contactor') ) ) {
         add_action('admin_notices', array($this, 'contact_form_7_missing_notice'));
         add_action( 'network_admin_notices',  array( $this, 'contact_form_7_missing_notice') );
         deactivate_plugins($plugin);
         if ( isset($_GET['activate']) ) {
            // Do not sanitize it because we are destroying the variables from URL
            unset($_GET['activate']);
         }
      }
   }

   /**
    * If Contact Form 7 plugin is not installed or activated then throw the error
    *
    * @access public
    * @return mixed error_message, an array containing the error message
    *
    * @since 1.0 initial version
    */
   public function contact_form_7_missing_notice() {
      $plugin_error = Fb_Contactor_Utility::instance()->admin_notice(array(
          'type' => 'error',
          'message' => 'Facebook Contactor Add-on requires Contact Form 7 plugin to be installed and activated.'
              ));
      echo $plugin_error;
   }

   /**
    * Create/Register menu items for the plugin.
    * @since 1.0
    */
   public function register_fb_menu_pages() {
      $current_role = Fb_Contactor_Utility::instance()->get_current_user_role();
      add_submenu_page('wpcf7', __('Facebook Contactor', 'fbcontactor'), __('Facebook Contactor', 'fbcontactor'), $current_role, 'wpcf7-facebook-contactor-config', array($this, 'facebook_contactor_config'));
   }

   /**
    * Facebook Contactor page action.
    * This method is called when the menu item "Facebook Contactor" is clicked.
    *
    * @since 1.0
    */
   public function facebook_contactor_config() {
      ?>
      <div class="wrap gs-form"> 
         <h1><?php echo esc_html(__('Contact Form 7 - Facebook Contact Integration', 'fbcontactor')); ?></h1>
         <div class="card" id="googlesheet">
            <h2 class="title"><?php echo esc_html(__('Facebook Contactor', 'fbcontactor')); ?></h2>
            
            <br class="clear">
            
            <?php if (!function_exists('curl_init')) { ?>
            <div class="error"><strong><p class="gs-alert">Facebook needs the CURL PHP extension. Contact your server adminsitrator!</p></strong></div>
            <?php } else {
            	$version = curl_version();
            	$ssl_supported = ($version['features'] & CURL_VERSION_SSL);
            	if (!$ssl_supported) {
                ?>
                	<div class="error"><strong><p class="gs-alert">Protocol https not supported or disabled in libcurl. Contact your server adminsitrator!</p></strong></div>
                <?php } }
        	if (!function_exists('json_decode')) { ?>
            	<div class="error"><strong><p class="gs-alert">Facebook needs the JSON PHP extension. Contact your server adminsitrator!</p></strong></div>
            <?php } ?>
            
            <p>
  			  <?php _e('<ol><li><a href="https://developers.facebook.com/apps/" target="_blank">Create a facebook app!</a></li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Don\'t choose from the listed options, but click on <b>advanced setup</b> in the bottom.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Choose an <b>app name</b>, and a <b>category</b>, then click on <b>Create App ID</b>.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Pass the security check.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Go to the <b>Settings</b> of the application.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Click on <b>+ Add Platform</b>, and choose <b>Website</b>.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Give your website\'s address at the <b>Site URL</b> field with: <b>' . get_option('siteurl') . '</b></li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Give a <b>Contact Email</b> and click on <b>Save Changes</b>.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Go to <b>Status & Review</b>, and change the availability for the general public to <b>YES</b>.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li>Go back to the <b>Settings</b>, and copy the <b>App ID</b>, and <b>APP Secret</b>, which you have to copy and paste below.</li>', 'nextend-facebook-connect'); ?>
			  <?php _e('<li><b>Save changes!</b></li></ol>', 'nextend-facebook-connect'); ?>
  		</p>

            <br class="clear">

            <div class="inside">
               
               <p>
                  <label><?php echo esc_html(__('Facebook App ID', 'fbcontactor')); ?></label>
                  <input type="text" name="fb-app-id" id="fb-app-id" value="<?php echo esc_html(get_option('fb_app_id')); ?>" placeholder="<?php if ( get_option('fb_app_id') !== '' ) { echo esc_html(__('Currently inactive', 'fbcontactor')); } ?>"/>
               </p>
               
               <p>
                  <label><?php echo esc_html(__('Facebook App Secret', 'fbcontactor')); ?></label>
                  <input type="text" name="fb-app-secret" id="fb-app-secret" value="<?php echo esc_html(get_option('fb_app_secret')); ?>" placeholder="<?php if ( get_option('fb_app_secret') !== '' ) { echo esc_html(__('Currently inactive', 'fbcontactor')); } ?>"/>
               </p>
               
               <p> 
               		<a href="https://developers.facebook.com/apps/" target="_blank" class="button">Open facebook developer to retrieve Facebook app ID and Secret.</a>
               </p>

               <p> 
               	<input type="button" name="save-fb-contactor" id="save-fb-contactor" value="<?php _e('Save', 'fbcontactor'); ?>" class="button button-primary" />
               	<span class="loading-sign">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>
               </p>

               <p>
                  <label><?php echo esc_html(__('Debug Log', 'fbcontactor')); ?></label>
                  <label><a href= "<?php echo plugins_url('/logs/log.txt', __FILE__); ?>" target="_blank" class="debug-view" >View</a></label>
               </p>
               <p id="gs-validation-message"></p>
               <!-- set nonce -->
               <input type="hidden" name="gs-ajax-nonce" id="gs-ajax-nonce" value="<?php echo wp_create_nonce('gs-ajax-nonce'); ?>" />

            </div>
         </div>
      </div>
      <?php
   }

   /**
    * called on upgrade. 
    * checks the current version and applies the necessary upgrades from that version onwards
    * @since 1.0
    */
   public function run_on_upgrade() {
      $plugin_options = get_site_option('facebook_contact_info');

      // update the version value
      $facebook_contact_info = array(
          'version' => CF7FB_CONTACTOR_VERSION,
          'db_version' => CF7FB_CONTACTOR_DB_VERSION
      );
      update_site_option('facebook_contact_info', $facebook_contact_info);
   }

   /**
    * Add custom link for the plugin beside activate/deactivate links
    * @param array $links Array of links to display below our plugin listing.
    * @return array Amended array of links.    * 
    * @since 1.5
    */
   public function cf7fb_contactor_plugin_action_links( $links ) {
      // We shouldn't encourage editing our plugin directly.
      unset($links['edit']);

      // Add our custom links to the returned array value.
      return array_merge(array('<a href="' . admin_url('admin.php?page=wpcf7-facebook-contactor-config') . '">' . __('Settings', 'oasisworkflow') . '</a>' ), $links);
   }

   /**
    * Called on activation.
    * Creates the site_options (required for all the sites in a multi-site setup)
    * If the current version doesn't match the new version, runs the upgrade
    * @since 1.0
    */
   private function run_on_activation() {
      $plugin_options = get_site_option('facebook_contact_info');
      if ( false === $plugin_options ) {
         $facebook_contact_info = array(
             'version' => CF7FB_CONTACTOR_VERSION,
             'db_version' => CF7FB_CONTACTOR_DB_VERSION
         );
         update_site_option('facebook_contact_info', $facebook_contact_info);
      } else if ( CF7FB_CONTACTOR_DB_VERSION != $plugin_options['version'] ) {
         $this->run_on_upgrade();
      }
   }

   /**
    * Called on activation.
    * Creates the options and DB (required by per site)
    * @since 1.0
    */
   private function run_for_site() {
      if ( ! get_option('fb_app_id') ) {
         update_option('fb_app_id', '');
      }
      
      if ( ! get_option('fb_app_secret') ) {
         update_option('fb_app_secret', '');
      }
   }

   /**
    * Called on uninstall - deletes site_options
    *
    * @since 1.5
    */
   private static function run_on_uninstall() {
      if ( ! defined('ABSPATH') && ! defined('WP_UNINSTALL_PLUGIN') ) exit();

      delete_site_option('facebook_contact_info');
   }

   /**
    * Called on uninstall - deletes site specific options
    *
    * @since 1.5
    */
   private static function delete_for_site() {
      delete_option('fb_app_id');
      delete_option('fb_app_secret');
      
      delete_post_meta_by_key( 'cf7_fb_settings' );
   }

}

// Initialize the google sheet connector class
$init = new CF7FB_Contactor_Init();

