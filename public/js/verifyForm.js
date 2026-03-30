document.querySelector('form').addEventListener('submit', function (e) {

    let errors = [];

    // 🔹 Required fields
    const requiredFields = [
        'formation',
        'niveau',
        'date_debut',
        'duree',
        'cv',
        'lettre',
        'consent'
    ];

    requiredFields.forEach(name => {
        const field = document.querySelector(`[name="${name}"]`);

        if (!field) return;

        if (field.type === 'checkbox') {
            if (!field.checked) {
                errors.push("Vous devez accepter les conditions.");
            }
        } else if (!field.value) {
            errors.push(`Le champ "${name}" est obligatoire.`);
        }
    });

    // Validation (CV + Lettre)
    const maxSize = 2 * 1024 * 1024; // 2MB

    const cv = document.getElementById('cv').files[0];
    const lettre = document.getElementById('lettre').files[0];

    const typearray = ['application/pdf', 'image/png', 'image/jpeg', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

    function validateFile(file, label) {
        if (!file) return;

        if (!typearray.includes(file.type)) {
            errors.push(`${label} doit être des types suivants : PDF, PNG, JPEG, DOCX.`);
        }

        if (file.size > maxSize) {
            errors.push(`${label} dépasse 2MB.`);
        }
    }

    validateFile(cv, "Le CV");
    validateFile(lettre, "La lettre de motivation");

    // 🔹 URL validation (portfolio)
    const portfolio = document.getElementById('portfolio').value;

    if (portfolio) {
        try {
            const url = new URL(portfolio);

            // On vérifie si il y a bien "github.com" dans la string de l'url entrée
            if (!url.hostname.includes("github.com")) {
                errors.push("Le lien doit être un GitHub valide.");
            }

        } catch {
            errors.push("Le lien du portfolio n'est pas valide.");
        }
    }

    // Validation de la date (on ne veut pas qu'il y ait une date au passé)
    const dateInput = document.getElementById('date_debut').value;
    if (dateInput) {
        const selectedDate = new Date(dateInput);
        const today = new Date();
        today.setHours(0,0,0,0);

        if (selectedDate < today) {
            errors.push("La date de début ne peut pas être dans le passé.");
        }
    }

    // Valider la durée
    const duree = document.getElementById('duree').value;
    if (duree && duree <= 0) {
        errors.push("La durée doit être supérieure à 0.");
    }

    if (errors.length > 0) {
        e.preventDefault();

        alert(errors.join('\n'));
    }
});

function updateFileName(input, targetId) {
    const label = document.getElementById(targetId);
    label.textContent = input.files.length > 0
        ? input.files[0].name
        : 'Aucun fichier sélectionné';
}