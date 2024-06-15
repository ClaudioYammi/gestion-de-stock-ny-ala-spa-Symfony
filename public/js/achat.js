
    document.getElementById("add").addEventListener('click', addRow);

    function deleteRow(button) {
        var row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }

    function addRow(event) {

        try {
                  event.preventDefault();
        } catch (error) {
            
        }
        
        var table = document.getElementById("tbody");
        var newRow = document.createElement("tr");
        var produitCell = document.createElement("td");
        var quantiteCell = document.createElement("td");
        var prixUnitaireCell = document.createElement("td");
        var actionsCell = document.createElement("td");

        var produitSelect = document.createElement("select");
        produitSelect.name = "produit[]";
        produitSelect.classList.add("produit-select", "form-select");
        produitSelect.innerHTML = '<option value="">Sélectionnez un produit</option>';

       
        // Appel initial de la fonction updateTotal pour mettre à jour le prix total
        updateTotal();

        // Effectuer une requête AJAX avec fetch pour récupérer les données des produits
        fetch("/produit/api")
            .then(function(response) {
                if (response.ok) {
                    return response.json();
                } else {
                    throw new Error("Une erreur s'est produite lors de la récupération des produits.");
                }
            })
            .then(function(products) {
                products.forEach(function(product) {
                    var option = document.createElement("option");
                    option.value = product.id;
                    option.textContent = product.designation;
                    option.dataset.price = product.price; // Ajout de l'attribut data-price pour stocker le prix unitaire
                    produitSelect.appendChild(option);
                });
            })

            .catch(function(error) {
                console.error(error);
            });

        produitSelect.addEventListener('change', function() {
            var selectedOption = this.options[this.selectedIndex];
            var priceInput = prixUnitaireCell.querySelector('.prixunitaire-input');
            var price = selectedOption.dataset.price;

            priceInput.value = price;
        });

        produitCell.appendChild(produitSelect);
        quantiteCell.innerHTML = '<input type="text" name="quantite[]" class="quantite-input form-control">';
        prixUnitaireCell.innerHTML = '<input type="text"  name="prixunitaire[]" class="prixunitaire-input form-control">';
        actionsCell.innerHTML = '<button class="delete btn btn-danger btn-sm" onclick="deleteRow(this)"><i class="fas fa-trash"></i></button>';

        newRow.appendChild(produitCell);
        newRow.appendChild(quantiteCell);
        newRow.appendChild(prixUnitaireCell);
        newRow.appendChild(actionsCell);

        table.appendChild(newRow);

        // Attache un écouteur d'événement à chaque champ d'entrée de quantité
        var quantiteInputs = document.getElementsByClassName("quantite-input");
        for (var i = 0; i < quantiteInputs.length; i++) {
            quantiteInputs[i].addEventListener('input', updateTotal);
        }
    }
    for (let index = 0; index < 3; index++) {
        addRow();
    }
    const renameInput=(event)=>{
        event.preventDefault()
    // Récupérer tous les éléments <tr> dans le <tbody>
    const rows = document.querySelectorAll("tbody tr");

    // Parcourir chaque ligne du tableau
    rows.forEach((row, index) => {
    // Récupérer les éléments <input> dans la ligne actuelle
    const inputProduit = row.querySelector("select[name='produit[]']");
    const inputPrix = row.querySelector("input[name='prixunitaire[]']");
    const inputQuantite = row.querySelector("input[name='quantite[]']");
    console.log(inputProduit)
        try {
            if(inputProduit.value.trim()){
            // Mettre à jour les valeurs des inputs
            inputProduit.setAttribute('name', "produit-" + index + "");
            inputPrix.setAttribute('name',"prixunitaire-" + index + "");
            inputQuantite.setAttribute('name',"quantite-" + index + "");
        }
    } catch (error) {
        
        }  
    });
    document.getElementsByName('achat')[0].submit()
}

    function updateTotal() {
        var rows = document.querySelectorAll("tbody tr");
        var total = 0;

        rows.forEach(function(row) {
            try {
                var quantityInput = row.querySelector(".quantite-input");
                var unitPriceInput = row.querySelector(".prixunitaire-input");
                var quantity = parseFloat(quantityInput.value) || 0;
                var unitPrice = parseFloat(unitPriceInput.value) || 0;
                total += quantity * unitPrice;
            } catch(error){

            }
        });

        document.getElementById("total-value").textContent = total.toFixed(2);
        }

        const form=document.getElementsByName('achat')[0]
        form.addEventListener('submit',renameInput)
    
