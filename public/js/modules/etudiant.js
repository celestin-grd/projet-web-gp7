document.addEventListener('DOMContentLoaded', function () {

    const roleSelect = document.getElementById('role-select');
    const piloteBlock = document.getElementById('pilote-block');

    // ⚠️ injecté depuis PHP
    const piloteRoles = JSON.parse(document.getElementById('pilote-roles-data').textContent);

    function togglePiloteBlock() {
        if (!roleSelect || !piloteBlock) return;

        const selectedRole = parseInt(roleSelect.value);

        if (piloteRoles.includes(selectedRole)) {
            piloteBlock.style.display = 'block';
        } else {
            piloteBlock.style.display = 'none';
        }
    }

    // 🔥 au chargement
    togglePiloteBlock();

    // 🔁 au changement
    if (roleSelect) {
        roleSelect.addEventListener('change', togglePiloteBlock);
    }

    const addBtn = document.getElementById('add-etudiant');

    if (!addBtn) return;

    addBtn.addEventListener('click', function () {

        const select = document.getElementById('etudiant-select');
        const container = document.getElementById('etudiants-container');

        const id = select.value;
        const label = select.options[select.selectedIndex].text;

        if (!id) return;

        // éviter doublons
        if (container.querySelector(`input[value="${id}"]`)) {
            alert('Etudiant déjà ajouté');
            return;
        }

        // supprimer placeholder
        const placeholder = container.querySelector('.placeholder');
        if (placeholder) {
            placeholder.remove();
        }

        const div = document.createElement('div');
        div.classList.add('etudiant-item');

        div.innerHTML = `
            ${label}
            <button type="button" class="remove">X</button>
            <input type="hidden" name="etudiants[]" value="${id}">
        `;

        // suppression
        div.querySelector('.remove').addEventListener('click', function () {
            div.remove();

            if (container.children.length === 0) {
                container.innerHTML = '<p class="placeholder">Aucun étudiant sélectionné</p>';
            }
        });

        container.appendChild(div);

        // reset select
        select.value = '';
    });

    // gérer les boutons remove déjà présents (mode modification)
    document.querySelectorAll('.etudiant-item .remove').forEach(btn => {
        btn.addEventListener('click', function () {
            const div = this.parentElement;
            const container = document.getElementById('etudiants-container');

            div.remove();

            if (container.children.length === 0) {
                container.innerHTML = '<p class="placeholder">Aucun étudiant sélectionné</p>';
            }
        });
    });

});

