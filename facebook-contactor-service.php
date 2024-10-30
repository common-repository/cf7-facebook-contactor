<?php


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// if $form_data[0]['fbenabled'] == "on" .... atunci da, integram facebook pe acest form                                                             
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



if ( !defined( 'ABSPATH' ) ) {
   exit; // Exit if accessed directly
}


function cf7fb_contactor_css_and_js() 
{
	//wp_deregister_script('jquery');
         
	wp_register_style('cf7fb_contactor_css_and_js', CF7FB_CONTACTOR_URL . 'assets/css/facebook-btn.css', CF7FB_CONTACTOR_VERSION, true);
	wp_register_script('cf7fb_contactor_css_and_js', CF7FB_CONTACTOR_URL . 'assets/js/fb-contactor.js', array( 'jquery' ), CF7FB_CONTACTOR_VERSION, true);
	
    	wp_enqueue_style('cf7fb_contactor_css_and_js');
    	wp_enqueue_script('cf7fb_contactor_css_and_js');
    	
	wp_localize_script( 'cf7fb_contactor_css_and_js', 'ajaxchatlive', array( 
           'ajax_url' => admin_url( 'admin-ajax.php' )
        ) ); // this will initialize the ajaxurl global parameter, otherwise script will fail in frontend.
}

if (!isset($new_fb_settings['fb_load_style'])) $new_fb_settings['fb_load_style'] = 1;
if ($new_fb_settings['fb_load_style']) {
    	add_action('wp_enqueue_scripts', 'cf7fb_contactor_css_and_js');
    	add_action('admin_enqueue_scripts', 'cf7fb_contactor_css_and_js');
}

function cf7fb_add_facebook_contact_tag_handler( $tag ) {
   	$tag = new WPCF7_FormTag( $tag );
   	$title = (string) reset( $tag->values );
   	if ($title == "") {
   		$title = "Contact using Facebook";
   	}
   	
   	if ( $contact_form = WPCF7_ContactForm::get_current() ) 
   	{
		$formId = $contact_form->shortcode_attr('id');
		$form_data = get_post_meta( $formId, 'cf7_fb_settings' );
				
		if ($form_data[0]['fbenabled'] == "on") {
			$appid = get_option( 'fb_app_id');
        		$appsecret = get_option( 'fb_app_secret');
        	
			$fbScript = "<script>
  window.fbAsyncInit = function() {
    FB.init({
      appId            : '$appid',
      autoLogAppEvents : true,
      xfbml            : true,
      version          : 'v2.11'
    });
  };

  (function(d, s, id){
     var js, fjs = d.getElementsByTagName(s)[0];
     if (d.getElementById(id)) {return;}
     js = d.createElement(s); js.id = id;
     js.src = 'https://connect.facebook.net/en_US/sdk.js';
     fjs.parentNode.insertBefore(js, fjs);
   }(document, 'script', 'facebook-jssdk'));
</script>";
		
			return $fbScript . '<div class="new-fb-btn new-fb-1 new-fb-default-anim"><input type="hidden" id="cfformid" value="' . $formId . '" /><div id="contact-with-facebook" class="new-fb-1-1"><div class="new-fb-1-1-1">' . $title . '</div></div></div>';
		}
		else {
			return ''; // return EMPTY in case facebook is disabled for the form.
		}
	}
	else
	{
		return  '<p style="color: red;">Error configuring the FORM.</p>';
	}
   	     	
	return '<p style="color: red;">This error message should now show up :) Please contact developer (sebi@onofrei.org) should you see this message.</p>';
      
      
   }


/**
 * CF7FB_Contactor_Service Class
 *
 * @since 1.0
 */
class CF7FB_Contactor_Service {
    /**
    *  Set things up.
    *  @since 1.0
    */
   public function __construct() { 
   	
      // this verifies for Facebook api key/secret validadity
      add_action( 'wp_ajax_cf7fb_contactor_verify_integation', array( $this, 'cf7fb_contactor_verify_integation' ) );
                                                                                                                                                                 
      // this action will submit the form using the facebook button (works for users, because of 'wp_ajax_nopriv_')
    	// add_action( 'wp_ajax_nopriv_contact_via_facebook_now', array( $this, 'contact_via_facebook_now' ) );
    	// add_action( 'wp_ajax_contact_via_facebook_now', array( $this, 'contact_via_facebook_now' ) );
     
      // Adds new tab to contact form 7 editors panel
      add_filter( 'wpcf7_editor_panels', array( $this, 'cf7_fbcontactor_editor_panels' ) );
     
     
      // method callsed in order to save our boolean parameter to DB
      add_action('wpcf7_after_save', array( $this, 'save_fbcontactor_settings' ) );
     
      
      // injects in CF7 function, for adding the Facebook button in it, for fast contact via Facebook.
      add_action( 'wpcf7_init', array($this, 'cf7_add_facebook_contact_tag'), 1); 
      
   }
     
   public function cf7_add_facebook_contact_tag() 
   { 
   	wpcf7_add_form_tag( 'facebook', 'cf7fb_add_facebook_contact_tag_handler', array( 'name-attr' => true ) );
   }
   
   
   
   /**
    * AJAX function - verifies the appid/secret eventually
    *
    * @since 1.0
    */
   public function cf7fb_contactor_verify_integation() {
      // nonce check
      check_ajax_referer( 'gs-ajax-nonce', 'security' );

      /* sanitize incoming data */
      $appid = sanitize_text_field( $_POST[ "appid" ] );
      $appsecret = sanitize_text_field( $_POST[ "appsecret" ] );
      
      update_option( 'fb_app_id', $appid );
      update_option( 'fb_app_secret', $appsecret );
      
      wp_send_json_success();      
   }
   
   
   
   /**
    * Add new tab to contact form 7 editors panel
    * @since 1.0
    */
   public function cf7_fbcontactor_editor_panels( $panels ) {
      $panels[ 'fbcontactor' ] = array(
          'title' => __( 'Facebook Contactor', 'contact-form-7' ),
          'callback' => array( $this, 'cf7_editor_panel_facebook_contactor' )
      );

      return $panels;
   }
   
   
   
   /*
    * Set Facebook Contactor settings with contact form
    * @since 1.0
    */
   public function save_fbcontactor_settings( $post ) {
     update_post_meta( $post->id(), 'cf7_fb_settings', $_POST['cf7-fb'] );
   }

   /*
    * Facebook Contactor settings page  
    * @since 1.0
    */
   public function cf7_editor_panel_facebook_contactor( $post ) { 
         $form_id = sanitize_text_field( $_GET['post'] ); 
         $form_data = get_post_meta( $form_id, 'cf7_fb_settings' );
      ?>
         <div class="fb-fields">
            <h2><span><?php echo esc_html( __( 'Facebook settings', 'fbcontactor' ) ); ?></span></h2>
            <p>
               <label>
               		<input type="checkbox" id="fb-enabled" name="cf7-fb[fbenabled]" <?php echo ( isset ( $form_data[0]['fbenabled'] ) ) ? 'checked="checked"' : ''; ?>">
               		<?php echo esc_html( __( 'Enable Facebook fast contact for this contact form?', 'fbcontactor' ) ); ?>
               </label>
            </p>
            
            <p>   
            <?php if (isset ( $form_data[0]['fbenabled'] ) ): ?>
            	<span style="color:red; font-height: 16px;">Ok, now please add a <b>[facebook "Text on the button"]</b> code in the contact-form and also a <b>[hidden your-fb id:your-fb]</b> code, 
            	where you want the facebook button to show-up. You can also include waterver HTML you want in there. </span>
            	<span>your-fb hidden element will be filled-in automatically with the user's facebook page, while the [facebook] shortcode 
            	will be replaced with a facebook button, for retrieving data from Facebook.</span>
            <?php endif; ?>
            &nbsp;
            </p>
         </div>
   <?php }
      
}

$fb_contactor_service = new CF7FB_Contactor_Service();


