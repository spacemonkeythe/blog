<?php
	function korisnikJeUlogovan(){

		// u koliko je u login.html.php pritisnuto dugme Log In
		if(isset($_POST['action']) and $_POST['action'] == 'login'){

			//proveravam da li je sve uneseno kako treba
			if(!isset($_POST['email']) or ($_POST['email'] == '') or !isset($_POST['password']) or($_POST['password'] == '')){
				$GLOBALS['greska'] = 'Molimo vas popunite oba polja.';
				return FALSE;
			}

			//skremblujemo autorovu lozinku koju je autor uneo u login stranici
			$password = md5($_POST['password'] . 'blogdb');
			
			//proveravamo da li je autor korektno ulogovan/unesen u bazu 
			if(autorJeUBazi($_POST['email'], $password)){  // 324 (297)
				//u koliko jeste zapocece se sesija i u njoj cuvam kontrolnu promenljivu, email i password
				//autora
				session_start();
				$_SESSION['ulogovan'] = TRUE;
				$_SESSION['email'] = $_POST['email'];
				$_SESSION['password'] = $password; 
				return TRUE;
			}
			else{
				// u koliko se autor ne nalazi u bazi/tj nije (pravilno) ulogovan
				//prekidam sesiju i ispisjume poruko u gresci
				session_start();
				unset($_SESSION['ulogovan']);
				unset($_SESSION['password']);
				unset($_SESSION['email']); 
				$GLOBALS['greska'] = 'Uneti email ili lozinka nisu ispravni, molimo pokusajte ponovo.';
				return FALSE;
			}
		}

			//u koliko je negde kliknuto dugme Log out
			if(isset($_POST['action']) and $_POST['action'] == 'logout'){
				//prekidam sesiju
				session_start();
				unset($_SESSION['ulogovan']);
				unset($_SESSION['password']);
				unset($_SESSION['email']); 
				//vracm korisnika na 
				header('Location: ' . $_POST['goto']);
				exit();
			}



			// u koliko nije u pitanju ni jedan od prethodnih specijalnih slucajeva
			session_start();
			if(isset($_SESSION['ulogovan'])){
				return autorJeUBazi($_SESSION['email'], $_SESSION['password']);
				// TRUE;
			}
		
		
	}


	function autorJeUBazi($email, $password){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//trazim broj autora koji ispunjavaju kriterijum
		try{
			$sql = 'SELECT COUNT(*) FROM author 
					WHERE email = :email AND password = :password';
			$s = $pdo->prepare($sql);
			$s->bindValue(':email', $email);
			$s->bindValue(':password', $password);
			$s->execute();
		}
		catch (PDOException $e){
				$greska = 'Nismo uspeli da utvrdimo broj odgovarajucih autora : ' . $e->getMessage();
				include 'greska.html.php';
				exit();
		}

		$rezultat = $s->fetch();
		echo $rezultat[0];

		if($rezultat[0] > 0){
			return TRUE;
			
		}
		else{
			return FALSE;
			
		}

	}

	//funkcija koja proverava da li je autoru dodeljena neka od uloga na sajtu
	function korisnikImaUlogu($uloga){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//za autorov email i password tekuce sesije koji se nalaze u $_SESSION nizu proveram da li je mu je  
		// dodeljena$uloga koja je proseldjena ovoj funkciji
		try{
			$sql = 'SELECT COUNT(*)
					FROM author 
						INNER JOIN authorrole ON author.id = authorid
						INNER JOIN role ON role.id = roleid
					WHERE (email = :email) AND (role.id = :roleid)';
			$s = $pdo->prepare($sql);
			$s->bindValue(':email', $_SESSION['email']);
			$s->bindValue(':roleid', $uloga);
			$s->execute();
		}
		catch (PDOException $e){
				$greska = 'Nismo uspeli da utvrdimo uloge autora : ' . $e->getMessage();
				include 'greska.html.php';
				exit();
		}

		$rezultat = $s->fetch();

		if($rezultat[0] > 0){
			return TRUE;
		}
		else{
			return FALSE;
		}
	}
?>