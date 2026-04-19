<?php
$values = $values ?? [];
$errors = $errors ?? [];
$messages = $messages ?? [];
?>

<html>
<head>
<meta charset="UTF-8">
<style>
body{font-family:sans-serif;background:#eef2f3;display:flex;justify-content:center;padding:20px;}
form{background:#fff;padding:25px;border-radius:10px;max-width:500px;width:100%;box-shadow:0 4px 15px rgba(0,0,0,.1);}
.field{margin-bottom:15px;}
label{font-weight:bold;display:block;margin-bottom:5px;}
input,select,textarea{width:100%;padding:10px;border:1px solid #ccc;border-radius:6px;}
.error{border:2px solid #e74c3c;}
.error-msg{margin-top:5px;font-size:12px;color:#c0392b;background:#fff5f5;border:1px solid #ffcccc;padding:6px;border-radius:6px;}
.success{margin-bottom:15px;background:#e6ffed;border:1px solid #b7f5c5;color:#1e7e34;padding:10px;border-radius:6px;}
button{width:100%;padding:12px;background:#28a745;color:white;border:none;border-radius:6px;}
.radio{display:flex;gap:10px;}
</style>
</head>

<body>

<?php if (isset($_SESSION['user_id'])): ?>
<a href="?logout=1">Выйти</a>
<?php else: ?>
<a href="login.php">Войти</a>
<?php endif; ?>

<form method="POST" novalidate>

<?php if (!empty($messages)): ?>
<div class="success"><?= $messages[0] ?></div>
<?php endif; ?>

<div class="field">
<label>ФИО</label>
<input name="fio" value="<?= htmlspecialchars($values['fio']) ?>" class="<?= isset($errors['fio'])?'error':'' ?>">
<?php if(isset($errors['fio'])): ?><div class="error-msg"><?= $_COOKIE['fio_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Телефон</label>
<input name="phone" value="<?= htmlspecialchars($values['phone']) ?>" class="<?= isset($errors['phone'])?'error':'' ?>">
<?php if(isset($errors['phone'])): ?><div class="error-msg"><?= $_COOKIE['phone_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Email</label>
<input name="email" value="<?= htmlspecialchars($values['email']) ?>" class="<?= isset($errors['email'])?'error':'' ?>">
<?php if(isset($errors['email'])): ?><div class="error-msg"><?= $_COOKIE['email_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Дата рождения</label>
<input type="date" name="birth_date" value="<?= $values['birth_date'] ?>" class="<?= isset($errors['birth'])?'error':'' ?>">
<?php if(isset($errors['birth'])): ?><div class="error-msg"><?= $_COOKIE['birth_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Пол</label>
<div class="radio">
<label><input type="radio" name="gender" value="male" <?= $values['gender']=='male'?'checked':'' ?>> М</label>
<label><input type="radio" name="gender" value="female" <?= $values['gender']=='female'?'checked':'' ?>> Ж</label>
</div>
<?php if(isset($errors['gender'])): ?><div class="error-msg"><?= $_COOKIE['gender_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Языки</label>
<select name="languages[]" multiple size="6">
<?php foreach($langs as $id=>$name): ?>
<option value="<?= $id ?>" <?= in_array($id,$values['languages'])?'selected':'' ?>><?= $name ?></option>
<?php endforeach; ?>
</select>
<?php if(isset($errors['languages'])): ?><div class="error-msg"><?= $_COOKIE['languages_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>Биография</label>
<textarea name="biography"><?= htmlspecialchars($values['biography']) ?></textarea>
<?php if(isset($errors['bio'])): ?><div class="error-msg"><?= $_COOKIE['bio_error'] ?></div><?php endif; ?>
</div>

<div class="field">
<label>
<input type="checkbox" name="contract" <?= !empty($values['contract'])?'checked':'' ?>>
Согласен
</label>
<?php if(isset($errors['contract'])): ?><div class="error-msg"><?= $_COOKIE['contract_error'] ?></div><?php endif; ?>
</div>

<button type="submit">Сохранить</button>

</form>

</body>
</html>
