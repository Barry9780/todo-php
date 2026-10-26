<?php
declare(strict_types=1);
require_once __DIR__ . '/config/bootstrap.php';
requireLogin();

$pageTitle = 'To-Do List';
require __DIR__ . '/partials/header.php';
?>
<div class="main-page" data-current-user="<?= e((string) getCurrentUser()) ?>">
    <div class="todo-card">
        <div class="todo-header">
            <div>
                <p class="small-label">Utilisateur connecté</p>
                <h1>To-Do List</h1>
                <p class="subtitle">Gérez uniquement vos propres tâches.</p>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>

        <div id="todoMessage"></div>

        <div class="table-wrapper">
            <table class="todo-table">
                <thead>
                <tr>
                    <th>ID</th>
                    <th>Activity</th>
                    <th>Deadline</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="todoTableBody">
                <tr class="template-row">
                    <td>(Auto)</td>
                    <td>
                        <input type="text" id="newActivity" maxlength="150" placeholder="New activity">
                    </td>
                    <td>
                        <input type="date" id="newDeadline">
                    </td>
                    <td>
                        <button type="button" class="add-btn" id="addTodoButton">Add</button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require __DIR__ . '/partials/footer.php'; ?>
