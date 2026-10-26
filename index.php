<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

if (getCurrentUser()) {
    redirect('main.php');
}

$pageTitle = 'Connexion';
$error = '';
$userId = '';

if (isPost()) {
    $userId = sanitizeUserId($_POST['user'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($userId === '' || $password === '') {
        $error = 'Veuillez remplir le userId et le mot de passe.';
    } else {
        try {
            $pdo = getPDO();
            if (verifyLoginAndUpgrade($pdo, $userId, $password)) {
                session_regenerate_id(true);
                $_SESSION['user'] = $userId;
                redirect('main.php');
            }
            $error = 'UserId ou mot de passe incorrect.';
        } catch (Throwable $e) {
            $error = 'Erreur de connexion à la base de données.';
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<div class="auth-page">
    <div class="auth-card">
        <h1>Connexion</h1>
        <p class="subtitle">Connectez-vous pour gérer votre liste de tâches.</p>

        <?php if ($error !== ''): ?>
            <div class="message error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="form-card" novalidate>
            <label for="user">UserId</label>
            <input type="text" id="user" name="user" maxlength="30" value="<?= e($userId) ?>" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" maxlength="255" required>

            <button type="submit" class="primary-btn full-width">Se connecter</button>
        </form>

        <div class="auth-links">
            <a href="register.php">Créer un login</a>
            <a href="change_password.php">Changer le mot de passe</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
