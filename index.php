<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$pdo = new PDO(
  'mysql:host=localhost;dbname=u82373;charset=utf8',
  'u82373',
  '4362231',
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$langs = [
  1=>'Pascal',2=>'C',3=>'C++',4=>'JavaScript',
  5=>'PHP',6=>'Python',7=>'Java',8=>'Haskell',
  9=>'Clojure',10=>'Prolog',11=>'Scala',12=>'Go'
];

if (isset($_GET['logout'])) {
  session_destroy();
  header("Location: index.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

  $messages = [];

  if (!empty($_COOKIE['save_success'])) {
    $messages[] = $_COOKIE['save_success'];
    setcookie('save_success', '', 100000);
  }

  if (!empty($_COOKIE['login_data'])) {
    $messages[] = $_COOKIE['login_data'];
    setcookie('login_data', '', 100000);
  }

  if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM applications WHERE id=?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();

    $values = [
      'fio'=>$row['fio'],
      'phone'=>$row['phone'],
      'email'=>$row['email'],
      'birth_date'=>$row['birth_date'],
      'gender'=>$row['gender'],
      'biography'=>$row['biography'],
      'contract'=>1,
      'languages'=>$pdo->query("SELECT language_id FROM application_languages WHERE application_id=".$row['id'])->fetchAll(PDO::FETCH_COLUMN)
    ];
  } else {
    $values = [
      'fio' => $_COOKIE['fio_value'] ?? '',
      'phone' => $_COOKIE['phone_value'] ?? '',
      'email' => $_COOKIE['email_value'] ?? '',
      'birth_date' => $_COOKIE['birth_value'] ?? '',
      'gender' => $_COOKIE['gender_value'] ?? '',
      'biography' => $_COOKIE['bio_value'] ?? '',
      'contract' => $_COOKIE['contract_value'] ?? '',
      'languages' => isset($_COOKIE['languages_value'])
        ? explode(',', $_COOKIE['languages_value'])
        : []
    ];
  }

  $errors = [];

  foreach (['fio','phone','email','birth','gender','languages','bio','contract'] as $f) {
    if (!empty($_COOKIE[$f.'_error'])) {
      $errors[$f] = $_COOKIE[$f.'_error'];
      setcookie($f.'_error', '', 100000);
    }
  }

  include 'form.php';
  exit();
}

$errors = [];

$fio = $_POST['fio'] ?? '';
if ($fio === '' || !preg_match('/^[a-zA-Zа-яА-ЯёЁ\s]+$/u', $fio)) {
  setcookie('fio_error', 'Только буквы и пробелы');
  $errors['fio'] = true;
}

$phone = $_POST['phone'] ?? '';
if ($phone === '' || !preg_match('/^[0-9+\-\s()]+$/', $phone)) {
  setcookie('phone_error', 'Некорректный телефон');
  $errors['phone'] = true;
}

$email = $_POST['email'] ?? '';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
  setcookie('email_error', 'Некорректный email');
  $errors['email'] = true;
}

$birth = $_POST['birth_date'] ?? '';
if ($birth === '' || !strtotime($birth)) {
  setcookie('birth_error', 'Укажите дату рождения');
  $errors['birth'] = true;
} else {
  $year = (int)date('Y', strtotime($birth));
  if ($year < 1900 || $year > 2026) {
    setcookie('birth_error', 'Год 1900–2026');
    $errors['birth'] = true;
  }
}

$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male','female'])) {
  setcookie('gender_error', 'Выберите пол');
  $errors['gender'] = true;
}

$languages = $_POST['languages'] ?? [];
if (empty($languages)) {
  setcookie('languages_error', 'Выберите язык');
  $errors['languages'] = true;
}

$bio = $_POST['biography'] ?? '';
if ($bio === '') {
  setcookie('bio_error', 'Введите биографию');
  $errors['bio'] = true;
}

$contract = !empty($_POST['contract']);
if (!$contract) {
  setcookie('contract_error', 'Подтвердите согласие');
  $errors['contract'] = true;
}

foreach ($_POST as $k => $v) {
  if (is_array($v)) $v = implode(',', $v);
  setcookie($k.'_value', $v, time() + 365*24*60*60);
}

if ($errors) {
  header("Location: index.php");
  exit();
}

if (isset($_SESSION['user_id'])) {

  $id = $_SESSION['user_id'];

  $stmt = $pdo->prepare("UPDATE applications SET fio=?,phone=?,email=?,birth_date=?,gender=?,biography=? WHERE id=?");
  $stmt->execute([$fio,$phone,$email,$birth,$gender,$bio,$id]);

  $pdo->prepare("DELETE FROM application_languages WHERE application_id=?")->execute([$id]);

  $stmt = $pdo->prepare("INSERT INTO application_languages(application_id,language_id) VALUES(?,?)");
  foreach ($languages as $l) $stmt->execute([$id,$l]);

  setcookie('save_success','Данные обновлены',time()+5);

} else {

  $login = 'user'.rand(1000,9999);
  $pass = bin2hex(random_bytes(4));
  $hash = password_hash($pass, PASSWORD_DEFAULT);

  $stmt = $pdo->prepare("INSERT INTO applications(fio,phone,email,birth_date,gender,biography,login,password_hash) VALUES(?,?,?,?,?,?,?,?)");
  $stmt->execute([$fio,$phone,$email,$birth,$gender,$bio,$login,$hash]);

  $id = $pdo->lastInsertId();

  $stmt = $pdo->prepare("INSERT INTO application_languages(application_id,language_id) VALUES(?,?)");
  foreach ($languages as $l) $stmt->execute([$id,$l]);

  setcookie('login_data',"Логин: $login Пароль: $pass",time()+10);
}

header("Location: index.php");
exit();
