<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
    <meta http-equiv="X-AU-Compatible" content="IE=edge; IE=11; IE=10; IE=9; IE=8; IE=7; IE=6; IE=5">
	<link   href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
	<script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>

<?php

    session_start();

    if (!isset($_SESSION['username'])) {
        if (isset($_POST['username'])) {
            $username = $_POST['username'];
            if (strlen($username) == 0) {
                header("Location: error.php?username_empty");
            } else {
                $_SESSION['username'] = $username;
            }
        } else {
            header("Location: error.php?username_missing");
        }
    }

?>

<body>
	<div class="container">
		<div class="page-header">
			<h3 align="center"> TurkNet EEKA/ESB Domain Erişim Engelleme Sayfası</h3>
		</div>

		<div class="row clearfix">
			<p>
                <form method="post" action="search.php">
                    <a href="create.php" class="btn btn-success">Yeni Kayıt</a>
				    <a href="control.php" class="btn">Kontrol</a>
                    <input value="" name="domain" type="text" autocomplete="off" class="form-control pull-right" placeholder="Örnek: domain.com">
				    <input type="submit" href="search.php" class="btn pull-right" value="Arama">
                </form>
            </p>

			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>Hedef</th>
						<th>Kaynak</th>
						<th>İşlem</th>
						<th>İşlem Tarihi</th>
						<th>Aksiyon</th>
					</tr>
				</thead>
				<tbody>
					<?php
						include 'database.php';
						$pdo = Database::connect();
						$sql = 'SELECT * FROM domains WHERE source = "ESB" ORDER BY id DESC LIMIT 10';
						foreach ($pdo->query($sql) as $row) {
							echo '<tr>';
							echo '<td>'. $row['domain'] . '</td>';
							echo '<td>'. $row['source'] . '</td>';
							echo '<td>'. $row['action'] . '</td>';
							echo '<td>'. $row['timestamp'] . '</td>';
							echo '<td width=50>';
							echo '<a class="btn btn-info" href="read.php?id='.$row['id'].'">Detay</a>';
							echo '</td>';
							echo '</tr>';
						}
						Database::disconnect();
					?>
				</tbody>
			</table>
		</div>

		<head>
		<style>
		.alert {
				padding: 20px;
				background-color: #f44336;
				color: white;
		}

		.alert.success {background-color: #4CAF50;}
		.alert.info {background-color: #2196F3;}
		.alert.warning {background-color: #ff9800;}

		.closebtn {
				margin-left: 15px;
				color: white;
				font-weight: bold;
				float: right;
				font-size: 22px;
				line-height: 20px;
				cursor: pointer;
				transition: 0.3s;
		}

		.closebtn:hover {
				color: black;
		}
		</style>
		</head>
		<body>

<!--
		<div class="alert"> red
		<div class="alert success"> green
		<div class="alert info"> blue
		<div class="alert warning"> yellow
-->
		<div class="alert info">
				<span href="#" class="closebtn" onclick="this.parentElement.style.display='none';">×</span>
				<strong>BİLGİ: </strong> Veritabanındaki son 10 ESB kaydı görüntülenmektedir.
		</div>

	</div> <!-- /container -->
</body>
</html>
