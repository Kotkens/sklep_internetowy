<?php
/**
 * Debug URLs for login/registration
 * Usuń ten plik po naprawie problemu
 */

if (!defined('ABSPATH')) {
    exit;
}

echo "<h2>🔧 Debug Login URLs</h2>";

echo "<h3>WordPress URLs:</h3>";
echo "<ul>";
echo "<li><strong>Site URL:</strong> " . site_url() . "</li>";
echo "<li><strong>Home URL:</strong> " . home_url() . "</li>";
echo "<li><strong>Login URL:</strong> " . wp_login_url() . "</li>";
echo "<li><strong>Lost Password URL:</strong> " . wp_lostpassword_url() . "</li>";
echo "<li><strong>Registration URL:</strong> " . wp_registration_url() . "</li>";
echo "</ul>";

if (class_exists('WooCommerce')) {
    echo "<h3>WooCommerce URLs:</h3>";
    echo "<ul>";
    echo "<li><strong>My Account URL:</strong> " . wc_get_page_permalink('myaccount') . "</li>";
    echo "<li><strong>Registration enabled:</strong> " . (get_option('woocommerce_enable_myaccount_registration') === 'yes' ? 'YES' : 'NO') . "</li>";
    echo "<li><strong>My Account Page ID:</strong> " . get_option('woocommerce_myaccount_page_id') . "</li>";
    $page = get_page(get_option('woocommerce_myaccount_page_id'));
    if ($page) {
        echo "<li><strong>Page exists:</strong> YES (" . $page->post_title . ")</li>";
        echo "<li><strong>Page slug:</strong> " . $page->post_name . "</li>";
    } else {
        echo "<li><strong>Page exists:</strong> NO</li>";
    }
    echo "</ul>";
}

echo "<h3>Server Info:</h3>";
echo "<ul>";
echo "<li><strong>HTTP_HOST:</strong> " . $_SERVER['HTTP_HOST'] . "</li>";
echo "<li><strong>REQUEST_URI:</strong> " . $_SERVER['REQUEST_URI'] . "</li>";
echo "<li><strong>Permalink Structure:</strong> " . get_option('permalink_structure') . "</li>";
echo "<li><strong>WordPress Debug:</strong> " . (WP_DEBUG ? 'ON' : 'OFF') . "</li>";
echo "</ul>";

echo "<h3>Test Links:</h3>";
echo "<ul>";
echo "<li><a href='" . wp_lostpassword_url() . "' target='_blank'>Test Lost Password URL</a></li>";
echo "<li><a href='" . wp_registration_url() . "' target='_blank'>Test Registration URL</a></li>";
if (class_exists('WooCommerce')) {
    echo "<li><a href='" . wc_get_page_permalink('myaccount') . "' target='_blank'>Test My Account URL</a></li>";
    echo "<li><a href='" . wc_get_page_permalink('myaccount') . "?action=register' target='_blank'>Test My Account Registration</a></li>";
}
echo "</ul>";

echo "<p><small>Aby wyświetlić ten debug, dodaj <code>?debug_login=1</code> do URL strony logowania</small></p>";
?>
