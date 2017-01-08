<?php
define("SITE_KEY", "mytimemanagerapp");

class TMApi {

    public static function genApiKey($session_uid) {
        $key = md5(SITE_KEY.$session_uid);
        return hash('sha256', $key.$_SERVER['REMOTE_ADDR']);
    }
}
