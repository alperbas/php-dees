<?php

    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        header("Location: error.php?username_missing");
    }

	require 'database.php';
	$id = null;
	if ( !empty($_GET['id'])) {
		$id = $_REQUEST['id'];
	}
	
	if ( null==$id ) {
		header("Location: index.php");
	} else {
		$pdo = Database::connect();
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$sql = "SELECT * FROM domains where id = ?";
		$q = $pdo->prepare($sql);
		$q->execute(array($id));
		$data = $q->fetch(PDO::FETCH_ASSOC);
		Database::disconnect();
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
				<h3>Detay</h3>
			</div>
			
			<div class="form-horizontal" >
				<div class="control-group">
						<label class="control-label">Hedef</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['domain'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">Kaynak</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['source'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">Aksiyon Tipi</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['action'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">İşlem Tarihi</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['timestamp'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">Kullanıcı</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['user'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">Kullanıcı IP</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['ip'];?>
								</label>
						</div>
				</div>
				
				<div class="control-group">
						<label class="control-label">ESB Mail ID</label>
						<div class="controls">
								<label class="checkbox">
										<?php echo $data['esb_mail_id'];?>
								</label>
						</div>
				</div>
				
				<div class="form-actions">
						<a class="btn" href="index.php">Geri</a>
				</div>
			</div>

		</div>
	</div> <!-- /container -->
</body>
</html>
