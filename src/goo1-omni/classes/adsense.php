<?php

namespace plugins\goo1\omni;

class adsense {

    public static function adstxt() {
        header("Cache-Control: max-age=86400, s-maxage=86400, stale-while-revalidate=86400000, stale-if-error=86400000");
        header("Content-Type: text/plain; charset=utf-8");
        die('google.com, pub-3364448309114165, DIRECT, f08c47fec0942fa0');
    }


}