<?php
session_start();

$pdo = new PDO(
  'mysql:host=localhost;dbname=u82373;charset=utf8',
  'u82373',
  '4362231'
);

if ($_SERVER['REQUEST_METHOD']=='POST') {

  $login=$_POST['login'];
  $pass=$_POST['password'];

  $stmt=$pdo->prepare("SELECT * FROM applications WHERE login=?");
  $stmt->execute([$login]);
  $user=$stmt->fetch();

  if ($user && password_verify($pass,$user['password_hash'])) {
    $_SESSION['user_id']=$user['id'];
    header("Location: index.php");
    exit();
  }

  echo "Ошибка входа";
}
?>

<form method="POST">
<input name="login" placeholder="логин">
<input name="password" type="password" placeholder="пароль">
<button>Войти</button>
</form>
