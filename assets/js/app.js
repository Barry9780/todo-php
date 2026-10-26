(function () {
    const page = document.querySelector('.main-page');
    if (!page) {
        return;
    }

    const tableBody = document.getElementById('todoTableBody');
    const addButton = document.getElementById('addTodoButton');
    const newActivity = document.getElementById('newActivity');
    const newDeadline = document.getElementById('newDeadline');
    const messageBox = document.getElementById('todoMessage');

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function showMessage(text, type) {
        messageBox.innerHTML = '<div class="message ' + type + '">' + escapeHtml(text) + '</div>';
    }

    function clearMessage() {
        messageBox.innerHTML = '';
    }

    async function callApi(formData, method = 'POST') {
        const options = { method };
        if (method === 'POST') {
            options.body = formData;
        }
        const response = await fetch('api/todo_api.php', options);
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Une erreur est survenue.');
        }
        return data;
    }

    function buildTodoRow(todo) {
        const tr = document.createElement('tr');
        tr.dataset.id = todo.ID;
        tr.innerHTML = `
            <td>${escapeHtml(String(todo.ID))}</td>
            <td><input type="text" maxlength="150" class="edit-activity" value="${escapeHtml(todo.ACTIVITE || '')}"></td>
            <td><input type="date" class="edit-deadline" value="${escapeHtml(todo.ECHEANCE || '')}"></td>
            <td>
                <button type="button" class="update-btn">Update</button>
                <button type="button" class="delete-btn">Delete</button>
            </td>
        `;
        return tr;
    }

    async function loadTodos() {
        clearMessage();
        const response = await fetch('api/todo_api.php?action=list');
        const data = await response.json();
        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Impossible de charger les tâches.');
        }

        const currentRows = Array.from(tableBody.querySelectorAll('tr')).slice(1);
        currentRows.forEach((row) => row.remove());

        data.todos.forEach((todo) => {
            tableBody.appendChild(buildTodoRow(todo));
        });
    }

    async function addTodo() {
        const activity = newActivity.value.trim();
        const deadline = newDeadline.value;

        if (activity === '') {
            showMessage('Veuillez saisir une activité.', 'error');
            return;
        }
        if (activity.length > 150) {
            showMessage('L’activité ne peut pas dépasser 150 caractères.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('activity', activity);
        formData.append('deadline', deadline);

        await callApi(formData);
        newActivity.value = '';
        newDeadline.value = '';
        showMessage('Tâche ajoutée avec succès.', 'success');
        await loadTodos();
    }

    async function updateTodo(row) {
        const activity = row.querySelector('.edit-activity').value.trim();
        const deadline = row.querySelector('.edit-deadline').value;
        const id = row.dataset.id;

        if (activity === '') {
            showMessage('L’activité est obligatoire pour la mise à jour.', 'error');
            return;
        }
        if (activity.length > 150) {
            showMessage('L’activité ne peut pas dépasser 150 caractères.', 'error');
            return;
        }

        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('id', id);
        formData.append('activity', activity);
        formData.append('deadline', deadline);

        await callApi(formData);
        showMessage('Tâche modifiée avec succès.', 'success');
        await loadTodos();
    }

    async function deleteTodo(row) {
        if (!window.confirm('Voulez-vous vraiment supprimer cette tâche ?')) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', row.dataset.id);

        await callApi(formData);
        showMessage('Tâche supprimée avec succès.', 'success');
        await loadTodos();
    }

    addButton.addEventListener('click', async function () {
        try {
            await addTodo();
        } catch (error) {
            showMessage(error.message, 'error');
        }
    });

    tableBody.addEventListener('click', async function (event) {
        const row = event.target.closest('tr');
        if (!row || row.classList.contains('template-row')) {
            return;
        }

        try {
            if (event.target.classList.contains('update-btn')) {
                await updateTodo(row);
            }
            if (event.target.classList.contains('delete-btn')) {
                await deleteTodo(row);
            }
        } catch (error) {
            showMessage(error.message, 'error');
        }
    });

    loadTodos().catch(function (error) {
        showMessage(error.message, 'error');
    });
})();
