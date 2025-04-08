<?php
// Отправляем браузеру правильную кодировку,
// файл index.php должен быть в кодировке UTF-8 без BOM.
header('Content-Type: text/html; charset=UTF-8');

// В суперглобальном массиве $_SERVER PHP сохраняет некторые заголовки запроса HTTP
// и другие сведения о клиненте и сервере, например метод текущего запроса $_SERVER['REQUEST_METHOD'].
if ($_SERVER['REQUEST_METHOD'] == 'GET') {
  // В суперглобальном массиве $_GET PHP хранит все параметры, переданные в текущем запросе через URL.
  if (!empty($_GET['save'])) {
    // Если есть параметр save, то выводим сообщение пользователю.
    print('Спасибо, результаты сохранены.');
  }
  // Включаем содержимое файла form.php.
  /*include('form.php');*/
  // Завершаем работу скрипта.
  exit();
}
// Иначе, если запрос был методом POST, т.е. нужно проверить данные и сохранить их в БД.

// Проверяем ошибки.
$errors = FALSE;
if (empty($_POST['name'])) {
  print('Заполните имя.<br/>');
  $errors = TRUE;
}

if (!preg_match("/[а-я]/i", $_POST['name']) && !preg_match("/[a-z]/i", $_POST['name'])) {
  print('Имя должно содержать только буквы.<br/>');
  $errors = TRUE;
}

if (empty($_POST['phone'])) {
  print('Заполните телефон.<br/>');
  $errors = TRUE;
}

if (!is_numeric($_POST['phone'])) {
  print('Телефон должен состоять только из цифр.<br/>');
  $errors = TRUE;
}

if (empty($_POST['birthdate'])) {
  print('Заполните дату рождения.<br/>');
  $errors = TRUE;
}

if (empty($_POST['gender'])) {
  print('Укажите пол.<br>');
  $errors = TRUE;
}

if (empty($_POST['languages'])) {
  print('Укажите любымие языки программирования.<br>');
  $errors = TRUE;
}

if (empty($_POST['bio'])) {
  print('Заполните биографию.<br>');
  $errors = TRUE;
}

if (empty($_POST['agreement'])) {
  print('Невозможно продолжить без вашего согласия на обработку персональных данных.<br>');
  $errors = TRUE;
}

if ($errors) {
  // При наличии ошибок завершаем работу скрипта.
  exit();
}

// Сохранение в базу данных.
$user = 'u68890'; 
$pass = '9949076'; 
$pdo = new PDO('mysql:host=localhost;dbname=u68890', $user, $pass,
 [PDO::ATTR_PERSISTENT => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (empty($errors)) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $email = $_POST['email'] ?? '';
    $birthdate = $_POST['birthdate'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $languages = $_POST['languages'] ?? []; 

    $pdo->beginTransaction();

    // Сохранение основной информации в таблицу application
    $stmt = $pdo->prepare("INSERT INTO application (name, phone, email, birthdate, gender, bio) 
              VALUES (:name, :phone, :email, :birthdate, :gender, :bio)");
    $stmt->bindParam(':name', $name);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':birthdate', $birthdate);
    $stmt->bindParam(':gender', $gender);
    $stmt->bindParam(':bio', $bio);
    
    $stmt->execute();
    $applicationId = $pdo->lastInsertId();

    // Сохранение любимых языков программирования в таблицу application_language
    $langStmt = $pdo->prepare("
                INSERT INTO programming_language (name)
                VALUES (:name)
                ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
            ");
    
    $appLangStmt = $pdo->prepare("
        INSERT INTO application_language (application_id, language_id)
        VALUES (:app_id, :lang_id)
    ");
    foreach ($languages as $langName) {
      $langStmt->execute([':name' => $langName]);
      $langId = $pdo->lastInsertId();

      $appLangStmt->execute([
          ':app_id' => $applicationId,
          ':lang_id' => $langId
      ]);
    }
    $pdo->commit();

    echo "Данные успешно сохранены!";
  }
}