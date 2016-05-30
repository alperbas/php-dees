<?php

include_once dirname(__FILE__) . "/tndees-functions.php";

//die if run from browser
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}

//extra output to console
$debug=0;
//log file under /var/log/TNDEES/
$logfile = "tndees-eeka-action.log";

/*status output
version: 9.9.4-RedHat-9.9.4-29.el7_2.3 <id:8f9657aa>
CPUs found: 4
worker threads: 4
UDP listeners per interface: 4
number of zones: 105
debug level: 0
xfers running: 0
xfers deferred: 0
soa queries in progress: 0
query logging is OFF
recursive clients: 0/0/1000
tcp clients: 0/100
server is up and running
*/

if ($debug == 1) { logger("echo", "Domain listesi aliniyor.\n"); }
$domains = db_execute("local", "SELECT id,domain,source,timestamp,action FROM domains WHERE timestamp > NOW() - INTERVAL $interval MINUTE AND source = 'EEKA'");
if ($debug == 1) { logger("echo", "TNDEEDB veritabani kontrol edildi, ".$domains->num_rows." adet domain bulundu.\n"); }
if ($domains->num_rows > 0) {
    logger("file", "TNDEEDB veritabani kontrol edildi, ".$domains->num_rows." adet domain bulundu.", $logfile);
    while ($domainrow = mysqli_fetch_assoc($domains)) {
        $id = $domainrow['id'];
        $domain = $domainrow['domain'];
        $source = $domainrow['source'];
        $action = $domainrow['action'];
        $ispdb_tabloya_timestamp = $domainrow['timestamp'];
        if (is_valid_domain_name($domain)) {
            if ($debug == 1) { logger("echo", "$domain domain dogru oldu, tndees baslatiliyor.\n"); }
            tndees($domainrow);
        } else {
            if ($debug == 1) { logger("echo", "$domain hatali domain.\n"); }
            logger("file", "$domain $source $action karari, hatali domain nedeniyle uygulanmadi.", $logfile);
        }
    }
} else {
    if ($debug == 1) { logger("echo", "Islem yapilacak domain yok.\n\n"); }
    logger("file", "TNDEEDB veritabani kontrol edildi, son $interval dakika icerisinde eklenen domain bulunmuyor.", $logfile);
}

?>
