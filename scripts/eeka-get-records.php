<?php

//include functions
include dirname(__FILE__) . "/tndees-functions.php";
//die if run from browser
if (!isset($_SERVER["argv"][0]) || isset($_SERVER['REQUEST_METHOD'])  || isset($_SERVER['REMOTE_ADDR'])) {
   die("<br><strong>This script is only meant to run at the command line.</strong>");
}
//debug=1 for extra output.
$debug=0;
//logging path
$logfile = "tndees-eeka-ispdb.log";

## Main
if ($debug == 1) { logger("echo", "ISPDB veritabani kontrol ediliyor.\n"); }
//get records for last $interval minutes from ISPDB
$domains = db_execute("eeka", "SELECT id,hedef,tabloya_eklenme_tarihi,engelle_kaldir FROM Hedefler WHERE tabloya_eklenme_tarihi >= NOW() - INTERVAL $interval MINUTE and tur = 'AA'");
//Query for a specific date
//$hedefler = db_execute("eeka", "SELECT id,hedef,tabloya_eklenme_tarihi,engelle_kaldir FROM Hedefler WHERE tabloya_eklenme_tarihi >= '2016-05-01' and tur = 'AA'");
logger("file", "EEKA veritabani kontrol edildi, ".$domains->num_rows." adet kayit bulundu.", $logfile);

//insert new records to TNDEEDB
while ($row = mysqli_fetch_assoc($domains)) {
	$ispdb_hedefler_id = $row['id'];
	$source = "EEKA";
	$domain = $row['hedef'];
	$action = $row['engelle_kaldir'];
	$ispdb_tabloya_eklenme_tarihi = $row['tabloya_eklenme_tarihi'];

	if ($debug == 1) { logger("echo", "$ispdb_hedefler_id 'li kaydin olup olmadigi kontrol ediliyor.\n"); }
	$check = mysqli_fetch_row(db_execute("local", "SELECT ispdb_hedefler_id FROM domains WHERE ispdb_hedefler_id = '$ispdb_hedefler_id'"));
	if(is_null($check)) {
		db_execute("local", "INSERT INTO domains (ispdb_hedefler_id, source, domain, action, ispdb_tabloya_eklenme_tarihi) VALUES ('$ispdb_hedefler_id', '$source', '$domain', '$action', '$ispdb_tabloya_eklenme_tarihi')");
		logger("file","$ispdb_hedefler_id 'li $action talebi $domain icin eklendi.", $logfile);
		if ($debug == 1) { logger("echo", "$ispdb_hedefler_id 'li kayit domains tablosuna eklendi.\n"); }
	} else {
		logger("file", "$ispdb_hedefler_id 'li $action talebi $domain icin eklenmedi, zaten var.", $logfile);
		if ($debug == 1) { logger("echo", "$ispdb_hedefler_id 'li kayit domains tablosunda bulundugundan islem yapilmadi.\n"); }
	}
}

include_once('tndees-process.php')

?>
