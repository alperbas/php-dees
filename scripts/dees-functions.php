<?php

/*//die if run from browser
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}
*/

//variables, check interval as minutes
$interval = 30;
// DNS
$tibdns = "195.175.254.2";
$n1dns = "193.192.122.33";

function db_execute($server, $query) {
    global $debug;
    // which db?
    if ($server == "eeka") {
        $database_hostname = "193.192.100.86";
        $database_username = "root";
        $database_password = "tib2010tib";
        $database_default = "ISPDB";
    } elseif ($server = "local") {
        $database_hostname = "localhost";
        $database_username = "root";
        $database_password = 'BNM*p3Y"';
        $database_default = "TNDEEDB";
    } else {
        logger("echo", "server must be set eeka or local.\n");
        return 0;
    }

    // Connect and execute query to DB
    $connection = new mysqli($database_hostname, $database_username, $database_password, $database_default);
    $result = $connection->query($query);
    if (!$debug == 1) {
        if (!$result) {
            echo "Error executing query: (".$mysqli->errno.") ".$mysqli->error."\n";
            echo "$query";
        } else {
            return $result;
        }
    } else {
        return $result;
    }
}

function logger($type, $log, $file = NULL) {
    if ($type == 'file') {
        $log = date("Y-m-d H:i:s")." ".$log;
        if (!is_null($file)) {
            $filename = "/var/log/TNDEES/$file";
        } else {
            //for web requests
            $filename = "/var/log/TNDEES/tndees-esb-action.log";
        }
        $myfile = file_put_contents($filename, $log.PHP_EOL, FILE_APPEND);
    } elseif ($type == 'echo') {
        echo $log;
    }
}

function exec_into_array($command_line) {
        $out = array();
        $err = 0;
        exec($command_line,$out,$err);

        $command_array = array();

        for($i=0; list($key, $value) = each($out); $i++) {
                $command_array[$i] = $value;
        }

        return $command_array;
}

function ping($host, $timeout = "5") {
        exec(sprintf("ping -c 1 -W $timeout %s", escapeshellarg($host)), $res, $rval);
        return $rval === 0;
}

/*
function ping($host, $timeout = 1) {
        // ICMP ping packet with a pre-calculated checksum
        $package = "\x08\x00\x7d\x4b\x00\x00\x00\x00PingHost";
        $socket  = socket_create(AF_INET, SOCK_RAW, 1);
        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $timeout, 'usec' => 0));
        socket_connect($socket, $host, null);

        $ts = microtime(true);
        socket_send($socket, $package, strLen($package), 0);
        if (socket_read($socket, 255))
                $result = microtime(true) - $ts;
        else    $result = false;
        socket_close($socket);

        return $result;
}
*/

function is_blocked_domain($domain, $dns, $source) {
    global $debug;
    global $tibdns;
    global $n1dns;
    $cmd = "/usr/bin/dig $domain @$dns +short";
    $result = exec_into_array($cmd);
    if (sizeof($result) == 0) {
        if ($debug == 1) { logger("echo", "$domain icin $dns kontrol edildi, DNS kaydi bulunmuyor.\n"); }
        return 0;
    }
    if ($debug == 1) { logger("echo", "$domain icin $dns kontrol edildi, ".$result[0]."\n"); }
    if ($source == "EEKA") {
        if ($result[0] == $tibdns) {
            return 1;
        } else {
            return 0;
        }
    } elseif ($source == "ESB") {
        if ($result[0] == $n1dns) {
            return 1;
        } else {
            return 0;
        }
    }
}

function get_domain_ip($domain, $dns) {
    global $debug;
    global $tibdns;
    global $n1dns;
    $cmd = "/usr/bin/dig $domain @$dns +short";
    $result = exec_into_array($cmd);
    if (sizeof($result) == 0) {
        if ($debug == 1) { logger("echo", "$domain icin $dns kontrol edildi, DNS kaydi bulunmuyor.\n"); }
        return 0;
    }
    if ($debug == 1) { logger("echo", "$domain icin $dns kontrol edildi, ".$result[0]."\n"); }

    return $result[0];
}

function is_valid_domain_name($domain_name) {
    return (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
            && preg_match("/^.{1,253}$/", $domain_name) //overall length check
            && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name) ); //length of each label
}

function alert ($type, $message) {
    global $debug;
    if ($type == "mail") {
        $from    = 'tndees@linux.netone.net.tr';
        $to      = 'ict-oss@turknet.net.tr';
        $subject = 'TNDEES Alert';
        $message = 'Alarm Testi';
        $headers = 'From: tndees@turk.net' . "\r\n" .
                    'Reply-To: sistem@turknet.net.tr' . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

        mail($to, $subject, $message, $headers);
        exec("echo ".$message." | /sbin/sendmail -f $from $to");
        if ($debug == 1) { logger("echo", "$to adresine mail gönderildi.\n"); }
    }
}

function is_dnszones_sync() {
    global $debug;

    if ($debug == 1) { logger("echo", "DNS listesi aliniyor.\n"); }
    $dns = db_execute("local", "SELECT hostname,ip FROM dns WHERE aktif = '1'");
    $sync = TRUE;
    $i = 0;
    while ($dnsrow = mysqli_fetch_assoc($dns)) {
    	$hostname[$i] = $dnsrow['hostname'];
    	$ip[$i] = $dnsrow['ip'];
        if (ping($ip[$i], "4") || ping($ip[$i], "4")) {
            $prevzonecount[$i] = rndc("zonecount", $hostname[$i], $ip[$i]);
            if ($debug == 1) { logger("echo", $hostname[$i]." zones: ".$prevzonecount[$i]."\n"); }
            if ($i > 0 && $prevzonecount[$i] != $prevzonecount[($i-1)] ){
                if ($debug == 1) { logger("echo", "Zone senkron hatasi!!! ".$hostname[($i-1)]." : ".$prevzonecount[($i-1)]." ".$hostname[$i]." : ".$prevzonecount[$i]."\n"); }
                $sync = FALSE;
            }
        } else {
            if ($debug == 1) { logger("echo", $hostname[$i]." erisilemiyor.\n"); }
            $prevzonecount[$i] = "0";
        }
        $i++;
    }
    if ($sync) {
        return 1;
    } else {
        return 0;
    }
}

function rndc($command, $hostname, $ip, $domain = NULL, $source = NULL) {
    global $debug;
    $rndc = '/usr/sbin/rndc';

    switch ($command) {
        case "zonecount":
            //rndc -s 193.192.98.17 status
            $cmd = "$rndc -s $ip status 2>&1";
            $result = exec_into_array($cmd);
            foreach ($result as $line) {
                list($desc, $value) = explode(":", $line);
                list(, $value) = explode(" ", $value);
                if ($desc == "number of zones") {
                    return $value;
                }
            }
            break;
        case "addzone":
            //rndc -s 193.192.98.17 addzone hebe.com '{type master; file "n1.yasakli.domain";};'
            if ($source == "EEKA") {
                $cmd = "$rndc -s $ip addzone $domain '{type master; file \"tib.yasakli.domain\";};' 2>&1";
            } elseif ($source == "ESB") {
                $cmd = "$rndc -s $ip addzone $domain '{type master; file \"n1.yasakli.domain\";};' 2>&1";
            } else {
                if ($debug == 1) { logger("echo", "Source must be set EEKA or ESB.\n"); }
                return 0;
            }
            if ($debug == 1) { logger("echo", $cmd."\n"); }
            $result = exec_into_array($cmd);
            if (sizeof($result) == 0) {
                if ($debug == 1) { logger("echo", "$domain eklendi.\n"); }
                return 1;
            } else {
                if ($debug == 1) { logger("echo", "$domain eklenemedi, ".$result[0]."\n"); }
                return $result[0];
            }
            break;
        case "delzone":
            //rndc -s 193.192.98.17 delzone hebe.com
            $cmd = "$rndc -s $ip delzone $domain 2>&1";
            if ($debug == 1) {
                logger("echo", $cmd."\n");
            }
            $result = exec_into_array($cmd);
            if (sizeof($result) == 0) {
                if ($debug == 1) { logger("echo", "$domain kaldirildi.\n"); }
                return 1;
            } else {
                if ($debug == 1) { logger("echo", "$domain kaldirilamadi, ".$result[0]."\n"); }
                return $result[0];
            }
            break;
    }
}

function tndees($domain, $source = "ESB", $action = NULL, $ip = NULL, $hostname = NULL) {
    global $debug;
    global $logfile;

    if (is_array($domain)) {
        if ($debug == 1) { logger("echo", "Domainler array, istek EEKA ile geldi.\n"); }
        $id = $domain['id'];
        $source = $domain['source'];
        $action = $domain['action'];
        $domain = $domain['domain'];
    } else {
        if ($debug == 1) { logger("echo", "Domain browser uzerinden geldi.\n"); }
        $id = NULL;
    }

    if ($ip == NULL && $hostname == NULL) {
        if ($debug == 1) { logger("echo", "DNS listesi aliniyor.\n"); }
        $dns = db_execute("local", "SELECT hostname,ip FROM dns WHERE aktif = '1'");
        $i = 0;
        while ($dnsrow = mysqli_fetch_assoc($dns)) {
        	$hostname[$i] = $dnsrow['hostname'];
        	$ip[$i] = $dnsrow['ip'];
            if (ping($ip[$i], "4") || ping($ip[$i], "4")) {
                $prevzonecount[$i] = rndc("zonecount", $hostname[$i], $ip[$i]);
                if ($debug == 1) { logger("echo", $hostname[$i]." zones: ".$prevzonecount[$i]."\n"); }
            } else {
                if ($debug == 1) { logger("echo", $hostname[$i]." erisilemiyor.\n"); }
                $prevzonecount[$i] = "0";
            }
            $i++;
        }
    }

    //default results.
    $resultstatus = 1;
    $resultzone = "basarisiz";
    $resultdns = "basarisiz";

    for ($i=0;$i<sizeof($hostname);$i++){
        if ($debug == 1) { logger("echo", "Karar: ".$hostname[$i]." $source $action $domain\n"); }
        if (ping($ip[$i], "4") || ping($ip[$i], "4")) {
            if ($debug == 1) { logger("echo", $hostname[$i]." erisilebilir durumda.\n"); }
            if ($action == "E") {
                $resultrndc = rndc("addzone", $hostname[$i], $ip[$i], $domain, $source);
                $newzonecount[$i] = rndc("zonecount", $hostname[$i], $ip[$i]);
                if ($resultrndc == 1 && $newzonecount[$i] = ($prevzonecount[$i] + 1)) {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain Zone ekleme basarili.\n"); }
                    $resultzone = "basarili";
                } else {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain Zone ekleme basarisiz: $resultrndc\n"); }
                    $resultzone = "basarisiz: $resultrndc";
                }
                if (is_blocked_domain($domain, $ip[$i], $source)) {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain DNS sorgusu basarili.\n"); }
                    $resultdns = "basarili";
                } else {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain DNS sorgusu basarisiz.\n"); }
                    $resultdns = "basarisiz";
                }
            } elseif ($action == "K") {
                $resultrndc = rndc("delzone", $hostname[$i], $ip[$i], $domain);
                $newzonecount[$i] = rndc("zonecount", $hostname[$i], $ip[$i]);
                if ($resultrndc == 1 && $newzonecount[$i] = ($prevzonecount[$i] - 1)) {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain Zone kaldirma basarili.\n"); }
                    $resultzone = "basarili";
                } else {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain Zone kaldirma basarisiz: $resultrndc\n"); }
                    $resultzone = "basarisiz: $resultrndc";
                }
                if (!is_blocked_domain($domain, $ip[$i], $source)) {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain DNS sorgusu basarili.\n"); }
                    $resultdns = "basarili";
                } else {
                    if ($debug == 1) { logger("echo", "Kontrol - $action - ".$hostname[$i]." $domain DNS sorgusu basarisiz.\n"); }
                    $resultdns = "basarisiz";
                }
            } else {
                if ($debug == 1) { logger("echo", "$domain action parametresi hatali.\n"); }
            }
        } else {
            if ($debug == 1) { logger("echo", $hostname[$i]." erisilemiyor.\n"); }
            $resultzone = $hostname[$i]." is unreachable";
            $resultdns = "DNS down";
            $resultrndc = $resultzone;
        }
        if (is_null($id)) {
            $id = db_execute("local", "SELECT id FROM domains WHERE domain = '$domain' AND action = '$action' AND (timestamp >= NOW() - INTERVAL 1 MINUTE) ORDER BY id DESC LIMIT 1;");
            $id = mysqli_fetch_assoc($id);
            $id = $id['id'];
        }
        db_execute("local", "INSERT INTO results (domains_id, dns, result) VALUES ('$id', '".$hostname[$i]."', \"$resultrndc\")");
        if (!($resultzone == "basarili" && $resultdns == "basarili")) {
            $resultstatus = 0;
        }
        logger("file", "$domain $source $action karari ".$hostname[$i]." sunucusuna gonderildi. RNDC komutu kontrol: $resultzone, DNS Kontrol: $resultdns.", $logfile);
        if ($debug == 1) { logger("echo", "Islem sonuclari kontrol tablosuna yazildi.\n\n"); }
    }
    if ($resultstatus == 1) {
        logger("file", "$domain $source $action karari uygulandi.", $logfile);
        return 1;
    } else {
        logger("file", "$domain $source $action karari uygulanamadi.", $logfile);
        return 0;
    }
}

?>
