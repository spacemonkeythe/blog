<?php

	include_once $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/login.inc.php';
	
	if (!korisnikJeUlogovan()){
		include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.html.php';
		exit();
	}
	if (!korisnikImaUlogu('Account Administrator')){
		$greska = 'Jedino Account Administrator-i imaju pristup ovoj stranici.';
		include $_SERVER['DOCUMENT_ROOT'] . '/admin/pristupZabranjen.html.php';
		exit();
	}

////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u kloliko je na autori.html.php kliknuto na link "Dodajte novog autora"
	if(isset($_GET['dodaj'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//pravim listu autora
		try{
			$rezultat = $pdo->query('SELECT id, description FROM role');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo administratorske uloge iz baze.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//formiram niz $roles[] koji saljem u 
		foreach($rezultat as $red){
			$roles[] = array('id' => $red['id'], 'description' => $red['description'], 'selected' => FALSE);
		}

		//pravim template promenljive
		$naslovStrane = 'Dodaj autora: ';
		$action = 'dodajAutora';
		$name = '';
		$email = '';
		$slika = '';
		$dugme = 'Dodaj';

		//saljem ih u forma.html.php
		include 'forma.html.php';
		exit();

	}


////////////////////////////////////////////////////////////////////////////////////////////////////////

	// u koliko je u formo froma.html.php pritisnuto dugme 'Dodaj'

	if(isset($_GET['dodajAutora'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
	
		//unosim ime i email novog autora u bazu
		try{
			$sql = 'INSERT INTO author SET
					name = :name,
					email = :email';
			$s = $pdo->prepare($sql);
			$s->bindValue(':name', $_POST['name']);
			$s->bindValue(':email', $_POST['email']);
			$s->execute();
		}
			catch (PDOException $e){
				$greska = 'Nismo uspeli da unesem oautora u bazu : ' . $e->getMessage();
				include 'greska.html.php';
				exit();
		}

		//uzimam id novounetog autora
		$authorid = $pdo->lastInsertId();   ////////>>>>>>>>>>>>>>>>>>>>>>. PROVERI OVO!!!!

		//u koliko je prilikom unosa autora unesena i sifra
		if($_POST['password'] != ''){

			//posto u bazi drzim skremblovane lozinke autora, prvo uradim to pa tek onda unosim u bazu
			$password = md5($_POST['password'] . 'blogdb');

			try{
				$sql= 'UPDATE author SET 
						password = :password
						WHERE id = :id';
				$s = $pdo->prepare($sql);
				$s->bindValue(':password', $password);
				$s->bindValue(':id', $authorid);
				$s->execute();
			}
			catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da unesemo autorovu sifru u bazu: ' . $e->getMessage(); // 
				include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
				exit();
			}

		}

		// unosim i uloge autora u koliko su navedene
		if(isset($_POST['roles'])){

			foreach($_POST['roles'] as $role){

				//unosim u tabelu authorroles navedene uloge
				try{
					$sql= 'INSERT INTO authorrole SET 
							  authorid = :authorid
							, roleid = :roleid';
					$s = $pdo->prepare($sql);
					$s->bindValue(':authorid', $authorid);
					$s->bindValue(':roleid', $role);
					$s->execute();
				}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da unesemo autora u bazu: ' . $e->getMessage(); // 
					include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
					exit();
				}
			}
		}

		

			//u koliko je slika postavljena
			if(!empty($_FILES['slika'])){

					//unosim sliku u bazu
				//definisem folder gde ce biti trajna lokacija uploadovanih slika autora
				define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . '/slikeAutora/');

				$slika = $_FILES['slika'];

				// u koliko se prilikom uploada dogodila greska
				if($slika['error'] !== UPLOAD_ERR_OK){
					$greska = 'Dogodila se greska prilikom uploada slike.';
					include 'greska.html.php';
					exit();
				}

				//osiguravam 'sigurno' ime slike
				$name = preg_replace("/[^A-Z0-9._-]/i", "_", $slika["name"]); 

				//vodim racuna da 'ne pregazim' vec postojecu sliku u koliko imaju ista imena
				//tako sto u koliko postoje dve slike sa istim nazivom, drugoj dodajem u naslov
				// vreme i IP adresu sa koje je slika poslata 
				$i = 0;
    			$parts = pathinfo($name);
				while(file_exists(UPLOAD_DIR . $name)){
					$i++;
        			$name = $parts["filename"] . "-" . $i . "-". time() . $_SERVER['REMOTE_ADDR'] . "." . $parts["extension"];
				}

				// preserve file from temporary directory
    			$sveOK = move_uploaded_file($slika["tmp_name"], UPLOAD_DIR . $name);
    			if (!$sveOK) { 
        			$greska =  "Dogodila se greska prilikom memorisanja slike";
        			exit();
    			}
			

			
				//postavljam novu sliku u bazu
				try{
					$sql = 'INSERT INTO slika SET
							name = :name,
							authorid = :authorid';
					$s = $pdo->prepare($sql);
					$s->bindValue(':name', $name);
					$s->bindValue(':authorid', $authorid);
					$s->execute();
				}
					catch (PDOException $e){
						$greska = 'Nismo uspeli da unesem oautora u bazu : ' . $e->getMessage();
						include 'greska.html.php';
						exit();
				}

		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa autorima
		exit();


	}


////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u kolio je u autori.html.php kliknuto na dugme 'Edit'
	if(isset($_POST['action']) and $_POST['action'] == 'Edit'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//preuzimam iz baze selektovanog autora
		try{
			$sql= 'SELECT id, name, email FROM author WHERE id = :id ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo autora: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//rezultat upita smestam u promenljivu $red
		$red = $s->fetch();

		//popunjavam template promenljive

		$naslovStrane = 'Edituj autora: ';
		$action = 'editujAutora';
		$name = $red['name'];
		$email = $red['email'];
		$id = $red['id'];
		$dugme = 'Edituj';

		
		//pravim listu uloga koje autor ima
		try{
			$sql= 'SELECT roleid FROM authorrole WHERE authorid = :id ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $id);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo uloge koje ima autor: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//id-jeve uloga stavljam u promenljivu $roleid
		foreach($s as $red){
			$roleid[] = $red['roleid'];
		} 

		//pravim listu svih uloga na sajtu
		try{
			$rezultat= $pdo->query('SELECT id, description FROM role');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo sve uloge iz baze: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		foreach($rezultat as $red){
			if(isset($roleid)){
				$roles[] = array('id' => $red['id'], 'description' => $red['description'], 'selected' => in_array($red['id'], $roleid));
			}
			else{
				$roles[] = array('id' => $red['id'], 'description' => $red['description'], 'selected' => FALSE);
			}
		}

		//preuzimam autorovu sliku iz baze
		try{
			$sql= 'SELECT slika.name 
					FROM slika
						INNER JOIN author on authorid = author.id 
					WHERE authorid = :id ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $id);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo uloge koje ima autor: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}


		$rezultat = $s->fetch();
		$slika = $rezultat['name'];

		//saljem ih u forma.html.php
		include 'forma.html.php';
		exit();
		
	}


////////////////////////////////////////////////////////////////////////////////////////////////////////

	// u kolio je prilikom editovanja autora u fajlu forma.html.php pritisnuto dugme 'Edit'
	if(isset($_GET['editujAutora'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//update-ujem autorovo ime, mail i role
		try{
			$sql = 'UPDATE author SET
					  name = :name 
					, email = :email
					WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':name', $_POST['name']);
			$s->bindValue(':email', $_POST['email']);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da unesemo autora u bazu: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//u koliko je editovana i sifra
		if ($_POST['password'] != ''){
			$password = md5($_POST['password'] . 'blogdb');
			try{
				$sql = 'UPDATE author SET
						password = :password
						WHERE id = :id';
				$s = $pdo->prepare($sql);
				$s->bindValue(':password', $password);
				$s->bindValue(':id', $_POST['id']);
				$s->execute();
			}
			catch (PDOException $e){
				$error = 'Dogodila se greska prilikom postavljanaj lozinke.';
				include 'error.html.php';
				exit();
			}
		}

		//iz autorrole tabele brisem uloge koje su autoru bile dodeljene 
		try{
			$sql = 'DELETE FROM authorrole
					WHERE authorid = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo uloge iz baze: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		// u koliko su prilikom editovanja postavljene nove uloge
		if(isset($_POST['roles'])){

			foreach($_POST['roles'] as $role){

				//unosim u tabelu authorroles navedene uloge
				try{
					$sql= 'INSERT INTO authorrole SET 
							  authorid = :authorid
							, roleid = :roleid';
					$s = $pdo->prepare($sql);
					$s->bindValue(':authorid', $_POST['id']);
					$s->bindValue(':roleid', $role);
					$s->execute();
				}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da unesemo autora u bazu: ' . $e->getMessage(); // 
					include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
					exit();
				}
			}
		}


		//u koliko je slika postavljena
			if(!empty($_FILES['slika'])){


				//brisemprethodni unos za sliku tog autora
				// ali prvo proveravam da li taj autor uopste ima prethodno postavljenu sliku
				try{
					$sql = 'SELECT COUNT(*) 
							FROM slika
							INNER JOIN author ON slika.authorid = author.id
							WHERE author.id = :id';
					$s = $pdo->prepare($sql);
					$s->bindValue(':id', $_POST['id']);
					$s->execute();			
				}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da prebrojimo slike autora iz baze : ' . $e->getMessage(); // 
					include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
					exit();
				}

				$pom = $s->fetch();
				$imaSliku = $pom[0];

				if($imaSliku > 0){
					// u koliko ima sliku brisem je
					try{
						$sql = 'DELETE 
								FROM slika
								WHERE authorid = :id';
						$s = $pdo->prepare($sql);
						$s->bindValue(':id', $_POST['id']);
						$s->execute();			
					}
					catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
						$greska = 'Nismo uspeli da izbrisemo slike autora iz baze : ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();
					}
				}

				//postavljam novu sliku


					//unosim sliku u bazu

				//definisem folder gde ce biti trajna lokacija uploadovanih slika autora
				define("UPLOAD_DIR", $_SERVER['DOCUMENT_ROOT'] . '/slikeAutora/');

				$slika = $_FILES['slika'];

				// u koliko se prilikom uploada dogodila greska
				if($slika['error'] !== UPLOAD_ERR_OK){
					$greska = 'Dogodila se greska prilikom uploada slike.';
					include 'greska.html.php';
					exit();
				}

				//osiguravam 'sigurno' ime slike
				$name = preg_replace("/[^A-Z0-9._-]/i", "_", $slika["name"]); 

				//vodim racuna da 'ne pregazim' vec postojecu sliku u koliko imaju ista imena
				//tako sto u koliko postoje dve slike sa istim nazivom, drugoj dodajem u naslov
				// vreme i IP adresu sa koje je slika poslata 
				$i = 0;
    			$parts = pathinfo($name);
				while(file_exists(UPLOAD_DIR . $name)){
					$i++;
        			$name = $parts["filename"] . "-" . $i . "-". time() . $_SERVER['REMOTE_ADDR'] . "." . $parts["extension"];
				}

				// preserve file from temporary directory
    			$sveOK = move_uploaded_file($slika["tmp_name"], UPLOAD_DIR . $name);
    			if (!$sveOK) { 
        			$greska =  "Dogodila se greska prilikom memorisanja slike";
        			exit();
    			}
			

			
				//postavljam novu sliku u bazu
				try{
					$sql = 'INSERT INTO slika SET
							name = :name,
							authorid = :authorid';
					$s = $pdo->prepare($sql);
					$s->bindValue(':name', $name);
					$s->bindValue(':authorid', $_POST['id']);
					$s->execute();
				}
					catch (PDOException $e){
						$greska = 'Nismo uspeli da unesem oautora u bazu : ' . $e->getMessage();
						include 'greska.html.php';
						exit();
				}

		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa autorima
		exit();
		
	}


////////////////////////////////////////////////////////////////////////////////////////////////////////

	// u koliko je u autori.html.php kliknuto na dugme 'Delete'
	if(isset($_POST['action']) and $_POST['action'] == 'Delete'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		// selektujemo tekstove koje je autor postavio 
		try{
			$sql= 'SELECT id FROM post WHERE authorid = :authorid ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':authorid', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo tekstove iz baze: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//sve id-jeve autorovih tekstova dobijene iz prethodnog upita smestam u niz $tekstid[]
		foreach($s as $red){
			$tekstid[] = $red['id'];
		}

		//sada za svaki od tih tekstova brisem vezu sa njihovim kategorijama u tabeli postcategory
		try{
			$sql= 'DELETE FROM postcategory WHERE postid = :postid ';
			$s = $pdo->prepare($sql);

			foreach($tekstid as $id){
				$s->bindValue(':postid', $id);
				$s->execute();
			}
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo kategorije za autorove : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//brisem autorove tekstove
		try{
			$sql= 'DELETE FROM post WHERE authorid = :authorid ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':authorid', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo tekstove iz baze: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//brisem autorovu ulogu na sajtu u koliko ju je imao
		try{
			$sql= 'DELETE FROM authorrole WHERE authorid = :authorid ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':authorid', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo autorovu ulogu na sajtu: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}



		//u koliko autor ima postavljenu sliku, brisem i nju
			try{
					$sql = 'SELECT COUNT(*) 
							FROM slika
							INNER JOIN author ON slika.authorid = author.id
							WHERE author.id = :id';
					$s = $pdo->prepare($sql);
					$s->bindValue(':id', $_POST['id']);
					$s->execute();			
				}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da prebrojimo slike autora iz baze : ' . $e->getMessage(); // 
					include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
					exit();
				}

				$pom = $s->fetch();
				$imaSliku = $pom[0];

				if($imaSliku > 0){
					// u koliko ima sliku brisem je
					try{
						$sql = 'DELETE FROM slika
								WHERE authorid = :id';
						$s = $pdo->prepare($sql);
						$s->bindValue(':id', $_POST['id']);
						$s->execute();			
					}
					catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
						$greska = 'Nismo uspeli da izbrisemo slike autora iz baze : ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();
					}
				}



		//brisem autora
		try{
			$sql= 'DELETE FROM author WHERE id = :authorid ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':authorid', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo autora sa sajta: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa autorima
		exit();


	}


/////////////////////////////s///////////////////////////////////////////////////////////////////////////


	//konektujem se na bazu
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	// formiram listu autora koji se trenutno nalaze u bazi
	try{
		$rezultat = $pdo->query('SELECT id, name FROM author');
	}
	catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
		$greska = 'Nismo uspeli da selektujemo autore iz baze.' . $e->getMessage(); // 
		include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
		exit();
	}

	//formiram niz $authors koji saljem u 
	foreach($rezultat as $red){
		$authors[] = array('id' => $red['id'], 'name' => $red['name']);
	}

	include 'autori.html.php';

?>