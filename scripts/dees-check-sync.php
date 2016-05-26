<?php

include_once dirname(__FILE__) . "/tndees-functions.php";

//die if run from browser
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}

//extra output to console
$debug=0;
//log file under /var/log/TNDEES/
$logfile = "tndees-sync.log";


if ($debug == 1) { logger("echo", "DNS'ler kontrol ediliyor.\n"); }
if (is_dnszones_sync()){
    if ($debug == 1) { logger("echo", "DNS'ler sync.\n\n"); }
    logger("file", "DNS zone sayıları karşılaştırıldı, sorun bulunmuyor.", $logfile);
} else {
    if ($debug == 1) { logger("echo", "DNS'ler sync degil.\n\n"); }
    logger("file", "DNS zone sayıları karşılaştırıldı, sayılar tutmuyor.", $logfile);
}

?>
