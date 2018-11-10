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
$users = "SELECT id, login FROM user";

//проверяем на наличия сессии или удялаяем сессию при выходе с приложения и возвращаемся на момент авторизации
if (!$_SESSION['login'] || !empty($_GET['session'])) {
	session_destroy();?>
	<a href="autorisation2.php">Войти на сайт</a>
<?php } else {
	//Создание переменной $_SESSION['id']
	foreach ($pdo->query($users) as $value) {
		if ($value['login'] == $_SESSION['login']) {
			$userid = $value['id'];
			break;
		}
	}
	//Делаем запрос на присоединение к БД task, где автор задачи ID-пользователя
	$join = "SELECT task.id, task.description, task.date_added, task.is_done, task.user_id, task.assigned_user_id, user.login, user.login as assigned_login, user.id as id_users from task JOIN user on task.user_id = user.id WHERE task.user_id = $userid ORDER BY task.date_added";

	//Делаем запрос на присоединение к БД task, где ответственный задачи ID-пользователя и автор не ID пользователя
	$secondjoin = "SELECT task.id, task.description, task.date_added, task.is_done, task.user_id, task.assigned_user_id, user.login, user.id as id_users from task JOIN user on task.user_id = user.id WHERE task.assigned_user_id = $userid and task.user_id != $userid ORDER BY task.date_added";

	echo "Добро пожаловать" . ' ' . $_SESSION['login'];
 if (!empty($_GET['edit'])) {
 	$edit=$_GET['edit'];
 	$id=(int)$_GET['id'];
	$done=$pdo->prepare("UPDATE task SET description='$edit' WHERE id=$id");
	$done->execute();
 }


 if (!empty($_POST['description'])){
	$value = 'В процессе';
	$stmt = $pdo->prepare("INSERT INTO task (description, date_added, is_done, user_id, assigned_user_id) VALUES (:description, :date_added, :is_done, :user_id, :assigned_user_id)");
	$stmt->bindParam(':description', $_POST['description']);
	$stmt->bindParam(':date_added', $_POST['Date']);
	$stmt->bindParam(':is_done', $value);
	$stmt->bindParam(':user_id', $userid);
	$stmt->bindParam(':assigned_user_id', $userid);
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
	} elseif ($_GET['action'] == 'didnotdo') {
		$id=(int)$_GET['id'];
		$done=$pdo->prepare("UPDATE task SET is_done='Не выполнено' WHERE id=$id");
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
	    <input type="date" name="Date">
	    <input type="submit" value="Добавить">
	</form>
<?php } ?>

<!-- количество дел на сегодня-->
<table>
	<tr>
		<td>Количество дел</td>
	</tr>
	<?php 
		$count = "SELECT count(*) from task t WHERE t.user_id = $userid OR t.assigned_user_id = $userid";
		foreach ($pdo->query($count) as $value) {?>
			<tr>
				<td><?php echo $value[0];?></td>
			</tr>			
	    <?php }?>
</table><br>	

<!-- Задачи пользователя где он является автором-->
<table >
	<tr>
		<td>Задача</td>
		<td>До какого выполнить</td>
		<td>Статус</td>
		<td></td>
		<td>Автор</td>
		<td>Ответсвенный</td>
		<td>Закрепить задачу за пользователем</td>
	</tr>
	<?php foreach ($pdo->query($join) as $value) {?>
			<tr>
				<td><?php echo $value['description'];?></td>
				<td><?php echo $value['date_added'];?></td>
				<td><?php echo $value['is_done'];?></td>
				<td>
					<a href='?id=<?php echo $value['id']?>&action=edit'>Изменить</a>
			        <a href='?id=<?php echo $value['id']?>&action=done'>Выполнено</a>
			        <span>/<span>
			        <a href='?id=<?php echo $value['id']?>&action=didnotdo'>Не выполнено</a>	
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
						<input type="submit" value="Делегировать">
					</form>
				</td>
			</tr>

    <?php }?>
</table><br>

<!-- Задачи переложенные от других пользователей-->
<table >
	<tr>
		<td>Задача</td>
		<td>До какого выполнить</td>
		<td>Статус</td>
		<td></td>
		<td>Автор</td>
		<td>Ответсвенный</td>
	</tr>
	<?php foreach ($pdo->query($secondjoin) as $value) {?>
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
       <?php }?>
</table>		

<a href="?session=delete">Выйти</a>
<?php } ?>
</body>
</html>
