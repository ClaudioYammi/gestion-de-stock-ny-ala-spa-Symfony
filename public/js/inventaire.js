document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('inventaire-form');
    const nouvelleLigneBtn = document.getElementById('nouvelle-ligne-btn');
    const supprimerLigneBtn = document.getElementById('supprimer-ligne-btn');
    const formRows = document.getElementById('form-rows');

    nouvelleLigneBtn.addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.classList.add('row', 'g-3', 'mt-3');
        newRow.innerHTML = `
            <div class="col-md-2">
                <label for="reference">Référence</label>
                <select class="form-select" name="reference[]" required>
                    <option value="">Sélectionnez un produit</option>
                    {% for produit in produits %}
                        <option value="{{ produit.id }}">{{ produit.designation }}</option>
                    {% endfor %}
                </select>
            </div>
            <div class="col-md-2">
                <label for="update_at">Date de mise à jour</label>
                <input type="datetime-local" class="form-control" value="{{ updateAt }}" name="update_at[]" required>
            </div>
            <div class="col-md-2">
                <label for="note">Note</label>
                <input type="text" class="form-control" name="note[]" required>
            </div>
            <div class="col-md-2">
                <label for="stockinventaire">Stock inventaire</label>
                <input type="number" class="form-control" name="stockinventaire[]" required>
            </div>
            <div class="col-md-2">
                <label for="stockutiliser">Stock utilisé</label>
                <input type="number" class="form-control" name="stockutiliser[]" required>
            </div>
        `;
        formRows.appendChild(newRow);
    });

    supprimerLigneBtn.addEventListener('click', function() {
        const rows = formRows.querySelectorAll('.row.g-3');
        if (rows.length > 1) {
            rows[rows.length - 1].remove();
        } else {
            alert('Il doit y avoir au moins une ligne.');
        }
    });
});