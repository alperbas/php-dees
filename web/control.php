<?php

    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        header("Location: error.php?username_missing");
    }


require 'database.php';
$dnsg1 = "193.192.98.17";
$dnsg2 = "193.192.98.19";
$dns2 = "212.154.100.18";

if ( !empty($_POST)) {
        // keep track validation errors
        $domainError = null;

        // keep track post values
        $domain = $_POST['domain'];

        // validate input
        $valid = true;

        if (empty($domain)) {
            $domainError = 'Geçerli bir domain girin';
            $valid = false;
        } else if (isset($domain)) {
            if (preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain) //valid chars check
                && preg_match("/^.{1,253}$/", $domain) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain)) {
                $valid = true;
            }
            else {
                $domainError = 'Geçerli bir domain girin. Yazım hatası yaptınız.';
                $valid = false;
            }
        }
}

$result = array();
$result_html = '';

if (isset($_POST['domain']) && !empty($_POST['domain'])) {
    $domain_regex = '/[a-z\d][a-z\-\d\.]+[a-z\d]/i';

    if (preg_match($domain_regex, $_POST['domain'])) {
        //include_once('../scripts/tndees-functions.php');
        include_once dirname(__FILE__) . "../scripts/tndees-functions.php";
        $resultg1 = get_domain_ip($_POST['domain'], $dnsg1);
        $resultg2 = get_domain_ip($_POST['domain'], $dnsg2);
        $result2 = get_domain_ip($_POST['domain'], $dns2);
    }

	if (empty($resultg1)) {
		$result_html .= '<hr>dns-g1 sunucusunda herhangi bir sonuc bulunamadi!';
    } else {
        $result_html .= "<hr><b>dns-g1:</b> ".$_POST['domain']." A $resultg1";
	}
	if (empty($resultg2)) {
		$result_html .= '<hr>dns-g2 sunucusunda herhangi bir sonuc bulunamadi!';
    } else {
        $result_html .= "<hr><b>dns-g2:</b> ".$_POST['domain']." A $resultg2";
	}
	if (empty($result2)) {
		$result_html .= '<hr>dns2 sunucusunda herhangi bir sonuc bulunamadi!';
    } else {
        $result_html .= "<hr><b>dns2:</b> ".$_POST['domain']." A $result2";
	}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
        <meta charset="utf-8">
        <link   href="css/bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
        <script src="js/jquery-1.10.2.min.js"></script>
        <script src="js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
	<div class="panel panel-success">
            <div class="panel-footer">
                <h3>DNS Kontrol</h3>
            </div>
            <form class="form-horizontal" action="control.php" method="post">
                <div class="control-group <?php echo !empty($domainError)?'error':'';?>">
                    <label class="control-label">Domain</label>
                    <div class="controls">
                        <input name="domain" type="text" autocomplete="off" placeholder="Domain" value="<?php echo !empty($domain)?$domain:'';?>">
                        <?php if (!empty($domainError)): ?>
                            <span class="help-inline"><?php echo $domainError;?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-success">Tamam</button>
                    <a class="btn" href="index.php">Geri</a>
                </div>
            </form>
        </div>
        <?=$result_html?$result_html:''?>
        <hr/>
            <br/>
        </p>
    </div> <!-- /container -->
</body>
</html>
