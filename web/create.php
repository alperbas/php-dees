<?php

    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        header("Location: error.php?username_missing");
    }

	require 'database.php';

	if ( !empty($_POST)) {
		// keep track validation errors
		$domainError = null;
		$sourceError = null;
		$mailidError = null;
		$actionError = null;

		// keep track post values
		$domain = $_POST['domain'];
		$source = $_POST['source'];
		$mailid = $_POST['esb_mail_id'];
		$action = $_POST['action'];

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

		if (empty($source)) {
			$source = "ESB";
		}

		if (empty($mailid)) {
			$mailidError = 'Bu karara ait mail numarasını girin';
			$valid = false;
		}

		if (empty($action)) {
			$actionError = 'Seçiminizi yapın';
			$valid = false;
		}

		// insert data
		if ($valid) {
            $userip = $_SERVER['REMOTE_ADDR'];
			$pdo = Database::connect();
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$controlsql = "SELECT * FROM domains WHERE domain = '$domain' ORDER BY id DESC LIMIT 1";
            $controlresult = NULL;
            foreach ($pdo->query($controlsql) as $row) {
                $controlresult = $row;
            }
            //include_once('../scripts/tndees-functions.php');
            include_once dirname(__FILE__) . "/../scripts/tndees-functions.php";
            if (!is_null($controlresult)) {
                if ($controlresult['action'] == $action && $controlresult['esb_mail_id'] == $mailid) {
                    $domainError = 'Domain ve Mail ID kaydı zaten var!';
                }
                else {
                    $sql = "INSERT INTO domains (domain, source, action, esb_mail_id, user, ip) values(?, ?, ?, ?, ?, ?)";
                    $q = $pdo->prepare($sql);
                    $q->execute(array($domain,$source,$action,$mailid,$username,$userip));
                    Database::disconnect();
                    tndees($domain ,$source ,$action);
                    header("Location: index.php");
                }
            }
            else {
                $sql = "INSERT INTO domains (domain, source, action, esb_mail_id, user, ip) values(?, ?, ?, ?, ?, ?)";
                $q = $pdo->prepare($sql);
                $q->execute(array($domain,$source,$action,$mailid,$username,$userip));
                Database::disconnect();
                tndees($domain ,$source ,$action);
                header("Location: index.php");
            }
			Database::disconnect();
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

		<div class="span10 offset1">
			<div class="row">
				<h3>Domain Ekle/Kaldır</h3>
			</div>

			<form class="form-horizontal" action="create.php" method="post">
				<div class="control-group <?php echo !empty($domainError)?'error':'';?>">
					<label class="control-label">Domain</label>
					<div class="controls">
						<input name="domain" type="text" autocomplete="off" placeholder="Domain" value="<?php echo !empty($domain)?$domain:'';?>">
						<?php if (!empty($domainError)): ?>
							<span class="help-inline"><?php echo $domainError;?></span>
						<?php endif; ?>

						<div class="radio">
							<label><input type="radio" name="action" value="E" checked>Ekle</label>
							<label><input type="radio" name="action" value="K">Kaldır</label>
							<?php echo !empty($action)?$action:'';?>
							<?php if (!empty($actionError)): ?>
							    <span class="help-inline"><?php echo $actionError;?></span>
							<?php endif; ?>
						</div>

					</div>
				</div>

				<div class="control-group <?php echo !empty($sourceError)?'error':'';?>">
					<label class="control-label">Kaynak</label>
					<div class="controls">
						<input name="source" type="text" placeholder="ESB" readonly="readonly" value="<?php echo !empty($source)?$source:'';?>">
						<?php if (!empty($sourceError)): ?>
							<span class="help-inline"><?php echo $sourceError;?></span>
						<?php endif; ?>
					</div>
				</div>

                <div class="control-group <?php echo !empty($mailidError)?'error':'';?>">
                    <label class="control-label">Mail Id</label>
                    <div class="controls">
                        <input name="esb_mail_id" type="text" autocomplete="off" placeholder="Mail Id" value="<?php echo !empty($mailid)?$mailid:'';?>">
                        <?php if (!empty($mailidError)): ?>
                            <span class="help-inline"><?php echo $mailidError;?></span>
                        <?php endif; ?>
                    </div>
                </div>

				<div class="form-actions">
					<button type="submit" class="btn btn-success">Tamam</button>
					<a class="btn" href="index.php">Geri</a>
				</div>
			</form>
		</div>
	</div> <!-- /container -->
</body>
</html>
