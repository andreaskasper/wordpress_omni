<style>
@import url(https://library.goo1.de/fontawesome/5/css/all.min.css);
</style>
<h1>goo1 Omni Einstellungen</h1>
<hr class="wp-header-end">


<style>
#list01 th { text-align: left; }
</style>
<?php

echo('<table id="list01">');
echo('<tr><th>Cloudflare:</th>');
echo('<td>');
if (!empty($_SERVER["HTTP_CF_RAY"])) echo('<span style="color:#080;"><i class="fas fa-check"></i> Sie benutzen Cloudflare</span>'); else echo('<span class="text-muted"><i class="far fa-ellipsis-h"></i> Cloudflare wurde nicht als CDN gefunden.</span>');
echo('</td></tr>');

echo('<tr><th>Two Factor Plugin:</th>');
echo('<td>');
if (is_plugin_active("two-factor/two-factor.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Two-Factor Plugin ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Two Factor Plugin wurde nicht gefunden.      <a class="" href="'.url_install_plugin("two-factor").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');


if (is_plugin_active("elementor/elementor.php")) {
echo('<tr><th>Elementor:</th>');
echo('<td>');
if (is_plugin_active("elementor-pro/elementor-pro.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Elementor-Pro ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Elementor-Pro fehlt      <a class="" href="'.url_install_plugin("elementor-pro").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');
}

echo('<tr><th>ManageWP Worker:</th>');
echo('<td>');
if (is_plugin_active("worker/init.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> ManageWP Worker ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> ManageWP Worker wurde nicht gefunden.      <a class="" href="'.url_install_plugin("worker").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');



echo('<tr><td colspan="2"><b>Empfehlenswerte Plugins:</b></td></tr>');



echo('<tr><th>Credit Tracker:</th>');
echo('<td>');
if (is_plugin_active("credit-tracker/credit-tracker.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Credit Tracker ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Credit Tracker Plugin wurde nicht gefunden.      <a class="" href="'.url_install_plugin("credit-tracker").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');

echo('<tr><th>Contextual Related Posts:</th>');
echo('<td>');
if (is_plugin_active("contextual-related-posts/contextual-related-posts.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Contextual Related Posts ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Contextual Related Posts Plugin wurde nicht gefunden.      <a class="" href="'.url_install_plugin("contextual-related-posts").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');

echo('<tr><th>WP Admin UI Customize:</th>');
echo('<td>');
if (is_plugin_active("wp-admin-ui-customize/wp-admin-ui-customize.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> WP Admin UI Customiz ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> WP Admin UI Customiz wurde nicht gefunden.      <a class="" href="'.url_install_plugin("wp-admin-ui-customize").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');

echo('</table>');


echo('<div class="notice notice-warning" style="margin-top: 100px;"><i class="fas fa-user-hard-hat fa-2x" style="float:left; margin: 0.3rem 0.5rem 00rem 0;"></i><p>TODO: Noch ein paar weitere Abfragen und Features.</p></div>');

exit;

function url_install_plugin(string $slug) : string {
    $action = 'install-plugin';
    return wp_nonce_url(
        add_query_arg(
            array(
                'action' => $action,
                'plugin' => $slug
            ),
            admin_url( 'update.php' )
        ),
        $action.'_'.$slug
    );
}