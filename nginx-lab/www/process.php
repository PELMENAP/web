<?php
session_start();

require_once 'db.php';
require_once 'Student.php';
require_once 'ApiClient.php';
require_once 'UserInfo.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$age = intval($_POST['age'] ?? 0);
$faculty = $_POST['faculty'] ?? '';
$studyForm = $_POST['studyForm'] ?? '';
$agree = isset($_POST['agree']) ? 1 : 0;

$errors = [];

if (empty($name)) {
    $errors[] = "Имя обязательно";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Некорректный email";
}

if ($age < 16 || $age > 100) {
    $errors[] = "Возраст должен быть от 16 до 100";
}

if (empty($faculty)) {
    $errors[] = "Выберите факультет";
}

if (empty($studyForm)) {
    $errors[] = "Выберите форму обучения";
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

try {
    $student = new Student($pdo);
    $student->add($name, $email, $age, $faculty, $agree, $studyForm);
    
    $_SESSION['name'] = $name;
    $_SESSION['email'] = $email;
    $_SESSION['age'] = $age;
    $_SESSION['faculty'] = $faculty;
    $_SESSION['studyForm'] = $studyForm;
    $_SESSION['agree'] = $agree ? 'yes' : 'no';
    
    setcookie('last_name', $name, time() + 3600, '/');
    setcookie('last_email', $email, time() + 3600, '/');
    
    UserInfo::saveLastVisit();
    
    $api = new ApiClient(300);
    $apiData = $api->request('https://api.hh.ru/areas', false);
    $_SESSION['api_data'] = $apiData;
    
    $userInfo = UserInfo::getInfo();
    $_SESSION['user_info'] = $userInfo;
    
    $_SESSION['success'] = "✅ Студент успешно зарегистрирован!";
    
} catch (Exception $e) {
    $_SESSION['errors'] = ["Ошибка сохранения: " . $e->getMessage()];
}

header("Location: index.php");
exit();
?>