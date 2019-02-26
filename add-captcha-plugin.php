<?php
/**
* Plugin Name: Add Captcha
* Plugin URI: http://wp.test/plugin
* Description: This plugin is used to add math captcha in the wp-login page
* Version: 1.0
* Author: Saswati
* Author URI: http://wp.test/
* Text Domain: addcaptcha-plugin
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


define( 'ADD_CAPTCHA_URL', plugins_url( '', __FILE__ ) );
define( 'ADD_CAPTCHA_PATH', plugin_dir_path( __FILE__ ) );

class AddCaptchaPlugin {

     /**
     * registering the actions
     */
    function register() {
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
        add_action( 'login_form', array( $this, 'addedToLogin' ) );
        add_filter( 'authenticate', array( $this, 'customAuthenticate' ), 10, 3 );

    }


    /**
     * Activation.
     */
    public function activation() {

        flush_rewrite_rules();
    }

    /**
     * Deactivation.
     */
    public function deactivation() {

        flush_rewrite_rules();
    }

    /**
     * enqueuing the css file.
     */
    public function enqueue() {

        wp_enqueue_style( 'captcha-css', ADD_CAPTCHA_URL.'/assests/css/style.css', array(), false, 'all' );
    
    }

    /**
     * add the captcha part to the login page
     */
    public function addedToLogin() {

        session_start();

        $digit1 = mt_rand( 1, 20 );
        $digit2 = mt_rand( 1, 20 );
        if( mt_rand( 0, 1 ) === 1 ) :
            $math = "$digit1 + $digit2";
            $_SESSION['answer'] = $digit1 + $digit2;
        else :
            $math = "$digit1 - $digit2";
            $_SESSION['answer'] = $digit1 - $digit2;
        endif;
        ?>
            <p>
                <label for="answer">Enter the captcha value</label><br>
                <?php echo $math; ?> = <input name="answer" type="text" />
            </p>
        <?php
    }

     /**
     * login the user after validating the captcha answer
     */
    public function customAuthenticate( $user, $username ) {

        session_start();

        //Get POSTED value
        $formValue = $_POST['answer'];

        //Get user object
        $user = get_user_by( 'login', $username );

        //Get stored value
        $storedValue = $_SESSION['answer'];

        if ( !$user || empty($formValue) || $formValue != $storedValue ) :
            //User note found, or no value entered or doesn't match stored value - don't proceed.
                remove_action( 'authenticate', 'wp_authenticate_username_password', 20 );
                remove_action( 'authenticate', 'wp_authenticate_email_password', 20 ); 

            //Create an error to return to user
                return new WP_Error( 'denied', __( "<strong>ERROR</strong>: You're unique identifier was invalid." ) );
        endif;

        //Make sure you return null 
        return null;
    }
}

if ( class_exists( 'AddCaptchaPlugin' ) ) :

    $addCaptchaPlugin = new AddCaptchaPlugin();
    $addCaptchaPlugin->register();
    
endif;

register_activation_hook( __FILE__, array( $addCaptchaPlugin, 'activation' ) );
register_deactivation_hook( __FILE__, array( $addCaptchaPlugin, 'deactivation' ) );