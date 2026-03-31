document.querySelectorAll('.login-card form').forEach(form => {

    form.addEventListener('submit', function (e) {

        let errors = [];

        const emailInput = form.querySelector('input[name="email"]');
        const passwordInput = form.querySelector('input[name="password"]');

        const email = emailInput.value.trim();
        const password = passwordInput.value;

        // 🔹 Email
        if (!Validator.required(email)) {
            errors.push("L'adresse email est obligatoire.");
        } else if (!Validator.email(email)) {
            errors.push("L'adresse email n'est pas valide.");
        }

        // 🔹 Password
        if (!Validator.required(password)) {
            errors.push("Le mot de passe est obligatoire.");
        } else if (!Validator.password(password, 6)) {
            errors.push("Le mot de passe doit contenir au moins 6 caractères.");
        }

        // 🔥 If errors → stop form
        if (errors.length > 0) {
            e.preventDefault();
            showErrors(form, errors);

            // Highlight fields
            emailInput.classList.toggle('input-error', !Validator.email(email));
            passwordInput.classList.toggle('input-error', !Validator.password(password, 6));
        }

    });

});


// 🔹 Display errors nicely
function showErrors(form, errors) {

    // Remove old error box
    let old = form.querySelector('.login-alert-error');
    if (old) old.remove();

    const errorDiv = document.createElement('div');
    errorDiv.className = 'login-alert login-alert-error';

    errorDiv.innerHTML = `
        <ul style="margin:0; padding-left:20px;">
            ${errors.map(e => `<li>${e}</li>`).join('')}
        </ul>
    `;

    form.prepend(errorDiv);
}