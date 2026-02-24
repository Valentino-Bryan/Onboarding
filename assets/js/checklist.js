document.addEventListener('DOMContentLoaded', () => {
  const taskSection = document.getElementById('taskSection');
  if (!taskSection) return;

  const userId = taskSection.dataset.userId;
  const checklistId = taskSection.dataset.checklistId;
  const taskList = document.getElementById('taskList');

  /* ===== USER SELECTION ===== */
  const userSearch = document.getElementById('user-search');
  const userDropdown = document.getElementById('userDropdown');
  
  if (userSearch && userDropdown) {
    // Show dropdown when input is focused
    userSearch.addEventListener('focus', () => {
      userDropdown.classList.add('show');
    });

    // Filter users when typing
    userSearch.addEventListener('input', (e) => {
      const searchTerm = e.target.value.toLowerCase();
      const items = userDropdown.querySelectorAll('.dropdown-item');
      
      items.forEach(item => {
        const text = item.textContent.toLowerCase();
        item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
      });
    });

    // Handle user selection
    userDropdown.addEventListener('click', (e) => {
      const item = e.target.closest('.dropdown-item');
      if (item) {
        const selectedUserId = item.dataset.id;
        const userName = item.textContent.trim();
        
        // Update input field
        userSearch.value = userName;
        
        // Hide dropdown
        userDropdown.classList.remove('show');
        
        // Redirect to checklist page with selected user
        window.location.href = `checklist.php?user_id=${selectedUserId}`;
      }
    });

    // Hide dropdown when clicking outside
    document.addEventListener('click', (e) => {
      if (!userSearch.contains(e.target) && !userDropdown.contains(e.target)) {
        userDropdown.classList.remove('show');
      }
    });
  }

  /* ===== ADD TASK ===== */
  const addBtn = document.getElementById('addTaskBtn');
  if (addBtn) {
    addBtn.addEventListener('click', () => {
      if (document.querySelector('.task-item.new')) return;

      const row = document.createElement('div');
      row.className = 'task-item new';
      row.innerHTML = `
        <input type="text" class="task-input" placeholder="Nieuwe taak..."/>
        <button class="save">Opslaan</button>
        <button class="cancel">Annuleren</button>
      `;
      taskList.appendChild(row);

      const input = row.querySelector('.task-input');
      input.focus();

      row.querySelector('.save').onclick = async () => {
        const title = input.value.trim();
        if (!title) return alert('Vul een titel in.');

        try {
          const res = await fetch('../api/task_create.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId, checklist_id: checklistId, title })
          });
          const data = await res.json();
          if (!data.success) return alert(data.error || 'Fout bij opslaan');

          row.classList.remove('new');
          row.dataset.id = data.id;
          row.innerHTML = `
            <span class="material-symbols-outlined drag-icon">drag_indicator</span>
            <input type="checkbox" class="task-complete">
            <p class="task-title">${escapeHtml(title)}</p>
            <button class="edit" title="Bewerken">
              <span class="material-symbols-outlined">edit</span>
            </button>
            <button class="delete" title="Verwijderen">
              <span class="material-symbols-outlined">delete</span>
            </button>
          `;
        } catch (err) {
          console.error(err);
          alert('Netwerkfout bij opslaan');
        }
      };

      row.querySelector('.cancel').onclick = () => row.remove();
    });
  }

  /* ===== EDIT / DELETE ===== */
  taskSection.addEventListener('click', async e => {
    const task = e.target.closest('.task-item');
    if (!task) return;
    const taskId = task.dataset.id;

    // DELETE
    if (e.target.closest('.delete')) {
      if (!confirm('Weet je zeker dat je deze taak wilt verwijderen?')) return;
      try {
        const res = await fetch('../api/task_delete.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: taskId })
        });
        const data = await res.json();
        if (data.success) task.remove();
        else alert(data.error || 'Fout bij verwijderen');
      } catch (err) {
        console.error(err);
        alert('Netwerkfout bij verwijderen');
      }
    }

    // EDIT
    if (e.target.closest('.edit')) {
      const p = task.querySelector('p.task-title');
      const oldText = p.innerText;
      if (task.querySelector('.task-input')) return;

      const input = document.createElement('input');
      input.type = 'text';
      input.className = 'task-input';
      input.value = oldText;
      p.replaceWith(input);

      const saveBtn = document.createElement('button');
      saveBtn.className = 'save';
      saveBtn.innerText = 'Opslaan';
      const cancelBtn = document.createElement('button');
      cancelBtn.className = 'cancel';
      cancelBtn.innerText = 'Annuleren';
      task.appendChild(saveBtn);
      task.appendChild(cancelBtn);

      saveBtn.onclick = async () => {
        const newTitle = input.value.trim();
        if (!newTitle) return alert('Vul een titel in.');

        try {
          const res = await fetch('../api/task_update.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: taskId, title: newTitle })
          });
          const data = await res.json();
          if (data.success) {
            const pNew = document.createElement('p');
            pNew.className = 'task-title';
            pNew.innerText = newTitle;
            input.replaceWith(pNew);
            saveBtn.remove();
            cancelBtn.remove();
          } else {
            alert(data.error || 'Fout bij opslaan');
          }
        } catch (err) {
          console.error(err);
          alert('Netwerkfout bij opslaan');
        }
      };

      cancelBtn.onclick = () => {
        const pOld = document.createElement('p');
        pOld.className = 'task-title';
        pOld.innerText = oldText;
        input.replaceWith(pOld);
        saveBtn.remove();
        cancelBtn.remove();
      };
    }
  });

  /* ===== TOGGLE COMPLETED ===== */
  taskSection.addEventListener('change', async e => {
    if (!e.target.classList.contains('task-complete')) return;
    const task = e.target.closest('.task-item');
    if (!task) return;

    const taskId = task.dataset.id;
    const completed = e.target.checked ? 1 : 0;

    try {
      const res = await fetch('../api/task_toggle.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: taskId, completed })
      });
      const data = await res.json();
      if (!data.success) alert(data.error || 'Fout bij updaten');
    } catch (err) {
      console.error(err);
      alert('Netwerkfout bij updaten');
    }
  });

  /* ===== HELPERS ===== */
  function escapeHtml(text) {
    return text.replace(/[&<>"'`=\/]/g, s => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#39;',
      '/': '&#x2F;',
      '`': '&#x60;',
      '=': '&#x3D;'
    }[s]));
  }
});
