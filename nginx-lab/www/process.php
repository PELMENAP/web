<?php
session_start();

require_once 'ApiClient.php';
require_once 'UserInfo.php';

$errors = [];

$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$age = isset($_POST['age']) ? intval($_POST['age']) : 0;
$faculty = isset($_POST['faculty']) ? $_POST['faculty'] : '';
$studyForm = isset($_POST['studyForm']) ? $_POST['studyForm'] : '';
$agree = isset($_POST['agree']) ? 'yes' : 'no';

if (empty($name)) 
{
    $errors[] = "Имя не может быть пустым";
}

if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) 
{
    $errors[] = "Некорректный email";
}

if ($age < 16 || $age > 100) 
{
    $errors[] = "Возраст должен быть от 16 до 100 лет";
}

if (empty($faculty)) 
{
    $errors[] = "Необходимо выбрать факультет";
}

if (empty($studyForm)) 
{
    $errors[] = "Необходимо выбрать форму обучения";
}

if (!empty($errors)) 
{
    $_SESSION['errors'] = $errors;
    header("Location: index.php");
    exit();
}

$safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$safeEmail = htmlspecialchars($email, ENT_QUOTES, 'UTF-8');
$safeFaculty = htmlspecialchars($faculty, ENT_QUOTES, 'UTF-8');
$safeStudyForm = htmlspecialchars($studyForm, ENT_QUOTES, 'UTF-8');

$_SESSION['name'] = $safeName;
$_SESSION['email'] = $safeEmail;
$_SESSION['age'] = $age;
$_SESSION['faculty'] = $safeFaculty;
$_SESSION['studyForm'] = $safeStudyForm;
$_SESSION['agree'] = $agree;

$line = $safeName . ";" . $safeEmail . ";" . $age . ";" . $safeFaculty . ";" . $safeStudyForm . ";" . $agree . "\n";
file_put_contents("data.txt", $line, FILE_APPEND | LOCK_EX);

setcookie('last_name', $safeName, time() + 3600, '/');
setcookie('last_email', $safeEmail, time() + 3600, '/');
setcookie('last_faculty', $safeFaculty, time() + 3600, '/');
setcookie('last_submission', date('Y-m-d H:i:s'), time() + 3600, '/');

UserInfo::saveLastVisit();

$api = new ApiClient(300);
$forceRefresh = isset($_GET['refresh']) && $_GET['refresh'] === '1';

$apiUrl = 'https://api.hh.ru/areas';
$apiData = $api->request($apiUrl, $forceRefresh);
$_SESSION['api_data'] = $apiData;

$userInfo = UserInfo::getInfo();
$_SESSION['user_info'] = $userInfo;

$_SESSION['success'] = "Регистрация прошла успешно!";

header("Location: index.php");
exit();
?>