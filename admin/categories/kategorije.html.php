<?php include $_SERVER['DOCUMENT_ROOT'] . '/includes/htmlout.inc.php' ;?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Kategorije</title>
	<link rel="stylesheet" type="text/css" href="../../style1.css">
</head>
<body>
<div id="wrapper">
	<header>
		<img src="../../Slike/blog.gif" id="logo">
		<img src="../../Slike/baner2.png" id="baner">
		<a id="login" href="">Log in</a>
		<nav>
			<ul>
				<li><a href="../../?">Home</a></li>
				<li><a href="../../?kategorije">Kategorije</a></li>
				<li><a href="../../?autori">Autori</a></li>
				<li><a href="../../?pretraga">Pretraga</a></li>
			</ul>
		</nav>
	</header>
	<section>
			<h1>Kategorije: </h1>
			<div class="content">
			<table>
				<thead>
					<tr>
						<td><b>Autori</b></td>
						<td><b>Opcije</b></td>
					</tr>
				</thead>
				<tbody>
					<?php foreach($categories as $category): ?>
						<tr>
							<td><p><?php htmlout($category['name']); ?></p></td>
							<td>
								<form action="?" method="post">
									<div>
											<input type="hidden" name="id" value="<?php htmlout($category['id']);?>">
											<input type="submit" name="action" value="Edit">
											<input type="submit" name="action" value="Delete">	
									</div>
								</form>	
							</td>
					<?php endforeach; ?>
				</tbody>
			</table>
			<p><a href="..">Nazad na Kontrolu Sadrzaja</a></p><p><a href="?dodaj">Dodajte novu kategoriju</a></p>
			<?php include '../logout.inc.html.php'; ?>
		<div>
	</section>
	<aside>
		<div>
			<h3>Prijatelji sajta : </h3>
			<br>
			<p>
				<h3>Blog b92</h3>
				<img src="../../Slike/b92.png">
				<p>
					 Blog drugo ime za slobodu, pribežiste nezavinog novinartva, paralelna stvarnost aktuelnoj, u nekoliko i vrsta rijalitija, s obziom na ekstrovertnost koja je odlika pisanja na blogu. Svako ko ima ppotrebu da iznese lični stav može to da uradi na blogu koji je neka vsta ličnog dnevnika. <a href="http://blog.b92.net/">Posetite nas</a> 
				</p>
			</p>
			<hr>
			<br>
			<p>
				<h3>php.net</h3>
				<img src="../../Slike/php.png">
				<p>
					 SPHP (recursive acronym for PHP: Hypertext Preprocessor) is a widely-used open source general-purpose scripting language that is especially suited for web development and can be embedded into HTML.
					 Nice, but what does that mean? <a href="http://php.net">Check on</a> </a> 
				</p>
			</p>
			<hr>
			<br>
			<p>
				<h3>Stackoverflow</h3>
				<img src="../../Slike/stackoverflow.png">
				<p>
					Stack Overflow is a question and answer site for professional and enthusiast programmers. It's built and run by you as part of the Stack Exchange network of QandA sites. With your help, we're working together to build a library of detailed answers to every question about programming.  This site is all about getting answers. It's not a discussion forum. There's no chit-chat.<a href="https://www.stackoverflow.com">Check on</a>
				</p>
			</p>
			<hr>
			<br>
			<p>
				<h3>health-alt</h3>
				<img src="../../Slike/health-alt.png">
				<p>
					 health-alt je blog o kozmetici otvoren februara 2014. godine, koji se fokusira isključivo na prirodnu kozmetiku i prirodne, zdrave zamene za industrijske proizvode → healthy alternatives, odakle dolazi i naziv samog bloga. <a href="http://health-alt.tumblr.com/about">Proverite ovde</a> 
				</p>
			</p>
		</div>
	</aside>
</div>
</body>
</html>