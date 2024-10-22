
document.addEventListener('DOMContentLoaded',async function(){
    await init();
});
async function init(){
    initDatatable();
    initBtnAdd();
    setupModifyButtons();
    await handleDeleteCity();
}

function initDatatable() {
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
}

function initBtnAdd(){
    const btn = document.getElementById("add-city");
    btn.addEventListener('click',function(){
        addNewCityRow();
    })
}

function addNewCityRow() {
    const tableBody = document.querySelector('#city-datatable tbody');

    // Créer une nouvelle ligne
    const newRow = document.createElement('tr');
    newRow.classList.add('border-bottom');

    // Créer les cellules pour la ville et le code postal
    const cityCell = document.createElement('td');
    const postCodeCell = document.createElement('td');
    const actionCell = document.createElement('td');
    //ajout des classes au td action
    actionCell.classList.add('w-25');
    actionCell.classList.add('text-center');

    // Ajouter des inputs pour la ville et le code postal
    cityCell.innerHTML = `<input class="form-control bg-transparent text-white" type="text" placeholder="Nom de la ville">`;
    postCodeCell.innerHTML = `<input class="form-control bg-transparent text-white" type="text" placeholder="Code postal">`;

    // Créer les boutons Ajouter et Annuler
    const addButton = document.createElement('button');
    addButton.className = 'btn btn-success me-2 add';
    addButton.type = 'button';
    addButton.textContent = 'Ajouter';
    addButton.addEventListener('click', async () => {
        await handleAddCity(newRow); // Appeler la fonction d'ajout de ville
    });

    const cancelButton = document.createElement('button');
    cancelButton.className = 'btn btn-danger';
    cancelButton.type = 'button';
    cancelButton.textContent = 'Annuler';
    cancelButton.addEventListener('click', () => {
        newRow.remove(); // Supprimer la ligne si l'utilisateur annule
    });

    // Ajouter les boutons à la cellule des actions
    actionCell.appendChild(addButton);
    actionCell.appendChild(cancelButton);

    // Ajouter les cellules à la nouvelle ligne
    newRow.appendChild(cityCell);
    newRow.appendChild(postCodeCell);
    newRow.appendChild(actionCell);

    // Ajouter la nouvelle ligne au début du tableau
    tableBody.prepend(newRow);
}

 async function handleAddCity(newRow) {
    const inputs = newRow.querySelectorAll('input');
    const cityName = inputs[0].value;
    const postCode = inputs[1].value;
    const city = {
        'citName': cityName,
        'citPostCode': postCode
    }

    try {
        const response = await sendCityData(`/city/create`, city, 'POST'); // Adaptez l'URL selon votre route
        if (response && response.status === 'success') {
            // Swal.fire({
            //     icon: 'success',
            //     title: 'Ajouté !',
            //     text: 'Ville ajoutée avec succès.'
            // });
            location.reload();
            // Ajoutez ici le code pour mettre à jour le tableau avec les nouvelles données, si nécessaire
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message,
        });
    }

}

function setupModifyButtons() {
    const tableBody = document.querySelector('#city-datatable tbody');

    // Délégation d'événements sur le body du tableau
    tableBody.addEventListener('click', async function(event) {
        const button = event.target.closest('button'); // Utilise closest pour obtenir le bouton cliqué

        // Vérifie si le bouton cliqué a la classe 'modify'
        if (button && button.classList.contains('modify')) {
            const row = button.closest('tr');
            const isSaving = button.getAttribute('data-save') === 'yes';

            if (isSaving) {
               await handleSave(row, button);
            } else {
                handleEdit(row, button);
            }
        }
    });
}

function handleDeleteCity() {
    const tableBody = document.querySelector('#city-datatable tbody');

    // Délégation d'événements pour le bouton de suppression
    tableBody.addEventListener('click', async function(event) {
        const button = event.target.closest('button'); // Utilise closest pour obtenir le bouton cliqué

        if (button && button.classList.contains('delete')) {
            const row = button.closest('tr');
            const id = row.getAttribute('data-city-id');

            // Logique de suppression ici
            await confirmDeleteCity(id, row);
        }
    });
}

function handleEdit(row, button) {
    // Récupérer tous les inputs dans la ligne
    const inputs = row.querySelectorAll('input');
    // Activer les inputs
    inputs.forEach(input => {
        input.classList.add('border');
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

async function handleSave(row, button) {
    const inputs = row.querySelectorAll('input');
    const spans = row.querySelectorAll('label');
    const id = row.getAttribute('data-city-id');
    const cityName = inputs[0].value;
    const cityPostCode = inputs[1].value;

    const city = {
        'id': id,
        'citName': cityName,
        'citPostCode': cityPostCode,
    };

    try {
        await sendCityData(`/city/update/${city.id}`, city, 'POST');
        Swal.fire({
            icon: 'success',
            title: 'Succès',
            text: 'Mise à jour effectuée avec succès',
        });
        spans[0].textContent = cityName;
        spans[1].textContent = cityPostCode;
        // Désactiver les inputs après enregistrement
        inputs.forEach(input => {
            input.disabled = true;
            input.classList.remove('border');
        });
        button.textContent = 'Modifier';
        button.setAttribute('data-save', 'no');
        const deleteButton = row.querySelector('.delete');
        if (deleteButton) {
            deleteButton.disabled = false;
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Erreur',
            text: error.message,
        });
    }
}


async function confirmDeleteCity(id,row) {
    // Afficher une boîte de dialogue pour confirmer la suppression
    const result = await Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: 'Cette action est irréversible. Voulez-vous vraiment supprimer cette ville ?',
        icon: 'warning',
        showCancelButton: true, // Ajoute un bouton "Annuler"
        confirmButtonColor: '#d33', // Couleur du bouton de confirmation (rouge pour la suppression)
        cancelButtonColor: '#3085d6', // Couleur du bouton d'annulation (bleu par défaut)
        confirmButtonText: 'Oui, supprimer !',
        cancelButtonText: 'Annuler',
    });

    // Si l'utilisateur clique sur "Oui"
    if (result.isConfirmed) {
        try {
            const response = await sendCityData(`/city/delete/${id}`, null, 'POST'); // Utiliser la méthode DELETE
            console.log(response);
            if (response && response.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Supprimé !',
                    text: response.message
                });
                // Supprimer la ligne de la table après la suppression
                row.remove();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: response.message
                });
            }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: error.message,
            });
        }
    }
}



// Fonction générique pour envoyer des données avec fetch
async function sendCityData(url, data, method = 'POST') {
    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
            },
            body: data ? JSON.stringify(data) : null, // Envoyer le body seulement s'il y a des données
        });

        // Vérification de la réponse
        if (!response.ok) {
            const errorResponse = await response.json(); // Extraire le corps de la réponse
            throw new Error(errorResponse.message || 'Erreur lors de l\'opération'); // Utiliser le message de l'erreur
        }

        return await response.json(); // Retourner la réponse (si nécessaire)
    } catch (error) {
        return { status: 'error', message: error.message }; // Retourner un message d'erreur
    }
}
