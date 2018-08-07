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
$pdo = new PDO("mysql:host=localhost;dbname=books", "Miha", "Qwerty123");

 if (!empty($_POST['description'])){
	$value='В процессе';
	$stmt = $pdo->prepare("INSERT INTO tasks (description, is_done) VALUES (:description, :is_done)");
	$stmt->bindParam(':description', $_POST['description']);
	$stmt->bindParam(':is_done', $value);
	$stmt->execute();
 }	
if (!empty($_GET)){
	$id=(int)$_GET['id'];
	$pdo->query("DELETE FROM tests WHERE id=$id");
	/*$delete=$pdo->prepare("DELETE FROM tests WHERE id=:id");
	$int=(int)$_GET['id'];
	$delete->execute(['id'=>$int]);*/
	var_dump($id);
}


$sql = "SELECT * FROM tasks";?>

<form action="" method="POST">
    <input type="text" name="description" placeholder="Описание задачи" />
    <input type="submit" value="Добавить" />
</form>

<table >
	<tr>
		<td>Задача</td>
		<td>Дата добавления</td>
		<td>Статус</td>
		<td></td>
	</tr>
	<?php foreach ($pdo->query($sql) as $value) {?>
	<tr>
		<td><?php echo $value['description'];?></td>
		<td><?php echo $value['date_added'];?></td>
		<td><?php echo $value['is_done'];?></td>
		<td>
			<a href=''>Изменить</a>
	        <a href=''>Выполнить</a>
	        <a href='?id=<?php echo $value['id']?>'>Удалить</a>
		</td>
	<?php } ?>
	</tr>
</table>

</body>
</html>