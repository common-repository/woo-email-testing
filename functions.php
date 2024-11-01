<?php

function run_wet_email_script(){

    global $wet_testing_email_class, $wet_testing_email_class_additional;

    // the email type to send
    $email_class = get_query_var('wc_email_testing');	

    // check type is in array
    if( !array_key_exists( $email_class, $wet_testing_email_class) && !array_key_exists( $email_class, $wet_testing_email_class_additional ) ){
        echo "Invalid email type";
        exit();
    }  

    // assign email address and order id variables
    if( get_option( "wc_email_testing_order_id", false ) == 'latest' ){       
        $wc_email_testing_order_id = '';           
    } else {     
        $wc_email_testing_order_id = get_option( "wc_email_testing_order_id", false );           
    }	

    if( ! $wc_email_testing_order_id ) {		

        // get a valid and latest order_id ( if no order is has been selected )
        global $wpdb;
        $order_id_query = 'SELECT order_id FROM '.$wpdb->prefix.'woocommerce_order_items ORDER BY order_item_id DESC LIMIT 1';
        $order_id = $wpdb->get_results( $order_id_query );

        if( empty( $order_id ) ) {

            echo "No order within your WooCommerce shop. Please create a test order first to test the emails";
            return;

        } else {

            $wc_email_testing_order_id = $order_id[0]->order_id ;

        }

    }        

    $for_filter = strtolower( str_replace( 'WC_Email_', '' , $email_class ) );


    // change email address within order to saved option	
    add_filter( 'woocommerce_email_recipient_'.$for_filter , 'wet_email_recipient_filter_function', 10, 2);
    function wet_email_recipient_filter_function($recipient, $object) {

        if( get_option( "wc_email_testing_email", false ) ) {       
            $wc_email_testing_email = get_option( "wc_email_testing_email", false );           
        } else {    
            $wc_email_testing_email = ""; 
        }              
        $recipient = $wc_email_testing_email;

        return $recipient;
    }

    // change subject link	
    $subject_filter_prefix = 'woocommerce_email_subject_';
    $subject_filter = $for_filter;

    add_filter($subject_filter_prefix.$subject_filter , 'wet_change_admin_email_subject', 1, 2);	 
    function wet_change_admin_email_subject( $subject, $order ) {
        //global $woocommerce;       
        $subject = "Testing: ".$subject;		
        return $subject;
    } 

    // email send toggle
    add_filter('woocommerce_email_enabled_'.$for_filter , 'wet_change_email_enabled', 1, 2);	 
    function wet_change_email_enabled( $enabled, $order ) {
        if( get_option( "wc_email_testing_email", false ) ) {       
            return true;           
        } else {    
            return false;
        }
    }      

    if( isset( $GLOBALS['wc_advanced_notifications'] ) ) {
        unset( $GLOBALS['wc_advanced_notifications'] );
    }

    // load the email classs
    $wc_emails = new WC_Emails( );
    $emails = $wc_emails->get_emails();

    // select the email we want
    $new_email = $emails[ $email_class ];

    // if it gets a variable from Preview button. Else just send the email out.
    if(isset($_GET["preview"])) {

        // Make sure email isn't sent to customer (by Muhammad Usama M.)
        add_filter( 'woocommerce_email_enabled_' . $new_email->id , '__return_false' );
        add_filter( 'woocommerce_email_recipient_' . $new_email->id , '__return_false' );

        if( $for_filter == 'customer_note' ) {
            $new_email->trigger( array( 'order_id'=>$wc_email_testing_order_id ) );
        } else {
            $new_email->trigger( $wc_email_testing_order_id  );     
        }

        // echo the email content for browser 
        echo apply_filters( 'woocommerce_mail_content', $new_email->style_inline( $new_email->get_content_html() ) );

    } else {

        if( $for_filter == 'customer_note' ) {
            $new_email->trigger( array( 'order_id'=>$wc_email_testing_order_id ) );
        } else {
            $new_email->trigger( $wc_email_testing_order_id  );     
        }


        // redirect back on the form after email sent
        // set GET variable "emailSent" for admin notification "Email Sent".
        wp_redirect(admin_url('admin.php?page=wc-email-testing&emailSent'));

    }

}

function wet_get_order_id_select_field( $wc_email_testing_order_id ) {

    global $wpdb;

    $order_id_query = 'SELECT ID as order_id FROM '.$wpdb->prefix . 'posts'.' WHERE post_type = "shop_order" GROUP BY ID ORDER BY ID DESC LIMIT 100';
    $order_id = $wpdb->get_results( $order_id_query  );
    if( empty( $order_id ) ) {

        return "<strong style='color:red;'>No orders to display</strong><br><strong style='color:red;'>Please create at least one order</strong>";

    } else {

        $order_id_select_options = "<option value='latest'>Latest</option>";
        foreach( $order_id as $id ) {
            $order_id_select_options .= "<option value='{$id->order_id}'>#{$id->order_id}</option>";
        }

        $order_id_select_options = str_replace( "value='{$wc_email_testing_order_id}'", "value='{$wc_email_testing_order_id}' selected", $order_id_select_options ); 

        $order_id_select = "<select id='wc_email_testing_order_id' name='wc_email_testing_order_id'>{$order_id_select_options}</select>";

        return $order_id_select;

    }

}

function wet_update_testing_email_options() {

    $updated = false;

    if( isset( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'], 'wet_update_form' ) ) {

        if( isset( $_POST['wc_email_testing_email'] ) ){

            if( $_POST['wc_email_testing_email'] == get_option("wc_email_testing_email") ){
                $updated = true;
                $email = true;                
            } else {

                $result = update_option( "wc_email_testing_email", sanitize_email( $_POST['wc_email_testing_email'] ) );
                if( $result ){
                    $updated = true;
                    $email = true;
                } else {
                    $email = false;
                }

            }		
        } else {

            $result = update_option( "wc_email_testing_email", "" );
            $updated = true;
            $email = true;
        }

        if( isset( $_POST['wc_email_testing_order_id'] )  ){

            $result = update_option( "wc_email_testing_order_id", intval( $_POST['wc_email_testing_order_id'] ) );					
            $updated = true;

        }

        if( !$email ) {

            echo "<div id='message' class='error fade'><p><strong>Your email looks invalid</strong></p></div>";

        } else {
            if( $updated ) {

                echo "<div id='message' class='updated fade'><p><strong>Your settings have been saved.</strong></p></div>";

            }            
        }
    }

    return $updated;

}


function wet_get_testing_email_options() {

    $return = array();

    if( get_option( "wc_email_testing_email", "false" ) ) {

        $return['wc_email_testing_email'] = get_option( "wc_email_testing_email", "" );

    } else {

        $return['wc_email_testing_email'] = '';

    }
    if( get_option( "wc_email_testing_order_id", "false" ) ) {

        $return['wc_email_testing_order_id'] = get_option( "wc_email_testing_order_id", "false" );

    } else {

        $return['wc_email_testing_order_id'] = '';

    }

    return $return;

}


// add custom CSS
function add_css_to_menu_page()
{
    global $pagenow;

    if ( ( 'admin.php' === $pagenow ) && ( 'wc-email-templates' === $_GET['page'] ) || ( 'admin.php' === $pagenow ) && ( 'wc-email-testing' === $_GET['page'] ) ) {
        
        wp_register_style( 'style-css', plugin_dir_url( __FILE__ ) . 'includes/css/style.css', false, '1.0.0' );
        wp_enqueue_style( 'style-css' );
        
    }         
}
add_action( 'admin_enqueue_scripts', 'add_css_to_menu_page' );