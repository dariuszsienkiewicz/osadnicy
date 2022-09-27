<?php
	session_start();
	
	if(!isset($_SESSION['zalogowany']))
	{
		header('Location: index.php');
		exit();
	}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome1=1" />
	
	<title> Osadnicy - gra przeglądarkowa </title>
</head>
<body>

<?php

echo "<p>Witaj ".$_SESSION['user'].'! [ <a href="logout.php">Wyloguj się!</a> ]</p>';

echo "<p><strong>Drewno</strong>: ".$_SESSION['drewno'];
echo " | <b>Kamień</b>: ".$_SESSION['kamien'];
echo " | <strong>Zboże</strong>: ".$_SESSION['zboze'];
echo "<br /><br />E-mail: ".$_SESSION['email']."</p>";
echo "<p><b>Dni premium: ".$_SESSION['dnipremium']."</p>";
?>

</body>
</html>