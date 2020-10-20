<?php

namespace plugins\goo1\omni;

class PluginInfos {

    public static function pluginlinks($links) {
        $links[] = '<a href="' . admin_url("options-general.php?page=goo1omni-settings") . '">'.__("Settings","goo1-omni").'</a>';
        $links[] = '<a href="' . admin_url("options-general.php?page=goo1omni-getpro") . '"><b style="color: #808;">'.__("GET PRO","goo1-omni").'</b></a>';
        return $links;
    }

}