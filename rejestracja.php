<?php
	session_start();
	
	if(isset($_POST['email']))
	{
		//udana walidacja, załóżmy, że tak
		$wszystko_OK=true;
		
		//sprawdz nickname
		$nick = $_POST['nick'];
		
		//sprawdzenie długości nicka
		
		if (strlen($nick)<3 ||(strlen($nick)>20))
		{
			$wszystko_OK=false; //test się nie udał
			$_SESSION['e_nick']="Nick musi posiadać od 3 do 20 znaków";
		}
		
		// chcemy aby nick składał się ze znaków alfanumerycznych, a także bez polskich ogonków
		
		if(ctype_alnum($nick)==false)
		{
			$wszystko_OK=false; //test się nie udał
			$_SESSION['e_nick']="Nick może składać się tylko z liter i cyfr (bez polskich znaków)";
		}
		
		// sprawdzamy poprawnosc email, uzyjemy dwa razy funkcji filter_var
		$email=$_POST['email'];
		$emailB=filter_var($email, FILTER_SANITIZE_EMAIL);
		
		if ((filter_var($emailB, FILTER_VALIDATE_EMAIL)==false) || ($emailB != $email))
		{
			$wszystko_OK=false; //test się nie udał
			$_SESSION['e_email']="Podaj poprawny adres email";
		}
		
		
		//sprawdz poprawnosc hasel, zakładamy ze długość moze miec 8-20 znakow
		
		$haslo1 = $_POST['haslo1'];
		$haslo2 = $_POST['haslo2'];
		
		if((strlen($haslo1)<8) || (strlen($haslo1)>20))
		{
			$wszystko_OK=false; //test się nie udał
			$_SESSION['e_haslo']="Hasło musi mieć 8-20 znaków";
		}
		
		// walidacja z drugim hasłem
		
		if($haslo1 != $haslo2)
		{
			$wszystko_OK=false;
			$_SESSION['e_haslo']="Podane hasła różnią się!";
		}
		
		//hashujemy hasło 1
		
		$haslo_hash = password_hash($haslo1, PASSWORD_DEFAULT);
		
		// czy zaakceptowano regulamin
		if(!isset($_POST['regulamin']))
				{
			$wszystko_OK=false;
			$_SESSION['e_regulamin']="Potwierdź akceptację regulaminu!";
		}
		
		// BOT or not? This is a question...
		
		$sekret="6LfjIfkhAAAAAICEIXgf4SDvH5ipuDTe8Ks0GFot";
		
		
		$sprawdz= file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$sekret.'&response='.$_POST['g-recaptcha-response']);
		
		$odpowiedz= json_decode($sprawdz);
		
		if($odpowiedz->success==false)
		{
			$wszystko_OK=false;
			$_SESSION['e_bot']="Potwierdź że nie jesteś botem!";
		}
		
		// weryfikcja czy mail i login się nie powtarzają
		
		require_once "connect.php";
		mysqli_report(MYSQLI_REPORT_STRICT);
		
		try
			{
				$polaczenie = new mysqli($host, $db_user, $db_password,$db_name);
					if($polaczenie->connect_errno!=0)
					{
						throw new Exception(mysqli_connect_errno());
					}
					else
					{
						// czy email już istnieje
						$rezultat = $polaczenie->query("SELECT id FROM uzytkownicy WHERE email = '$email' ");
						//gdyby połączenie się nie udało
						if(!$rezultat) throw new Exception($polaczenie->error);
						
						$ile_takich_maili = $rezultat->num_rows;
						if($ile_takich_maili>0)
						{
							$wszystko_OK=false;
							$_SESSION['e_email']="istnieje już konto przypisane do tego adresu email";
						}
						
						// czy nick jest już zarezerwowany/ już istnieje
						$rezultat = $polaczenie->query("SELECT id FROM uzytkownicy WHERE user = '$nick' ");
						//gdyby połączenie się nie udało
						if(!$rezultat) throw new Exception($polaczenie->error);
						
						$ile_takich_nickow = $rezultat->num_rows;
						if($ile_takich_nickow>0)
						{
							$wszystko_OK=false;
							$_SESSION['e_nick']="istnieje już gracz o takim nicku, wybierz inny";
						}
						
						if($wszystko_OK==true)
						{
								//hurra, testy zaliczone dodajemy gracza do bazy
							if($polaczenie->query("INSERT INTO uzytkownicy VALUES (NULL, '$nick', '$haslo_hash', '$email', 100,100,100,14)"))
							{
								$_SESSION['udanarejestracja']=true;
								header('Location: witamy.php');
							}
							else
							{
								throw new Exception($polaczenie->error);
							}
						
						}
						
						$polaczenie ->close();
					}
			}
		catch(Exception $e)
			{
				echo '<span style="color: red;">Błąd serwera! Przepraszamy za niedogodności i prosimy o rejestrację w późniejszym terminie!</span>';
				echo '<br />Informacja deweloperska: '.$e;
			}
		

	}
?>

<!DOCTYPE HTML>
<html lang="pl">
<head>
	<meta charset = "utf-8"/>
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome1=1" />
	<title> Osadnicy - załóż darmowe konto </title>
	
   
	 <script src="https://www.google.com/recaptcha/api.js" async defer></script>
	 
	 
	 <style>
	 .error
	 {
		 color: red;
		 margin-top: 10px;
		 margin-bottom: 10px;
	 }
	 </style>
	
</head>
<body>
	
	
	<form method ="POST">
	
		Nickname: <br /> <input type="text" name="nick" /><br />
		<?php
			if(isset($_SESSION['e_nick']))
			{
				echo '<div class="error">'.$_SESSION['e_nick'].'</div>';
				unset($_SESSION['e_nick']);
				
			}
		?>
		e-mail: <br /> <input type="text" name="email" /><br />
		<?php
			if(isset($_SESSION['e_email']))
			{
				echo '<div class="error">'.$_SESSION['e_email'].'</div>';
				unset($_SESSION['e_email']);
			}
		?>
		
		
		Twoje hasło: <br /> <input type="password" name="haslo1" /><br />
				<?php
			if(isset($_SESSION['e_haslo']))
			{
				echo '<div class="error">'.$_SESSION['e_haslo'].'</div>';
				unset($_SESSION['e_haslo']);
			}
		?>
		
		
		Powtórz hasło: <br /> <input type="password" name="haslo2" /><br />
		
		<label>
		<input type="checkbox" name="regulamin"/> Akceptuję regulamin
		</label>
		<?php
			if(isset($_SESSION['e_regulamin']))
			{
				echo '<div class="error">'.$_SESSION['e_regulamin'].'</div>';
				unset($_SESSION['e_regulamin']);
			}
		?>
		
		
		
		<br />
		<br />
			
		 <div class="g-recaptcha" data-sitekey="6LfjIfkhAAAAAIbNcU2FsTm4OFaWTO9PEOMvo5TZ"></div>
          	<?php
			if(isset($_SESSION['e_bot']))
			{
				echo '<div class="error">'.$_SESSION['e_bot'].'</div>';
				unset($_SESSION['e_bot']);
			}
			?>
		 <br />
		
		<input type="submit" value="Zarejestruj się" />

        </form>


</body>
</html>