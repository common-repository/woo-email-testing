<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// run the script based on the trigger GET value populated
function wet_email_trigger_init(){
    if ( isset($_GET['wc_email_testing']) && in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	
        if( current_user_can( 'administrator' ) ) {
            add_filter( 'query_vars', 'wet_plugin_add_trigger' );
            function wet_plugin_add_trigger( $vars ) {

                $vars[] = 'wc_email_testing';
                return $vars;

            }	

            add_action( 'template_redirect', 'wet_plugin_trigger_check' );
            function wet_plugin_trigger_check() {

                if( get_query_var( 'wc_email_testing' )  ) {

                    // run the email script based on the wc_email_testing class	
                    run_wet_email_script();

                    exit();

                }
            }
        }

    }
}
add_action( 'init', 'wet_email_trigger_init' );