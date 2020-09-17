<?php
$str = file_get_contents("https://goo1.de/freshdesk/json_domainstatus.php?domain=".urlencode($_SERVER["HTTP_HOST"]));
$json = json_decode($str, true);

$a = get_site_option("plugin-goo1-omni-personal", false);
if ($a == false) add_site_option("plugin-goo1-omni-personal", $json["personal"]); else update_site_option("plugin-goo1-omni-personal", $json["personal"]);
$is_personal = ($json["personal"] == 2);

if (!empty($_POST["act"]) AND $_POST["act"] == "send") {
    $current_user = wp_get_current_user();
    $m = new \plugins\goo1\omni\FreshdeskTicket();
    $m->subject = $_POST["subject"];
    $m->description = nl2br($_POST["msg"]).'<br/><br/><hr/><br/><br/>URL: '.htmlentities($_GET["refurl"] ?? "",3,"UTF-8").'<br/><pre>'.var_export($_SERVER,true).'</pre>';
    $m->priority = $_POST["priority"] ?? 2;
    $m->type = $_POST["type"] ?? null;
    $m->name = $current_user->display_name;
    $m->email = $current_user->user_email;
    $m->addTag("DOKpress");
    $msg_submitbugreportvianavigation = $m->upload();
}
?>

<style>
@import url(https://library.goo1.de/fontawesome/5/css/all.min.css);
</style>
<h1><?php
if ($is_personal) _e("Aks Andreas if you're having a question or problem", "goo1-omni");
else _e("goo1 Helpdesk", "goo1-omni");
?></h1>
<hr class="wp-header-end">

<?php
if (!empty($msg_submitbugreportvianavigation["id"])) {
    echo('<div class="notice notice-success"><i class="fas fa-bug fa-2x" style="float:left; margin: 0.3rem 0.5rem 00rem 0;"></i><p>'.__("Your report/request was sent...", "goo1-omni").'<br/>'.__("Ticket id:", "goo1-omni").' '.$msg_submitbugreportvianavigation["id"].'</p></div>');
}
if (isset($msgerror_submitbugreportvianavigation)) {
    echo('<div class="notice notice-danger"><i class="fas fa-bug fa-2x" style="float:left; margin: 0.3rem 0.5rem 00rem 0;"></i><p>'.__("Your report/request couldn't be sent.","goo1-omni").'</p></div>');
}
?>

<form method="POST">
<INPUT type="hidden" name="act" value="send"/>

<style>
table#list01 input:checked ~ span {
    border-color: black;
    font-weight: bold;
} 
</style>
<table id="list01" class="form-table" role="presentation">
    <tbody>
        <tr>
            <th scope="row"><label for="fld_subject"><?=__("Subject","goo1-omni"); ?>:</label></th>
            <td><input name="subject" type="text" id="fld_subject" value="" class="regular-text" REQUIRED="REQUIRED"></td>
        </tr>

        <tr>
            <th scope="row"><?=__("Type","goo1-omni"); ?>:</th>
            <td>
                <label><input type="radio" name="type" value="Question"> <span class="format-i18n"><i class="fad fa-question"></i> <?=__("Question", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="type" value="Incident"> <span class="format-i18n"><i class="fad fa-car-crash"></i> <?=__("Incident", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="type" value="Problem" checked="checked"> <span class="format-i18n"><i class="fad fa-bug"></i> <?=__("Problem", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="type" value="Feature Request"> <span class="format-i18n"><i class="fad fa-lightbulb-exclamation"></i> <?=__("Feature Request", "goo1-omni"); ?></span></label><br/>
	        </td>
        </tr>

        <tr>
            <th scope="row"><?=__("Priority","goo1-omni"); ?>:</th>
            <td>
                <label><input type="radio" name="priority" value="1"> <span class="format-i18n"><i class="fad fa-tachometer-slowest"></i> <?=__("low", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="priority" value="2" checked="checked"> <span class="format-i18n"><i class="fad fa-tachometer-average"></i> <?=__("normal", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="priority" value="3"> <span class="format-i18n"><i class="fad fa-tachometer-fastest"></i> <?=__("high", "goo1-omni"); ?></span></label><br/>
                <label><input type="radio" name="priority" value="4"> <span class="format-i18n" style="color:red;"><i class="fad fa-bomb"></i> <?=__("absolutely urgent", "goo1-omni"); ?></span></label><br/>
	        </td>
        </tr>

        <tr>
            <th scope="row"><label for="fld_msg"><?=__("Message","goo1-omni"); ?>:</label></th>
            <td>
                <TEXTAREA name="msg" id="fld_msg" class="regular-text" REQUIRED="REQUIRED" ROWS="10" PLACEHOLDER=""></TEXTAREA>
                <p class="description" id="tagline-description"><?=__("Please describe in detail the best you can, to help you as fast as possible.", "goo1-omni"); ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="fld_msg"><?=__("Affected URL","goo1-omni"); ?>:</label></th>
            <td><a href="<?=htmlentities($_GET["refurl"] ?? "",3,"UTF-8"); ?>" target="_blank"><i class="far fa-share-square"></i> <?=htmlentities($_GET["refurl"] ?? "",3,"UTF-8"); ?></a></td>
        </tr>

         

    </tbody>
</table>

<table><tr>
    <td><button type="submit" class="button button-primary"><i class="fas fa-paper-plane"></i> <?=__("send your request","goo1-omni"); ?></button></td>
    <td><?php
print_r($json);
    ?></td>
</tr></table>

</form>


<?php



exit;
