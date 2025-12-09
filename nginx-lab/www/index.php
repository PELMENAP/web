<?php

require 'vendor/autoload.php';

use App\RedisExample;

$redis = new RedisExample();

$action = $_GET['action'] ?? 'list';
$userId = $_GET['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $age = $_POST['age'] ?? 0;
        $role = $_POST['role'] ?? 'user';
        
        if ($name && $email) {
            $userData = [
                'name' => $name,
                'email' => $email,
                'age' => (int)$age,
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $newUserId = time() . rand(1000, 9999);
            $redis->addUser($newUserId, $userData);
            
            if ($role === 'admin') {
                $redis->addUserToSet('admins', $newUserId);
            }
            
            header('Location: index.php?success=added');
            exit;
        }
    }
    
    if ($action === 'delete' && $userId) {
        $redis->deleteUser($userId);
        header('Location: index.php?success=deleted');
        exit;
    }
    
    if ($action === 'login' && $userId) {
        $logins = $redis->incrementUserLogins($userId);
        header("Location: index.php?success=login&logins=$logins");
        exit;
    }
}

$userIds = $redis->getAllUserIds();
$users = [];
foreach ($userIds as $key) {
    $id = str_replace('user:', '', $key);
    $userData = $redis->getUser($id);
    if ($userData) {
        $userData['id'] = $id;
        $userData['logins'] = $redis->getUserLogins($id);
        $users[] = $userData;
    }
}

$adminIds = $redis->getUsersFromSet('admins');
$totalUsers = $redis->getUserCount();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redis Users Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 32px;
        }
        .subtitle {
            color: #666;
            font-size: 16px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-value {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
        }
        input, select {
            padding: 10px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 15px;
            transition: border 0.3s;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        button {
            padding: 12px 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
        }
        .users-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .user-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .user-card:hover {
            transform: translateY(-5px);
        }
        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
        }
        .user-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }
        .user-role {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-admin {
            background: #ff6b6b;
            color: white;
        }
        .role-user {
            background: #51cf66;
            color: white;
        }
        .user-info {
            margin-bottom: 15px;
        }
        .user-info div {
            margin-bottom: 8px;
            color: #555;
        }
        .user-info strong {
            color: #333;
        }
        .user-actions {
            display: flex;
            gap: 10px;
        }
        .btn-small {
            padding: 8px 16px;
            font-size: 14px;
            flex: 1;
        }
        .btn-danger {
            background: #ff6b6b;
        }
        .btn-success {
            background: #51cf66;
        }
        .success-message {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: white;
            font-size: 18px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔴 Redis Users Management</h1>
            <p class="subtitle">In-Memory Database с Webdis HTTP API</p>
            
            <div class="stats">
                <div class="stat-card">
                    <div class="stat-value"><?= $totalUsers ?></div>
                    <div class="stat-label">Всего пользователей</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count($adminIds) ?></div>
                    <div class="stat-label">Администраторов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $totalUsers - count($adminIds) ?></div>
                    <div class="stat-label">Обычных пользователей</div>
                </div>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="success-message">
                <?php
                switch ($_GET['success']) {
                    case 'added':
                        echo '✅ Пользователь успешно добавлен в Redis!';
                        break;
                    case 'deleted':
                        echo '✅ Пользователь удалён из Redis!';
                        break;
                    case 'login':
                        echo '✅ Логин зафиксирован! Всего логинов: ' . ($_GET['logins'] ?? 0);
                        break;
                }
                ?>
            </div>
        <?php endif; ?>

        <div class="form-card">
            <h2>➕ Добавить пользователя</h2>
            <form method="POST" action="?action=add">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Имя</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Возраст</label>
                        <input type="number" name="age" min="1" max="120" required>
                    </div>
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role">
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <button type="submit">Добавить в Redis</button>
            </form>
        </div>

        <?php if (count($users) > 0): ?>
            <div class="users-grid">
                <?php foreach ($users as $user): ?>
                    <div class="user-card">
                        <div class="user-header">
                            <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                            <div class="user-role <?= $user['role'] === 'admin' ? 'role-admin' : 'role-user' ?>">
                                <?= strtoupper($user['role']) ?>
                            </div>
                        </div>
                        <div class="user-info">
                            <div><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></div>
                            <div><strong>Возраст:</strong> <?= $user['age'] ?> лет</div>
                            <div><strong>Создан:</strong> <?= $user['created_at'] ?></div>
                            <div><strong>Логинов:</strong> <?= $user['logins'] ?></div>
                            <div><strong>ID:</strong> <code><?= $user['id'] ?></code></div>
                        </div>
                        <div class="user-actions">
                            <form method="POST" action="?action=login&user_id=<?= $user['id'] ?>" style="flex: 1;">
                                <button type="submit" class="btn-small btn-success">🔐 Login</button>
                            </form>
                            <form method="POST" action="?action=delete&user_id=<?= $user['id'] ?>" style="flex: 1;">
                                <button type="submit" class="btn-small btn-danger">🗑️ Delete</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                📭 Пользователей пока нет. Добавьте первого!
            </div>
        <?php endif; ?>
    </div>
</body>
</html>