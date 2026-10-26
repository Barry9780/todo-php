<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$pageTitle = 'Changer le mot de passe';
$messages = [];
$success = false;
$userId = '';

if (isPost()) {
    $userId = sanitizeUserId($_POST['user'] ?? '');
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($userId === '' || $currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
        $messages[] = 'Tous les champs sont obligatoires.';
    }

    if ($newPassword !== $confirmPassword) {
        $messages[] = 'La confirmation du nouveau mot de passe doit être identique.';
    }

    if (!$messages) {
        try {
            $pdo = getPDO();
            $messages = updatePassword($pdo, $userId, $currentPassword, $newPassword);
            if (!$messages) {
                $success = true;
                $messages[] = 'Mot de passe modifié avec succès.';
                $userId = '';
            }
        } catch (Throwable $e) {
            $messages[] = 'Erreur lors du changement de mot de passe.';
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<div class="auth-page">
    <div class="auth-card wide-card">
        <h1>Changer le mot de passe</h1>
        <p class="subtitle">Le nouveau mot de passe doit contenir au moins 3 caractères.</p>

        <?php foreach ($messages as $message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>"><?= e($message) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-card" novalidate>
            <label for="user">UserId</label>
            <input type="text" id="user" name="user" maxlength="30" value="<?= e($userId) ?>" required>

            <label for="current_password">Mot de passe courant</label>
            <input type="password" id="current_password" name="current_password" maxlength="255" required>

            <label for="new_password">Nouveau mot de passe</label>
            <input type="password" id="new_password" name="new_password" maxlength="255" required>

            <label for="confirm_password">Confirmation du nouveau mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" maxlength="255" required>

            <button type="submit" class="primary-btn full-width">Changer le mot de passe</button>
        </form>

        <div class="auth-links single-link">
            <a href="index.php">Retour à la connexion</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
