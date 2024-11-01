<?php
/*
Plugin Name: WooCommerce Email Testing
Plugin URI: https://wordpress.org/plugins/woo-email-testing/
Description: Preview & Send Emails for WooCommerce. Designing, Developing, and Testing Emails.
Version: 1.1
Author: ThemesEmail
Author URI: https://themes.email/woocommerce.html
Text Domain: woocommerce-email-testing
License: GNU GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.txt

Credits: 
This plugin uses Open Source components. We acknowledge and are grateful to these developers for their contributions to open source.
@functions.php is based on work: RaiserWeb (http://raiserweb.com)
@email-trigger.php is based on work: RaiserWeb (http://raiserweb.com)
*/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    // set email classes for options in select box
    $wet_testing_email_class = array(
        'WC_Email_New_Order'=>'New Order',
        'WC_Email_Customer_Processing_Order'=>'Processing Order',
        'WC_Email_Customer_Completed_Order'=>'Completed Order',
        'WC_Email_Customer_Invoice'=>'Customer Invoice',
        'WC_Email_Customer_Note'=>'Customer Note',
    );

    $wet_testing_email_class_additional = array(
        'WC_Email_Cancelled_Order'=>'Cancelled Order',
        'WC_Email_Failed_Order'=>'Failed Order',
        'WC_Email_Customer_On_Hold_Order'=>'Order On Hold',
        'WC_Email_Customer_Refunded_Order'=>'Refunded Order',
        'WC_Email_Customer_Reset_Password'=>'Reset Password',          
        'WC_Email_Customer_New_Account'=>'New Account',   
    );

    // include plugin files
    include( 'functions.php' );
    include( 'email-trigger.php' );

    if( is_admin() ) {

        // check for extra software 
        function wet_check_extra_software(){
            // get a list of plugins
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            // check if a plugin is installed
            if( is_plugin_active( 'woocommerce/woocommerce.php' ) ){
                $installed_extra_software = 0;
            } else {
                // Not active. Return 1 in notification bubble
                $installed_extra_software = 1;

                // Add banner on email-testing.php page
                add_action( 'admin_notices', 'wet_add_extra_software_banner' );
            }

            return $installed_extra_software;
        }

        // echo banner for extra software
        function wet_add_extra_software_banner(){
            global $pagenow;
            if ( ( 'admin.php' === $pagenow ) && ( 'wc-email-testing' === $_GET['page'] ) ) {
                echo '<div class="notice baner">This is the banner</div>';
            }
        }

        // check if WET is installed
        function wet_installed_email_templates(){
            // check if the file email-styles.php exist.
            $wet_path =  get_template_directory() . '/woocommerce/emails/email-styles.php';

            if(file_exists($wet_path)){
                $installed_email_templates = 0;
            } else {
                $installed_email_templates = 1;

                // Add admin notice on email-templates.php page
                add_action( 'admin_notices', 'wet_notice_install_templates' );
            }

            return $installed_email_templates;
        }

        // Notice MSG for email templates
        function wet_notice_install_templates(){
            global $pagenow;
            if ( ( 'admin.php' === $pagenow ) && ( 'wc-email-templates' === $_GET['page'] ) ) {
                echo    '<div class="update notice notice-success is-dismissible"><p>
                    <strong>Thanks for installing WooCommerce Email Testing. We also recommend using <a href="https://themes.email/woocommerce.html" target="_blank" onclick="ga(\'send\', \'event\', \'WooCommerce Email Testing\', \'WET\', \'Best Selling Templates MSG\');">WooCommerce Email Templates</a>. 2018\'s Best Selling WooCommerce Email Templates on Envato.</strong>
                    </p></div>';
            }
        }

        // add action links at plugins page
        add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'wc_email_testing_action_links' );

        function wc_email_testing_action_links( $links ) {
            $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-email-testing') ) .'">Testing</a>';
            $links[] = '<a href="'. esc_url( get_admin_url(null, 'admin.php?page=wc-email-templates') ) .'">Templates</a>';
            return $links;
        }

        // add submenu page email testing
        add_action('admin_menu', 'add_submenu_page_email_testing');

        function add_submenu_page_email_testing() {
            // NOTIFICATION BUBBLE for extra software (0 = hide the bubble)
            $notification_count = wet_check_extra_software();

            add_submenu_page( 
                'woocommerce', 
                'Email Testing', 
                $notification_count ? sprintf('Email Testing <span class="awaiting-mod update-plugins">%d</span>', $notification_count) : 'Email Testing',
                'manage_options', 
                'wc-email-testing', 
                'submenu_page_email_testing_callback' 
            ); 
        }
        function submenu_page_email_testing_callback() {
            include( 'email-testing.php' );
        }

        // add submenu page email templates
        add_action('admin_menu', 'add_submenu_page_email_templates');

        function add_submenu_page_email_templates() {
            // NOTIFICATION BUBBLE: The number of updated Email Templates (0 = hide the bubble)
            $notification_count = wet_installed_email_templates();

            add_submenu_page( 
                'woocommerce',
                'Email Templates',
                $notification_count ? sprintf('Email Templates <span class="awaiting-mod update-plugins">%d</span>', $notification_count) : 'Email Templates',
                'manage_options', 
                'wc-email-templates', 
                'submenu_page_email_templates_callback' 
            ); 
        }
        function submenu_page_email_templates_callback() {
            include( 'email-templates.php' );
        }

        // handle $_POST request. Preview button. Variable from value email_preview
        add_action( 'admin_post_wet_email_preview', 'wet_email_preview' );

        function wet_email_preview() {
            $EmailSelect = $_POST['EmailSelect'];
            $site_url = site_url();
            $url = $site_url . "/?wc_email_testing=" . $EmailSelect;

            // add a variable for condition Preview/Send
            wp_redirect( $url . "&preview" );
            exit;

        }

        // handle $_POST request. Send button. Variable from value email_send
        add_action( 'admin_post_wet_email_send', 'wet_email_send' );

        function wet_email_send() {
            $EmailSelect = $_POST['EmailSelect'];
            $site_url = site_url();
            $url = $site_url . "/?wc_email_testing=" . $EmailSelect;

            wp_redirect( $url );
            exit;

        }

        // admin notice "email sent"
        if(isset($_GET["emailSent"])) {

            add_action( 'admin_notices', 'wet_notice_email_sent' );

            function wet_notice_email_sent(){
                echo '<div class="update notice notice-success is-dismissible"><p><strong>Email Sent.</strong></p></div>';
            }
            
        }


    }

}