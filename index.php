<?php

	if(isset($_GET['pretraga'])){

			//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//pravim listu trenutnih autora koji se nalaze u bazi
		try{
			$rezultat = $pdo->query('SELECT id, name FROM author');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo autore iz baze : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//od rezultata prethodnog upita pravim niz $authors[] 
		//koji ce biti prosledjen u searchform.html.php
		foreach($rezultat as $red){
			$authors[] = array('id' => $red['id'], 'name' => $red['name']);  
		}

		//pravim listu trenutnih kategorija koji se nalaze u bazi
		try{
			$rezultat = $pdo->query('SELECT id, name FROM category');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo kategorije iz baze : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//od rezultata prethodnog upita pravim niz $categories[] 
		//koji ce biti prosledjen u searchform.html.php
		foreach($rezultat as $red){
			$categories[] = array('id' => $red['id'], 'name' => $red['name']);  
		}

		include 'pretraga.html';
		exit();
	}


	//u koliko je u searchform.html.php kliknuto 'Search' submit dugme
	if(isset($_GET['action']) and $_GET['action'] == 'search'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//pravim template query promenljive
		$select = 'SELECT id, title, content ';
		$from = ' FROM post ';
		$where = ' WHERE TRUE ';

		$placeholders = array();


		//u koliko je u searchform.html.php obelezen autor
		if($_GET['author'] != ''){
			$where .= ' AND authorid = :authorid';
			$placeholders[':authorid'] = $_GET['author'];
		}

		//u koliko je u searchform.html.php obelezea kategorija
		if($_GET['category'] != ''){
			$from .= ' INNER JOIN postcategory ON post.id = postid ';
			$where .= ' AND categoryid = :categoryid';
			$placeholders[':categoryid'] = $_GET['category'];
		}

		//u koliko je u searchform.html.php naveden naslov ili deo naslova
		if($_GET['title'] != ''){
			$where .= ' AND title LIKE :title';
			$placeholders[':title'] = '%' . $_GET['title'] . '%';
		}
		
		//pravim upit od prethodno formiranih template promenljivih
		try{
			$sql = $select . $from . $where;
			$s = $pdo->prepare($sql);
			$s->execute($placeholders);
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da izvrsimo pretragu : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//formiram niz $posts[] koji saljem u posts.html.php
		foreach($s as $red){
			$posts[] = array('id' => $red['id'], 'title' => $red['title'], 'content' => $red['content']); 
		}


		//za svaki od tih tekstova odredjujem kojim kategorijama pripada
		if(isset($posts)){
			foreach($posts as $red){

				// iz baze trazim sve kategorije kojima pripada tekst 
				//koj se trenutno obradjuje u petlij
				try{
				 	$sql = 'SELECT post.id, category.name 
				 			FROM post 
				 					INNER JOIN postcategory ON post.id = postid
				 					INNER JOIN category ON categoryid = category.id
				 			WHERE post.id = :id';
					 	$s = $pdo->prepare($sql);
						$s->bindValue(':id', $red['id']);
						$s->execute();
					}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
						$greska = 'Nismo uspeli da selektujemo autora: ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();

				}	
				
				foreach($s as $ime){
					$postcategories[] = array('id' => $ime['id'], 'name' => $ime['name']);
				}

				try{	
					$sql = 'SELECT author.name, post.id
							FROM author
									INNER JOIN post ON author.id = authorid
							WHERE post.id = :id';
						 	$s = $pdo->prepare($sql);
							$s->bindValue(':id', $red['id']);
							$s->execute();
				}
				catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
						$greska = 'Nismo uspeli da selektujemo autora: ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();

				}	

				$rezultat = $s->fetch();
				$authornames[] = array('name' => $rezultat['name'], 'id' => $rezultat['id']);
			

			}

		}

		//preuzimam slike autora iz baze
		try{
			$rezultat = $pdo->query('SELECT post.id, slika.name
								 FROM slika
									INNER JOIN author ON slika.authorid = author.id
									INNER JOIN post ON author.id = post.authorid');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da selektujemo slike iz baze: ' . $e->getMessage(); // 
							include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
							exit();
				}	

		foreach($rezultat as $red){
			$pictures[] = array('id' => $red['id'], 'name' => $red['name']);
		}


		include 'pretragaOut.html';
		exit();
	}



	//pravim listu autora koji se trenutno nalaze u bazi
	if(isset($_GET['autori'])){

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

		//preuzimam slike autora iz baze
		try{
			$rezultat = $pdo->query('SELECT author.id, slika.name
									 FROM slika
										INNER JOIN author ON slika.authorid = author.id');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
					$greska = 'Nismo uspeli da selektujemo slike iz baze: ' . $e->getMessage(); // 
							include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
							exit();
				}	

		foreach($rezultat as $red){
			$pictures[] = array('id' => $red['id'], 'name' => $red['name']);
		}

		include 'autori.html';
		exit();

	}

	if(isset($_GET['kategorije'])){

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

		include 'kategorije.html'; 
		exit();

	}

		///////////////////////////////////////////////////////////////////////////////////////////
		////////////////////////////Pravljenje navigacvionih linkova na dnu stranice///////////////
		///////////////////////////////////////////////////////////////////////////////////////////
		///////////////////////////////////////////////////////////////////////////////////////////


	//konektujem se na bazu
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';


			//proveravam koliko ima redova u tabeli posts
	try{
		$rezultat = $pdo->query('SELECT COUNT(*) FROM post');
					
	}
	catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
		$greska = 'Nismo uspeli da prebrojimo tekstove iz baze : ' . $e->getMessage(); // 
		include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
		exit();
	}

	//broj redova stavljam u pormenlivu $brRedova
	$pom = $rezultat->fetch();
	$brRedova = $pom[0];
	


	//odredjujem koliko zelim postova po stranici
	$postovaPoStranici = 2;
	//proveravam koliko imam ukupno stranica u tom slucaju
	$ukupnoStranica =  ceil($brRedova / $postovaPoStranici);

	
	//inicijalizujem pocetnu stranicu
	if(isset($_GET['page']) /*and is_numeric($_GET['page'])*/){
		$tekucaStranica = (int) $_GET['page'];
	}
	else{
		$tekucaStranica = 1;
	}

	//u koliko je tekuca stranica prekoracila opseg, ili ako neko rucno unese u URL 
	//neispravnu vrednost
	if($tekucaStranica > $ukupnoStranica){
		// postavljam tekucu stranicu na poslednju
		$tekucaStranica = $ukupnoStranica;
	}

	//u koliko je tekuca stranica prekoracila opseg, ili ako neko rucno unese u URL
	//neispravnu vrednost
	if($tekucaStranica < 1){
		// postavljam tekucu stranicu na poslednju
		$tekucaStranica = 1;
	}

	//definisem pocetnu poziciju od koje cu traziti tekst u bazi
	$pocetak = ($tekucaStranica -1 ) * 2;





	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////Ispis tekstova /////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////



	

	// pravim listu svih tekstova na sajtu
	//preuzimam sve postove koji se nalaze u bazi
	try{
		$rezultat = $pdo->query('SELECT post.id, post.title, post.content, post.authorid
				FROM post
				LIMIT ' .$pocetak.', '. $postovaPoStranici);
	}
	catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo tekstove iz baze: ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

	//pravim niz koji ce sadrzati naslov i sadrzaj posta

//	while ($row = $rezultat->fetch()){
		// za svaki od tekstova koji se nalaze u bazi selektujemo autora i kategorije kojim pripada
		foreach($rezultat as $red){

			//pravim niz koji ce sadrzati naslov i sadrzaj posta
			$posts[] = array('title' => $red['title'], 'content' => $red['content'], 'id' => $red['id']);

			//onda selektujem autora trenutnog teksta
			try{	
				$sql = 'SELECT  author.name
						FROM post
							INNER JOIN author ON author.id = authorid
						WHERE post.id = :id';
						$s = $pdo->prepare($sql);
						$s->bindValue(':id', $red['id']);
						$s->execute();
			}
			catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da selektujemo autora: ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();
			}	

			//pravim niz u kojem ce biti smestena imena autora u bazi
			$rez = $s->fetch();
			$author[] = $rez['name'];

			//nakon toga selektujem id teksta i sva imena kategorija kojima pripada tekst 
			//sve cu to staviti u jedan niz pa cu u html fajlu proveravati da ako se slaze id teksta
			//koji se ispisuje i id teksta u ovom nizu onda ispisujem i imena kategorija 
			//sacuvane u ovom nizu
			try{	
				$sql = 'SELECT  post.id, category.name
						FROM post
							INNER JOIN postcategory ON post.id = postid
							INNER JOIN category ON categoryid = category.id 
						WHERE post.id = :id';
						$s = $pdo->prepare($sql);
						$s->bindValue(':id', $red['id']);
						$s->execute();
			}
			catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da selektujemo id i categoryname: ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();
			}	

			foreach($s as $row){
				$categories[] = array('id' => $red['id'], 'name' => $row['name']);
			}

		}
		 // ova promenljiva i treba da ispisem autore u html-u



	///////////////////////////////////////////////////////////////////////////////////////////
	//////////////////////////////Nastavak koda za ispis navigacionih linkova na dnu stranice
	///////////////////////////////////////////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////////////////////////


	//pravljenje linkova na dnu stranice
	$raspon = 3;

	$ispis = '';
	//ako je na prvoj stranici ne pokazuju linkove za 'back'
	if($tekucaStranica > 1){
		//pokazi link  '<<' za povratak na prvu stranicu
		$ispis .= '<a href="?page=1"> << </a>';
		$prethodna = $tekucaStranica - 1;
		//pokazi link  '<' za povratak na prethodnu stranicu
		$ispis .= '<a href="?page='.$prethodna.'"> < </a>';
	}



	//petlja koja ce prikazati potrebne brojeve stranica
	for($i = ($tekucaStranica - $raspon); $i < (($tekucaStranica+$raspon)+1); $i++){
		// u koliko je ispravan broj strane
		if(($i>0) and ($i<=$ukupnoStranica)){
			if($i == $tekucaStranica){
				//obelezi, ali ne treba praviti link
				$ispis .= ' [<b>'.$i.'</b>] ';
			}
			//u koliko nije na tekucoj stranici
			else{
				//napravi link
				$ispis .= '<a href="?page='.$i.'"> ['.$i.'] </a>';
			}
		}
	}

	//ako je na poslednjoj stranici ne pokazuju se linkovi za 'back'
	if($tekucaStranica != $ukupnoStranica){
		
		//sledeca stranica
		$sledecaStranica = $tekucaStranica + 1;

		$ispis .= '<a href="?page='.$sledecaStranica.'"> > </a>';

		$ispis .= '<a href="?page='.$ukupnoStranica.'"> >> </a>';
	}


	//selektujem iz baze slike od autora i id-jeve postova kojie su ti autori napisali da bih u html
	//kodu mogao da isproveravam koja slika pripada kojem tekstu
	try{
		$rezultat = $pdo->query('SELECT post.id, slika.name
								 FROM slika
									INNER JOIN author ON slika.authorid = author.id
									INNER JOIN post ON author.id = post.authorid');
	}
	catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da selektujemo slike iz baze: ' . $e->getMessage(); // 
						include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
						exit();
			}	

		foreach($rezultat as $red){
			$pictures[] = array('id' => $red['id'], 'name' => $red['name']);
		}

	include  'index.html';

?>