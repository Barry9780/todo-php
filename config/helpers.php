<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function isPost(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function sanitizeUserId(string $userId): string
{
    return trim($userId);
}

function passwordWithSalt(string $userId, string $password): string
{
    return $password . $userId;
}

function passwordIsValidForPolicy(string $password): bool
{
    return mb_strlen($password) >= 3;
}

function userIdIsValidForPolicy(string $userId): bool
{
    return mb_strlen($userId) >= 3 && !preg_match('/\s/', $userId);
}

function requireLogin(): void
{
    if (empty($_SESSION['user'])) {
        redirect('index.php');
    }
}

function getCurrentUser(): ?string
{
    return $_SESSION['user'] ?? null;
}

function getUserLogin(PDO $pdo, string $userId): ?array
{
    $stmt = $pdo->prepare('SELECT USER, HPW FROM LOGINS WHERE USER = :user');
    $stmt->execute(['user' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function verifyLogin(PDO $pdo, string $userId, string $password): bool
{
    $login = getUserLogin($pdo, $userId);
    if (!$login) {
        return false;
    }

    return password_verify(passwordWithSalt($userId, $password), $login['HPW']);
}

function verifyLoginAndUpgrade(PDO $pdo, string $userId, string $password): bool
{
    $login = getUserLogin($pdo, $userId);
    if (!$login) {
        return false;
    }

    $saltedPassword = passwordWithSalt($userId, $password);
    if (!password_verify($saltedPassword, $login['HPW'])) {
        return false;
    }

    if (password_needs_rehash($login['HPW'], PASSWORD_DEFAULT)) {
        $newHash = password_hash($saltedPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE LOGINS SET HPW = :hpw WHERE USER = :user');
        $update->execute([
            'hpw' => $newHash,
            'user' => $userId,
        ]);
    }

    return true;
}

function createUser(PDO $pdo, string $userId, string $password): array
{
    $errors = [];

    if (!userIdIsValidForPolicy($userId)) {
        $errors[] = 'Le userId doit contenir au moins 3 caractères et aucun espace.';
    }

    if (!passwordIsValidForPolicy($password)) {
        $errors[] = 'Le mot de passe doit contenir au moins 3 caractères.';
    }

    if ($errors) {
        return $errors;
    }

    if (getUserLogin($pdo, $userId)) {
        return ['Ce userId existe déjà.'];
    }

    $hash = password_hash(passwordWithSalt($userId, $password), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO LOGINS (USER, HPW) VALUES (:user, :hpw)');
    $stmt->execute([
        'user' => $userId,
        'hpw' => $hash,
    ]);

    return [];
}

function updatePassword(PDO $pdo, string $userId, string $currentPassword, string $newPassword): array
{
    $errors = [];

    if (!userIdIsValidForPolicy($userId)) {
        $errors[] = 'Le userId est invalide.';
    }

    if (!passwordIsValidForPolicy($newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 3 caractères.';
    }

    if ($errors) {
        return $errors;
    }

    if (!verifyLogin($pdo, $userId, $currentPassword)) {
        return ['Le userId ou le mot de passe courant est incorrect.'];
    }

    $hash = password_hash(passwordWithSalt($userId, $newPassword), PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE LOGINS SET HPW = :hpw WHERE USER = :user');
    $stmt->execute([
        'hpw' => $hash,
        'user' => $userId,
    ]);

    return [];
}

function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}
