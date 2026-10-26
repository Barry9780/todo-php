<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';

$pageTitle = 'Créer un login';
$messages = [];
$success = false;
$userId = '';

if (isPost()) {
    $userId = sanitizeUserId($_POST['user'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if ($userId === '' || $password === '' || $confirmPassword === '') {
        $messages[] = 'Tous les champs sont obligatoires.';
    }

    if ($password !== $confirmPassword) {
        $messages[] = 'Les deux mots de passe doivent être identiques.';
    }

    if (!$messages) {
        try {
            $pdo = getPDO();
            $messages = createUser($pdo, $userId, $password);
            if (!$messages) {
                $success = true;
                $messages[] = 'Compte créé avec succès. Vous pouvez maintenant vous connecter.';
                $userId = '';
            }
        } catch (Throwable $e) {
            $messages[] = 'Erreur lors de la création du compte.';
        }
    }
}

require __DIR__ . '/partials/header.php';
?>
<div class="auth-page">
    <div class="auth-card wide-card">
        <h1>Créer un login</h1>
        <p class="subtitle">Le userId doit avoir au moins 3 caractères, sans espace.</p>

        <?php foreach ($messages as $message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>"><?= e($message) ?></div>
        <?php endforeach; ?>

        <form method="post" class="form-card" novalidate>
            <label for="user">UserId</label>
            <input type="text" id="user" name="user" maxlength="30" value="<?= e($userId) ?>" required>

            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" maxlength="255" required>

            <label for="confirm_password">Confirmation du mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" maxlength="255" required>

            <button type="submit" class="primary-btn full-width">Créer le compte</button>
        </form>

        <div class="auth-links single-link">
            <a href="index.php">Retour à la connexion</a>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
