<?php
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/login.inc.php';

		if (!korisnikJeUlogovan()){
			include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.html.php';
			exit();
		}
		if (!korisnikImaUlogu('Site Administrator')){
			$greska = 'Jedino Site Administrator-i imaju pristup ovoj stranici.';
			include $_SERVER['DOCUMENT_ROOT'] . '/admin/pristupZabranjen.html.php';
			exit();
		}


	//U koliko je u kategorije.html.php kliknuto na link 'Dodajte novu kategoriju' 
	if(isset($_GET['dodaj'])){

		// pripremam template promenljive koje posle saljem u forma.html.php
		$naslovStrane = 'Dodaj kategoriju';
		$action = 'dodajKategoriju';
		$ime = '';
		$id = '';
		$dugme = 'Dodaj';

		include 'forma.html.php';
		exit();

	}

	//U koliko je u forma.html.php kliknuto na "Dodaj" submit dugme
	if(isset($_GET['dodajKategoriju'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//unosim ime kategorije u bazu 
		//koje mi je prosledjeno iz forma.html.php u promenljivoj $_POST['name']
		try{
			$sql = 'INSERT INTO category SET name = :name ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':name', $_POST['ime']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da dodamo kategoriju u bazu.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa kategorijama 
		exit();

	}


///////////////////////////////////////////////////////////////////////////////////////////////////


	//U koliko je u kategorije.html.php kliknuto 'Edit' submit dugme
	if(isset($_POST['action']) and $_POST['action'] == 'Edit'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//selektujem iz baze kategoriju 
		//koja mi je prosledjena iz kategorije.html.php u promenljivoj $_POST['id']
		try{
			$sql = 'SELECT id, name FROM category WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo kategoriju iz baze.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		// rezultat upita smestam u promenljivu $rezultat
		$rezultat = $s->fetch();

		// pripremam template promenljive koje posle saljem u forma.html.php
		$naslovStrane = 'Editovanje kategorije';
		$action = 'editujKategoriju';
		$ime = $rezultat['name'];
		$id = $rezultat['id'];
		$dugme = 'Izmeni';

		include 'forma.html.php';
		exit();
	}

	//U koliko je u forma.html.php kliknuto na "Izmeni" submit dugme
	if(isset($_GET['editujKategoriju'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		///menjam/update-ujem novo ime kategorije u bazu 
		//koje mi je prosledjeno iz forma.html.php u promenljivoj $_POST['name']
		try{
			$sql = 'UPDATE category SET name = :name WHERE id = :id ';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->bindValue(':name', $_POST['ime']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da dodamo kategoriju u bazu.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa kategorijama 
		exit();
	}


//////////////////////////////////////////////////////////////////////////////////////////////////////

	//U koliko je u kategorije.html.php kliknuto 'Delete' submit dugme
	if(isset($_POST['action']) and $_POST['action'] == 'Delete'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//brisem vezi izmedju tekstova koji su vezani sa kategorijom koju brisem
		// a ciji id mi je prosledjen od kategorije.html.php u promenljivoj $_POST['id']
		try{
			$sql = 'DELETE FROM postcategory WHERE categoryid = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da izbrisemo vezu post-category iz baze.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//brisem iz baze kategoriju koja je selektovana u kategorije.html.php
		// ciji id mi je prosledjen u promenljivoj $_POST['id']
		try{
			$sql = 'DELETE FROM category WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id', $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da izbrisemo kategorije iz baze.' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa kategorijama 
		exit();	

	}

///////////////////////////////////////////////////////////////////////////////////////////////////////

	// pravim listu kategorija
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

	try{
		$rezultat = $pdo->query('SELECT id, name FROM category'); // selektujem sve kategorije u bazi
	}
	catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
		$greska = 'Nismo uspeli da selektujemo kategorije iz baze.' . $e->getMessage(); // 
		include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
		exit();
	}

	//od rezultata upita pravim dvodimenzionalni niz $categories[] 
	//koji ce biti prosledjen u kategorije.html.php
	foreach($rezultat as $red){
		$categories[] = array('id' => $red['id'], 'name' => $red['name']);  
	}

	include 'kategorije.html.php'; 


?>