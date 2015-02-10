<?php
	try{
		$dsn = 'mysql:host=localhost;dbname=blogdb';
		$username = 'bloguser';
		$lozinka = 'mypassword';
	//	$options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 


		$pdo = new PDO($dsn, $username, $lozinka);
		$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$pdo->exec("SET NAMES utf8");
	}
	catch (PDOException $e){
		$greska = 'Nismo uspeli da se konektujemo na database server.';
		include 'greska.html.php';
		exit();
	}
?>