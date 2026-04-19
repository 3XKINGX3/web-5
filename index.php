<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$db_user = 'u82373';
$db_pass = '4362231';
$pdo = new PDO('mysql:host=localhost;dbname=u82373;charset=utf8', $db_user, $db_pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);

$langs = [
    1=>'Pascal', 2=>'C', 3=>'C++', 4=>'JavaScript',
    5=>'PHP', 6=>'Python', 7=>'Java', 8=>'Haskell',
    9=>'Clojure', 10=>'Prolog', 11=>'Scala', 12=>'Go'
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

    $errors = [];
    foreach (['fio','phone','email','birth','gender','languages','bio','contract'] as $f) {
        if (!empty($_COOKIE[$f.'_error'])) {
            $errors[$f] = $_COOKIE[$f.'_error'];
            setcookie($f.'_error', '', 100000);
        }
    }

    $values = [];
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM applications WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        
        $values['fio'] = $_COOKIE['fio_value'] ?? $row['fio'];
        $values['phone'] = $_COOKIE['phone_value'] ?? $row['phone'];
        $values['email'] = $_COOKIE['email_value'] ?? $row['email'];
        $values['birth_date'] = $_COOKIE['birth_value'] ?? $row['birth_date'];
        $values['gender'] = $_COOKIE['gender_value'] ?? $row['gender'];
        $values['biography'] = $_COOKIE['bio_value'] ?? $row['biography'];
        $values['contract'] = 1;
        
        if (isset($_COOKIE['languages_value'])) {
            $values['languages'] = explode(',', $_COOKIE['languages_value']);
        } else {
            $stmt_l = $pdo->prepare("SELECT language_id FROM application_languages WHERE application_id=?");
            $stmt_l->execute([$row['id']]);
            $values['languages'] = $stmt_l->fetchAll(PDO::FETCH_COLUMN);
        }
    } else {
        $values['fio'] = $_COOKIE['fio_value'] ?? '';
        $values['phone'] = $_COOKIE['phone_value'] ?? '';
        $values['email'] = $_COOKIE['email_value'] ?? '';
        $values['birth_date'] = $_COOKIE['birth_value'] ?? '';
        $values['gender'] = $_COOKIE['gender_value'] ?? '';
        $values['biography'] = $_COOKIE['bio_value'] ?? '';
        $values['contract'] = $_COOKIE['contract_value'] ?? '';
        $values['languages'] = isset($_COOKIE['languages_value']) ? explode(',', $_COOKIE['languages_value']) : [];
    }

    include 'form.php';
    exit();
}

$errors = false;
$fio = $_POST['fio'] ?? '';
if (!preg_match('/^[a-zA-Zа-яА-ЯёЁ\s\-]+$/u', $fio)) {
    setcookie('fio_error', 'Используйте только буквы', time() + 24*3600);
    $errors = true;
}

$phone = $_POST['phone'] ?? '';
if (!preg_match('/^[0-9+\-\s()]+$/', $phone)) {
    setcookie('phone_error', 'Неверный формат телефона', time() + 24*3600);
    $errors = true;
}

$email = $_POST['email'] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    setcookie('email_error', 'Некорректный Email', time() + 24*3600);
    $errors = true;
}

$birth = $_POST['birth_date'] ?? '';
if (empty($birth)) {
    setcookie('birth_error', 'Укажите дату рождения', time() + 24*3600);
    $errors = true;
}

$gender = $_POST['gender'] ?? '';
if (!in_array($gender, ['male', 'female'])) {
    setcookie('gender_error', 'Выберите пол', time() + 24*3600);
    $errors = true;
}

$languages = $_POST['languages'] ?? [];
if (empty($languages)) {
    setcookie('languages_error', 'Выберите хотя бы один язык', time() + 24*3600);
    $errors = true;
}

$bio = $_POST['biography'] ?? '';
if (empty($bio)) {
    setcookie('bio_error', 'Заполните биографию', time() + 24*3600);
    $errors = true;
}

$contract = isset($_POST['contract']);
if (!$contract) {
    setcookie('contract_error', 'Нужно согласиться с условиями', time() + 24*3600);
    $errors = true;
}

setcookie('fio_value', $fio, time() + 365*24*3600);
setcookie('phone_value', $phone, time() + 365*24*3600);
setcookie('email_value', $email, time() + 365*24*3600);
setcookie('birth_value', $birth, time() + 365*24*3600);
setcookie('gender_value', $gender, time() + 365*24*3600);
setcookie('languages_value', implode(',', $languages), time() + 365*24*3600);
setcookie('bio_value', $bio, time() + 365*24*3600);
setcookie('contract_value', $contract ? 1 : 0, time() + 365*24*3600);

if ($errors) {
    header("Location: index.php");
    exit();
}

foreach(['fio','phone','email','birth','gender','languages','bio','contract'] as $f) {
    setcookie($f.'_value', '', 100000);
}

if (isset($_SESSION['user_id'])) {
    $id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("UPDATE applications SET fio=?, phone=?, email=?, birth_date=?, gender=?, biography=? WHERE id=?");
    $stmt->execute([$fio, $phone, $email, $birth, $gender, $bio, $id]);

    $pdo->prepare("DELETE FROM application_languages WHERE application_id=?")->execute([$id]);
    $stmt_l = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $l_id) {
        $stmt_l->execute([$id, $l_id]);
    }
    setcookie('save_success', 'Данные успешно обновлены!', time() + 24*3600);
} else {
    $login = 'user' . rand(1000, 9999);
    $pass = bin2hex(random_bytes(4));
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO applications (fio, phone, email, birth_date, gender, biography, login, password_hash) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$fio, $phone, $email, $birth, $gender, $bio, $login, $hash]);
    $id = $pdo->lastInsertId();

    $stmt_l = $pdo->prepare("INSERT INTO application_languages (application_id, language_id) VALUES (?, ?)");
    foreach ($languages as $l_id) {
        $stmt_l->execute([$id, $l_id]);
    }

    setcookie('save_success', 'Данные сохранены!', time() + 24*3600);
    setcookie('login_data', "Ваш логин: <b>$login</b>, пароль: <b>$pass</b>", time() + 24*3600);
}

header("Location: index.php");
exit();
