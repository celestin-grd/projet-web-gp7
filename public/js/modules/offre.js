document.addEventListener('DOMContentLoaded', function () {

    const addBtn = document.getElementById('add-competence');

    if (!addBtn) return;

    addBtn.addEventListener('click', function () {

        const select = document.getElementById('competence-select');
        const container = document.getElementById('competences-container');

        const id = select.value;
        const label = select.options[select.selectedIndex].text;

        if (!id) return;

        // éviter doublons
        if (container.querySelector(`input[value="${id}"]`)) {
            alert('Compétence déjà ajoutée');
            return;
        }

        // supprimer placeholder
        const placeholder = container.querySelector('.placeholder');
        if (placeholder) {
            placeholder.remove();
        }

        const div = document.createElement('div');
        div.classList.add('competence-item');

        div.innerHTML = `
            ${label}
            <button type="button" class="remove">X</button>
            <input type="hidden" name="competences[]" value="${id}">
        `;

        // suppression
        div.querySelector('.remove').addEventListener('click', function () {
            div.remove();

            if (container.children.length === 0) {
                container.innerHTML = '<p class="placeholder">Aucune compétence sélectionnée</p>';
            }
        });

        container.appendChild(div);

        // reset select
        select.value = '';
    });

    // gérer les boutons remove déjà présents (mode modification)
    document.querySelectorAll('.competence-item .remove').forEach(btn => {
        btn.addEventListener('click', function () {
            const div = this.parentElement;
            const container = document.getElementById('competences-container');

            div.remove();

            if (container.children.length === 0) {
                container.innerHTML = '<p class="placeholder">Aucune compétence sélectionnée</p>';
            }
        });
    });

});

