<!DOCTYPE html>
<html>
<head>
	<title>Авторизация пользователя</title>
</head>
<body>
<?php 
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=todo", "Miha", "Qwerty123");
$sql = "SELECT * FROM user";

if (!empty($_GET['enter'])) {
	$md5 = md5($_GET['password']);
	foreach ($pdo->query($sql) as $value) {
		if ($value['login'] == $_GET['login'] && $value['password'] == $md5) {
			$_SESSION['login'] = $_GET['login'];
			$_SESSION['id'] = $value['login'];
			header('location: MySQL2.php');
		}
	} 
	echo 'Неправильное имя или пароль';
} 

if (!empty($_GET['register'])) {
		foreach ($pdo->query($sql) as $value) {
			if ($value['login'] == $_GET['login']) {
				echo 'Такой пользователь уже существуует, попробуйте другое имя';
			} 
		} 	

		$_SESSION['login'] = $_GET['login'];
		$md5 = md5($_GET['password']);
		$newuser = $pdo->prepare("INSERT INTO user (login, password) VALUES (:login, :password)");
		$newuser->bindParam(':login', $_GET['login']);
		$newuser->bindParam(':password', $md5);
		$newuser->execute();
		header('location: MySQL2.php');
}

?>
 	
<form action="" method="GET">
	<p><input type="text" name="login"></p>
	<p><input type="text" name="password"></p>
	<p><button name="enter" value="enter">Войти</button></p>
	<p><button name="register" value="register">Зарегестрироваться</button></p>
</form>
</body>
</html>
