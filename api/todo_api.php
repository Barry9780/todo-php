<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/bootstrap.php';
requireLogin();

try {
    $pdo = getPDO();
    $user = (string) getCurrentUser();
    $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

    switch ($action) {
        case 'list':
            $stmt = $pdo->prepare('SELECT ID, ACTIVITE, ECHEANCE FROM TODOS WHERE USER = :user ORDER BY ID ASC');
            $stmt->execute(['user' => $user]);
            jsonResponse(['success' => true, 'todos' => $stmt->fetchAll()]);

        case 'add':
            $activity = trim((string) ($_POST['activity'] ?? ''));
            $deadline = trim((string) ($_POST['deadline'] ?? ''));

            if ($activity === '') {
                jsonResponse(['success' => false, 'message' => 'L’activité est obligatoire.'], 422);
            }

            if (mb_strlen($activity) > 150) {
                jsonResponse(['success' => false, 'message' => 'L’activité ne peut pas dépasser 150 caractères.'], 422);
            }

            $deadlineValue = $deadline !== '' ? $deadline : null;
            $stmt = $pdo->prepare('INSERT INTO TODOS (USER, ACTIVITE, ECHEANCE) VALUES (:user, :activity, :deadline)');
            $stmt->execute([
                'user' => $user,
                'activity' => $activity,
                'deadline' => $deadlineValue,
            ]);

            jsonResponse(['success' => true, 'message' => 'Tâche ajoutée avec succès.']);

        case 'update':
            $id = (int) ($_POST['id'] ?? 0);
            $activity = trim((string) ($_POST['activity'] ?? ''));
            $deadline = trim((string) ($_POST['deadline'] ?? ''));

            if ($id <= 0) {
                jsonResponse(['success' => false, 'message' => 'ID invalide.'], 422);
            }
            if ($activity === '') {
                jsonResponse(['success' => false, 'message' => 'L’activité est obligatoire.'], 422);
            }
            if (mb_strlen($activity) > 150) {
                jsonResponse(['success' => false, 'message' => 'L’activité ne peut pas dépasser 150 caractères.'], 422);
            }

            $deadlineValue = $deadline !== '' ? $deadline : null;
            $stmt = $pdo->prepare('UPDATE TODOS SET ACTIVITE = :activity, ECHEANCE = :deadline WHERE ID = :id AND USER = :user');
            $stmt->execute([
                'activity' => $activity,
                'deadline' => $deadlineValue,
                'id' => $id,
                'user' => $user,
            ]);

            if ($stmt->rowCount() === 0) {
                jsonResponse(['success' => false, 'message' => 'Tâche introuvable ou non autorisée.'], 404);
            }

            jsonResponse(['success' => true, 'message' => 'Tâche modifiée avec succès.']);

        case 'delete':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id <= 0) {
                jsonResponse(['success' => false, 'message' => 'ID invalide.'], 422);
            }

            $stmt = $pdo->prepare('DELETE FROM TODOS WHERE ID = :id AND USER = :user');
            $stmt->execute([
                'id' => $id,
                'user' => $user,
            ]);

            if ($stmt->rowCount() === 0) {
                jsonResponse(['success' => false, 'message' => 'Tâche introuvable ou non autorisée.'], 404);
            }

            jsonResponse(['success' => true, 'message' => 'Tâche supprimée avec succès.']);

        default:
            jsonResponse(['success' => false, 'message' => 'Action non supportée.'], 400);
    }
} catch (Throwable $e) {
    jsonResponse(['success' => false, 'message' => 'Erreur serveur.'], 500);
}
