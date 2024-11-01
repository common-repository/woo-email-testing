<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<div class="wrap">
    <h1 class="wp-heading-inline">Email Templates
        <span class="title-count">20</span>
    </h1>
    <a href="https://themes.email/woocommerce.html" target="_blank" class="page-title-action" onclick="ga('send', 'event', 'WooCommerce Email Testing', 'WET', 'Learn More');">Learn More</a>
    <hr class="wp-header-end">
    
    <div class="wp-filter">
        <ul class="filter-links">
            <li class=""><a href="#" class="current" aria-current="page">Portfolio</a> </li>
            <li class=""><a href="https://themes.email/woocommerce.html" onclick="ga('send', 'event', 'WooCommerce Email Testing', 'WET', 'About');">About</a> </li>
            <li class=""><a href="https://themes.email/woocommerce.html#documentation" onclick="ga('send', 'event', 'WooCommerce Email Testing', 'WET', 'Documentation');">Documentation</a> </li>
        </ul>
    </div>
    <br class="clear">

    <div class="theme-browser rendered">
        <?php include 'includes/templates.php';?>
    </div>

</div>