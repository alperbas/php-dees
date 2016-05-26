<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<link   href="css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <script src="js/jquery-1.10.2.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
</head>

<?php
    session_start();
    if (isset($_SESSION['username'])) {
        $username = $_SESSION['username'];
    } else {
        header("Location: error.php?username_missing");
    }
?>

<body>
	<div class="container">
        <div class="page-header">
            <h3>TNDEES Veritabanı Domain Arama Sayfası</h3>
        </div>

		<div class="row clearfix">
			<table class="table table-striped table-condensed">
				<thead>
					<tr>
						<th>Hedef</th>
						<th>Kaynak</th>
						<th>Islem</th>
						<th>Islem Tarihi</th>
						<th>Aksiyon</th>
					</tr>
				</thead>
				<tbody>
					<?php
                        include 'database.php';
                        $domain = $_REQUEST['domain'];
                        $pdo = Database::connect();
                        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $controlsql = "SELECT * FROM domains WHERE domain = '$domain'";
                        $controlresult = NULL;
                        foreach ($pdo->query($controlsql) as $row) {
                            $controlresult = $row;
                            echo '<tr>';
                            echo '<td>'. $row['id'] . '</td>';
                            echo '<td>'. $row['domain'] . '</td>';
                            echo '<td>'. $row['source'] . '</td>';
                            echo '<td>'. $row['action'] . '</td>';
                            echo '<td>'. $row['timestamp'] . '</td>';
                            echo '<td width=50>';
                            echo '<a class="btn btn-info" href="read.php?id='.$row['id'].'">Detay</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        if (is_null($controlresult)) {
                            echo '<tr>';
                            echo 'TNDEES veritabanında herhangi bir kayıt bulunamadı.';
                            echo '</tr>';
                        }
                        Database::disconnect();
					?>
				</tbody>
			</table>
		</div>
        <div class="form-actions">
            <a class="btn" href="index.php">Geri</a>
        </div>
<body>
	</div> <!-- /container -->
</body>
</html>
