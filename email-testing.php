<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Email Testing
        <!-- <span class="title-count">20</span> -->
    </h1>
    <a href="https://wordpress.org/plugins/woo-email-testing/" target="_blank" class="page-title-action">Learn More</a>
    <hr class="wp-header-end">

    <br class="clear">

    <div class="update-nag notice notice-success is-dismissible">
        <p><strong>Looking for the store emails settings? It can now be found in the section <a href="/wp-admin/admin.php?page=wc-settings&tab=email">Settings > Emails</a>.</strong></p>
    </div>

    <div class="theme-browser rendered">

        <?php 

        // update options if POST	
        wet_update_testing_email_options();

        // get option values
        $testing_email_options = wet_get_testing_email_options();

        ?>       


        <h3>Settings</h3>

        <form method="post" action="">
            <div class="testing">
                <div class="alignleft actions">
                    <label for="wc_email_testing_email"><strong>Email</strong> (where to send)</label>
                    <br/>
                    <input id="wc_email_testing_email" type="text" value="<?php echo $testing_email_options['wc_email_testing_email']; ?>" name="wc_email_testing_email">
                </div>

                <div class="alignleft actions">
                    <label for="wc_email_testing_order_id"><strong>Orders</strong> (what to send)</label>	
                    <br/>				
                    <?php echo $order_id_select = wet_get_order_id_select_field( $testing_email_options['wc_email_testing_order_id'] ); ?>						
                </div>

                <?php wp_nonce_field( 'wet_update_form', 'nonce' ); ?>
                <div class="alignleft actions nolabel">
                    <input id="submit" class="button action" type="submit" value="Save" name="submit">
                </div>
            </div>
        </form>

        <hr/>

        <h3>Testing</h3>
        <p>for WooCommerce emails.</p>

        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
            <div class="testing">
                <div class="alignleft actions">
                    <label for="EmailSelectId"><strong>Select Email</strong></label>	
                    <br/>
                    <select id="EmailSelectId" name="EmailSelect">
                        <?php
                        global $wet_testing_email_class, $wet_testing_email_class_additional;
                        $site_url = site_url();

                        // WC Emails
                        foreach($wet_testing_email_class as $class=>$name){
                        ?>
                        <option value="<?php echo $class; ?>"><?php echo $name; ?></option>
                        <?php
                        }
                        // WC Additional Emails
                        foreach($wet_testing_email_class_additional as $class=>$name){
                        ?>
                        <option value="<?php echo $class; ?>"><?php echo $name; ?></option>
                        <?php
                        }
                        ?>
                    </select>
                </div>                
                <div class="alignleft actions nolabel">
                    <button class="button action" type="submit" name="action" value="wet_email_preview">Preview Email</button>
                    <button class="button button-primary action" type="submit" formaction="<?php echo esc_url( admin_url('admin-post.php') ); ?>" name="action" value="wet_email_send">Send Email</button>
                </div>
            </div>
        </form>



    </div>
</div>