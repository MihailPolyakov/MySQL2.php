<!DOCTYPE html>
<html>
<head>
	<title>Books</title>
	<meta charset="utf-8">
</head>
<body>
	<style>
		table{
			border-collapse: collapse;
			border: 1px solid black;
		}
		td{
			border-collapse: collapse;
			border: 1px solid black;
		}
	</style>

<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=todo", "Miha", "Qwerty123");
$users = "SELECT * FROM user";
$sql = "SELECT * FROM task";
$join = 'SELECT task.id, task.description, task.date_added, task.is_done, task.user_id, task.assigned_user_id, user.login, user.id as id_users from task JOIN user on task.user_id = user.id';

//удялаяем сессию при выходе с приложения и возвращаемся на момент авторизации
if (!empty($_GET['session'])) {
	session_destroy();
	?>
	<a href="enterforsql.php">Войти на сайт</a>
	<?php exit;
}


if (!$_SESSION['login']) {?>
	<a href="enterforsql.php">Войти на сайт</a>
<?php } else {
//Создание переменной $_SESSION['id']
foreach ($pdo->query($users) as $value) {
	if ($value['login'] == $_SESSION['login']) {
		$_SESSION['id'] = $value['id'];
		break;
	}
}
echo "Добро пожаловать" . ' ' . $_SESSION['login'];
 if (!empty($_GET['edit'])) {
 	$edit=$_GET['edit'];
 	$id=(int)$_GET['id'];
	$done=$pdo->prepare("UPDATE task SET description='$edit' WHERE id=$id");
	$done->execute();
 }


 if (!empty($_POST['description'])){
	$value='В процессе';
	$you = 'Вы';
	$stmt = $pdo->prepare("INSERT INTO task (description, is_done, user_id, assigned_user_id) VALUES (:description, :is_done, :user_id, :assigned_user_id)");
	$stmt->bindParam(':description', $_POST['description']);
	$stmt->bindParam(':is_done', $value);
	$stmt->bindParam(':user_id', $_SESSION['id']);
	$stmt->bindParam(':assigned_user_id', $_SESSION['id']);
	$stmt->execute();
 }

if (!empty($_POST['select'])) {
	//Находим ID логина и добавляем в переменную
	foreach ($pdo->query($users) as $value) {
		if ($value['login'] == $_POST['select']) {
			$id_users = $value['id'];
		}
	}
	$id = $_POST['id'];
	$select = $_POST['select'];
	$updateuser = $pdo->prepare("UPDATE task set assigned_user_id = '$id_users' where id = $id");
	$updateuser->execute();
}

if (!empty($_GET['action'])){
	if ($_GET['action']=='delete') {
		$id=(int)$_GET['id'];
		$delete=$pdo->prepare("DELETE FROM task WHERE id=:id");
		$int=(int)$_GET['id'];
		$delete->execute(['id'=>$int]);
	} elseif ($_GET['action']=='done') {
		$id=(int)$_GET['id'];
		$done=$pdo->prepare("UPDATE task SET is_done='Выполнено' WHERE id=$id");
		$done->execute();
	}
	
}


if (!empty($_GET['action']=='edit')) {?>
	<form action="" method="GET">
	    <input type="text" name="edit" placeholder="Введите изменение">
	    <input type="hidden" name="id" value="<?php echo $_GET['id']?>">
	    <input type="submit" value="Сохранить">
	</form>	
<?php } else {?>
	<form action="" method="POST">
	    <input type="text" name="description" placeholder="Описание задачи">
	    <input type="submit" value="Добавить">
	</form>
<?php } ?>

<!-- Задачи пользователя где он является автором-->
<table >
	<tr>
		<td>Задача</td>
		<td>Дата добавления</td>
		<td>Статус</td>
		<td></td>
		<td>Автор</td>
		<td>Ответсвенный</td>
		<td>Закрепить задачу за пользователем</td>
	</tr>
	<?php foreach ($pdo->query($join) as $value) {
		//Делаем проверку для какого пользователя нам отображать задачи созданные им
		if ($_SESSION['id'] == $value['user_id']) {?>
			<tr>
				<td><?php echo $value['description'];?></td>
				<td><?php echo $value['date_added'];?></td>
				<td><?php echo $value['is_done'];?></td>
				<td>
					<a href='?id=<?php echo $value['id']?>&action=edit'>Изменить</a>
			        <a href='?id=<?php echo $value['id']?>&action=done'>Выполнить</a>
			        <a href='?id=<?php echo $value['id']?>&action=delete'>Удалить</a>
				</td>
					<td><?php echo $value['login'];?></td>
				    
				    <?php
					//Находим под каким пользователем закреплена задача				
					foreach ($pdo->query($users) as $id_users) {
						if ($id_users['id'] == $value['assigned_user_id']) {?>
							<td><?php echo $id_users['login'];?></td>
						<?php break;}

					}?>
				
				<td>
					<form method="POST">
						<input type="hidden" name="id" value="<?php echo $value['id']?>">
						<select name="select">
							<?php
							foreach ($pdo->query($users) as $value) {?>
								<option><?php echo($value['login'])?></option>
							<?php } ?>
						</select>
						<input type="submit" value="Выбрать из списка">
					</form>
				</td>
			</tr>
	
		<?php } ?>

    <?php }?>
</table><br>
<!-- Задачи переложенные от других пользователей-->
<table >
	<tr>
		<td>Задача</td>
		<td>Дата добавления</td>
		<td>Статус</td>
		<td></td>
		<td>Автор</td>
		<td>Ответсвенный</td>
	</tr>
	<?php foreach ($pdo->query($join) as $value) {
		//Делаем проверку какие задачи ереложили данному пользователю
			if ($_SESSION['id'] == $value['assigned_user_id'] && $_SESSION['id'] != $value['user_id']) {?>
			<tr>
				<td><?php echo $value['description'];?></td>
				<td><?php echo $value['date_added'];?></td>
				<td><?php echo $value['is_done'];?></td>
				<td>
			        <a href='?id=<?php echo $value['id']?>&action=done'>Выполнить</a>
				</td>
					<td><?php echo $value['login'];?></td>
				    
				    <?php
					//Находим под каким пользователем закреплена задача				
					foreach ($pdo->query($users) as $id_users) {
						if ($id_users['id'] == $value['assigned_user_id']) {?>
							<td><?php echo $id_users['login'];?></td>
						<?php break;
						}	
					}?>
			</tr>			
		<?php } 
        }?>
</table>		

<a href="?session=delete">Выйти</a>
<?php } ?>
</body>
</html>
