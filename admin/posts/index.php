<?php 
	include $_SERVER['DOCUMENT_ROOT'] . '/includes/magicquotes.inc.php';

	require_once $_SERVER['DOCUMENT_ROOT'] . '/includes/login.inc.php';

	if (!korisnikJeUlogovan()){
		include $_SERVER['DOCUMENT_ROOT'] . '/admin/login.html.php';
		exit();
	}
	if (!korisnikImaUlogu('Content Editor')){
		$greska = 'Jedino Content Editor-i imaju pristup ovoj stranici.';
		include $_SERVER['DOCUMENT_ROOT'] . '/admin/pristupZabranjen.html.php';
		exit();
	}


/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u koliko je u searchform.html.php kliknuto na link 'Dodajte novi tekst'
	if(isset($_GET['dodaj'])){

		//pravim template promenljive
		$naslovStrane = 'Dodavanje teksta';
		$action = 'dodajTekst';
		$title = '';
		$content = '';
		$authorid = '';
		$id = '';
		$dugme = 'Dodaj';

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
		//koji ce biti prosledjen u forma.html.php
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
			$categories[] = array('id' => $red['id'], 'name' => $red['name'], 'selected' => FALSE);  
		}


		//sve promenljive saljem u 
		include 'forma.html.php';
		exit();

	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u koliko je u forma.html.php kliknuto 'Dodaj' submit dugme
	if(isset($_GET['dodajTekst'])){

		// u koliko nije naveden autor, napisati gresku
		if($_POST['author'] == ''){
			$greska = 'Niste uneli autora teksta'; 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//
		try{
			$sql = 'INSERT INTO post SET
					  title = :title
					, content = :content
					, postdate = CURDATE()
					, authorid = :authorid';
			$s = $pdo->prepare($sql);
			$s->bindValue(':title', $_POST['title']);
			$s->bindValue(':content', $_POST['content']);
			$s->bindValue(':authorid', $_POST['author']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da unesemo tekst u bazu : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//da bih uneo i kategoriju teksta u bazu, potreban mi je id od prethodno unesenog teksta
		$postid = $pdo->lastInsertId();


		// u koliko je postavljena i kategorija		
		if (isset($_POST['categories'])){

			//sa tim id-em ubacujem novi unos u tabelu postcategory
			try{
				$sql = 'INSERT INTO postcategory SET
						  postid = :postid 
						, categoryid = :categoryid';
				$s = $pdo->prepare($sql);

				//zato sto se prilikom unosa moglo navesti vise kategorija kojima tekst pripada,
				//za svaku od tih kategorija unosimo id istiog/novog teksta 
				foreach($_POST['categories'] as $categoryid){
					$s->bindValue(':postid', $postid);
					$s->bindValue(':categoryid', $categoryid);
					$s->execute();
				}
			}
			catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da unesemo kategoriju u bazu : ' . $e->getMessage(); // 
				include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
				exit();
			}

		}
		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa tekstovima  
		exit();


	}

/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u koliko je u posts.html.php kliknuto 'Edit' submit dugme
	if(isset($_POST['action']) and $_POST['action'] == 'Edit'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//selektujem iz baze tekst koji je selektovan dugmetom 'Edit'
		try{
			$sql = 'SELECT id, title, content, authorid 
					FROM post
					WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':id' , $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo trazeni tekst : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//reyultat smestam u promenljivu $rezultat
		$rezultat = $s->fetch();

		//pravim template promenljive
		$naslovStrane = 'Editovanje teksta';
		$action = 'editujTekst';
		$title = $rezultat['title'];
		$content = $rezultat['content'];
		$authorid = $rezultat['authorid'];
		$id = $rezultat['id'];
		$dugme = 'Izmeni';

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


		//pravim listu kategorija kloje su vec dodeljene selektovanom tekstu
		try{
			$sql = 'SELECT categoryid 
					FROM postcategory
					WHERE  postid = :postid';
			$s = $pdo->prepare($sql);
			$s->bindValue(':postid' , $id);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo kategoriju : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		$rezultat = $s->fetchAll();

		//od rezultata prethodnog upita pravim niz $categoryid[] 
		//koji ce sadrzati id-eve svih kategorija koje su vec dodeljene selektovanom tekstu
		foreach($rezultat as $red){
			$categoryid[] = $red['categoryid'];  
		}


		//pravim listu kategorija koje se trenutno nalaze u bazi
		try{
			$rezultat = $pdo->query('SELECT id, name FROM category');
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da selektujemo postojece kategorije iz baze : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//od rezultata prethodnog upita pravim niz $categories[] 
		//koji ce sadrzati id-eve svih kategorija koje se trenutno nalaze u bazi, 
		//za one categoryid-eve koji su vec bili selektovani imati vrednost TRUE, a za ostale FALSE
		foreach($rezultat as $red){
			$categories[] = array('id' => $red['id'], 'name' => $red['name'], 'selected' => in_array($red['id'], $categoryid));  
		}
		

		//sve promenljive saljem u 
		include 'forma.html.php';
		exit();

	}



/////////////////////////////////////////////////////////////////////////////////////////////////////////

	
	// u koliko je prilikom editovanja teksta pritisnuto dugme 'Izmeni'
	if(isset($_GET['editujTekst'])){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//proveravam da li je prilikom izmene post-a izostavljeno da se navede autor
		//u koliko jeste ispisujem poruku o gresci
		if($_POST['author'] == ''){
			$greska = 'Prilikom editovanja, potrebno je selektovati autora : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}


		//update-ujem red u tabeli post ciji je id jednak prosledjenom id-u iz 'forma.html.php'
		try{
			$sql = 'UPDATE post SET
					  title = :title
					, content = :content
					, authorid = :authorid
					WHERE id = :id';
			$s = $pdo->prepare($sql);
			$s->bindValue(':title' , $_POST['title']);
			$s->bindValue(':content' , $_POST['content']);
			$s->bindValue(':authorid' , $_POST['author']);
			$s->bindValue(':id' , $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da unesemo izmenu u tekstu : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}


	//brisem dosadasnju vezu izmedju post-category tabela u postcategory tabeli
		try{
			$sql = 'DELETE FROM postcategory WHERE postid = :postid';
			$s = $pdo->prepare($sql);
			$s->bindValue(':postid' , $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo unos iz postcategory tabele : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}


		//u koliko je navedena i kategorija, unosim i nju u bazu
		if(isset($_POST['categories'])){

			//unosim nove kategorije koje us selektovane u tabelu postcategory
			try{
				$sql = 'INSERT INTO postcategory SET
						  postid = :postid 
						, categoryid = :categoryid';
				$s = $pdo->prepare($sql);

				//zato sto se prilikom unosa moglo navesti vise kategorija kojima tekst pripada,
				//za svaku od tih kategorija unosimo id istiog/novog teksta 
				foreach($_POST['categories'] as $categoryid){
					$s->bindValue(':postid', $_POST['id']);
					$s->bindValue(':categoryid', $categoryid);
					$s->execute();
				}
			}
			catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
				$greska = 'Nismo uspeli da unesemo kategoriju u bazu : ' . $e->getMessage(); // 
				include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
				exit();
			}
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa tekstovima  
		exit();
			

	}


/////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u koliko je u posts.html.php kliknuto 'Delete' submit dugme
	if(isset($_POST['action']) and $_POST['action'] == 'Delete'){

		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';

		//prvo brisem vezu teksta i njegove kategorije, odnosno unos u tabeli postcategory 
		try{
			$sql = 'DELETE FROM postcategory WHERE postid = :postid';
			$s = $pdo->prepare($sql);
			$s->bindValue(':postid' , $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo unos iz postcategory tabele : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		//nakon toga brisem iz baze selektovani tekst 
		try{
			$sql = 'DELETE FROM post WHERE id = :postid';
			$s = $pdo->prepare($sql);
			$s->bindValue(':postid' , $_POST['id']);
			$s->execute();
		}
		catch(PDOException $e){ // proveravam da li se prilikom izvrsavanja upita dogodila greska/izuzetak
			$greska = 'Nismo uspeli da obrisemo unos iz postcategory tabele : ' . $e->getMessage(); // 
			include 'greska.html.php'; // saljem promenljivu $greska na ispis u greska.html.php
			exit();
		}

		header('Location: .'); //posle obavljenog upita vracam se na stranicu sa tekstovima  
		exit();

	}



//////////////////////////////////////////////////////////////////////////////////////////////////////////

	//u koliko je u searchform.html.php kliknuto 'Search' submit dugme
	if(isset($_GET['action']) and $_GET['action'] == 'search'){

		//pravim default query promenljive
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


		//konektujem se na bazu
		include $_SERVER['DOCUMENT_ROOT'] . '/includes/db.inc.php';
		
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
				try{
				 	$sql = 'SELECT post.title, category.name 
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
					$postcategory[] = array('title' => $ime['title'], 'name' => $ime['name']);
				}

				try{	
					$sql = 'SELECT author.name, post.title
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
				$authorname[] = array('name' => $rezultat['name'], 'title' => $rezultat['title']);
			

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

		include 'posts.html.php';
		exit();

	}





////////////////////////////////////////////////////////////////////////////////////////////////////////


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

	include 'searchform.html.php'; 

?>