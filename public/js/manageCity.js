document.addEventListener('DOMContentLoaded', function() {
    const table = new DataTable('#city-datatable', {
        language: {
            processing: "Traitement...",
            search: "Rechercher :",
            lengthMenu: "Afficher _MENU_ entrées",
            info: "Affichage de l'entrée _START_ à _END_ sur _TOTAL_ entrées",
            infoEmpty: "Aucune entrée à afficher",
            infoFiltered: "(filtré de _MAX_ entrées au total)",
            loadingRecords: "Chargement...",
            zeroRecords: "Aucun enregistrement correspondant trouvé",
            paginate: {
                first: "Premier",
                last: "Dernier",
                next: "Suivant",
                previous: "Précédent"
            },
            aria: {
                sortAscending:  ": activer pour trier la colonne par ordre croissant",
                sortDescending: ": activer pour trier la colonne par ordre décroissant"
            }
        }
    });
});

function setupModifyButtons() {
    const modifyButtons = document.querySelectorAll('.modify');

    modifyButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Récupérer la ligne parente (tr) de ce bouton
            const row = this.closest('tr');
            // Vérifier la valeur de data-save
            const isSaving = this.getAttribute('data-save') === 'yes';

            if (isSaving) {
                handleSave(row, this);
            } else {
                handleEdit(row, this);
            }
        });
    });
}

function handleEdit(row, button) {
    // Récupérer tous les inputs dans la ligne
    const inputs = row.querySelectorAll('input');

    // Activer les inputs
    inputs.forEach(input => {
        input.disabled = false; // Réactiver l'input
    });

    // Modifier le texte du bouton à "Enregistrer"
    button.textContent = 'Enregistrer';

    // Ajouter l'attribut data-save
    button.setAttribute('data-save', 'yes');

    // Désactiver le bouton "Supprimer" pendant l'édition
    const deleteButton = row.querySelector('.delete');
    if (deleteButton) {
        deleteButton.disabled = true; // Désactive le bouton supprimer
    }
}

function handleSave(row, button) {
    // Récupérer tous les inputs dans la ligne
    const inputs = row.querySelectorAll('input');

    // Exemple de récupération des valeurs des inputs pour traitement
    const id = row.getAttribute('data-city-id');
    const cityName = inputs[0].value; // Supposons que le nom de la ville est le premier input
    const cityPostCode = inputs[1].value; // Supposons que le code postal est le deuxième input

    console.log('Enregistrer les données :', id,cityName, cityPostCode);

    // Désactiver les inputs
    inputs.forEach(input => {
        input.disabled = true; // Désactiver l'input après l'enregistrement
    });

    // Modifier le texte du bouton à "Modifier"
    button.textContent = 'Modifier';

    // Changer l'attribut data-save à "no"
    button.setAttribute('data-save', 'no');

    // Réactiver le bouton "Supprimer" si nécessaire
    const deleteButton = row.querySelector('.delete');
    if (deleteButton) {
        deleteButton.disabled = false; // Réactive le bouton supprimer
    }
}

// Appeler la fonction pour configurer les boutons
document.addEventListener('DOMContentLoaded', setupModifyButtons);
