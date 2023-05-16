<?php

use \plugins\goo1\omni\config;

if (!empty($_POST["act"]) AND $_POST["act"] == "save") {
    print_r($_POST);
    if (!empty($_POST["cloudflare_countriesadmin"])) {
        $a = preg_replace("@[^A-Z,]+@","", strtoupper($_POST["cloudflare_countriesadmin"]));
        config::set("cloudflare_admin_country", $a);
    }
    $is_saved = true;
}





?><style>
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
if (!empty($_SERVER["HTTP_CF_RAY"])) echo('<span style="color:#080;"><i class="fas fa-check"></i> Traffic is routed through Cloudflare</span>'); else echo('<span class="text-muted"><i class="far fa-ellipsis-h"></i> Cloudflare not found as CDN</span>');
echo('</td></tr>');

/*echo('<tr><th>Two Factor Plugin:</th>');
echo('<td>');
if (is_plugin_active("two-factor/two-factor.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin wurde nicht gefunden.      <a class="" href="'.url_install_plugin("two-factor").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');*/

echo('<tr><td colspan="2"><b>Plugins:</b></td></tr>');


if (is_plugin_active("elementor/elementor.php")) {
echo('<tr><th>Elementor Pro:</th>');
echo('<td>');
if (is_plugin_active("elementor-pro/elementor-pro.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin is installed</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin missing      <a class="" href="'.url_install_plugin("elementor-pro").'"><i class="far fa-save"></i> install</a></span>');
echo('</td></tr>');
}

echo('<tr><th>ManageWP Worker:</th>');
echo('<td>');
if (is_plugin_active("worker/init.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin ist installiert</span>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin wurde nicht gefunden.      <a class="" href="'.url_install_plugin("worker").'"><i class="far fa-save"></i> install</a></span>');
echo('</td></tr>');

/*echo('<tr><th>Wordfence:</th>');
echo('<td>');
if (is_plugin_active("wordfence/wordfence.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin ist installiert</span></td><td>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin wurde nicht gefunden.</td><td><a class="" href="'.url_install_plugin("wordfence").'"><i class="far fa-save"></i> installieren</a></span>');
echo('</td></tr>');*/

echo('<tr><th>iTheme Security:</th>');
echo('<td>');
if (is_plugin_active("better-wp-security/better-wp-security.php")) 
    echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin installed</span></td><td>'); 
    else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin not found.</td><td><a class="" href="'.url_install_plugin("better-wp-security").'"><i class="far fa-save"></i> install</a></span>');
echo('</td></tr>');

echo('<tr><th>UpdraftPlus – Sichern/Wiederherstellen:</th>');
echo('<td>');
if (is_plugin_active("updraftplus/updraftplus.php")) 
echo('<span style="color:#080;"><i class="fas fa-check"></i> Plugin installed</span></td><td>'); 
else echo('<span style="color:#f00"><i class="fas fa-times"></i> Plugin not found.</td><td><a class="" href="'.url_install_plugin("updraftplus").'"><i class="far fa-save"></i> install</a></span>');
echo('</td></tr>');



echo('<tr><td colspan="2"><b>Useful Plugins:</b></td></tr>');



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

?>
<h2 style="margin-top:2rem;margin-bottom: 0rem;"><?=__("Settings","goo1-omni"); ?></h2>

<form method="POST">
    <INPUT type="hidden" name="act" value="save"/>
<table class="form-table" role="presentation"><tbody>

<?php


if (!empty($_SERVER["HTTP_CF_IPCOUNTRY"])) {
    echo('<tr>
    <th scope="row"><label for="fld_cloudflare_countriesadmin">'.__("Allowed Countries:","goo1-omni").'</label><div><small style="font-weight:normal;">'.__("2 letters, comma separated", "goo1-omni").'</small></div></th>
    <td><input name="cloudflare_countriesadmin" type="text" id="fld_cloudflare_countriesadmin" value="'.(config::get("cloudflare_admin_country") ?? "").'" class="regular-text"></td>
    </tr>');
} else {
    echo('<tr>
    <th scope="row"><label for="fld_cloudflare_countriesadmin">'.__("Allowed Countries:","goo1-omni").'</label><div><small style="font-weight:normal;">'.__("2 letters, comma separated", "goo1-omni").'</small></div></th>
    <td>'.__("no Cloudflare-CDN found", "goo1-omni").'</td>
    </tr>');
}

?>

<tr>
    <th scope="row"></th>
    <td><button type="submit" class="button button-primary">Änderungen speichern</button></td>
    </tr>

</tbody></table>
</form>

<?php

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