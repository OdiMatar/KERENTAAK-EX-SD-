window.setTimeout(() => {
    document.querySelectorAll('.alert.auto-dismiss').forEach((element) => {
        element.remove();
    });
}, 3000);

document.querySelectorAll('[data-bs-dismiss="alert"]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('.alert')?.remove();
    });
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!window.confirm(form.dataset.confirm)) {
            event.preventDefault();
        }
    });
});

document.querySelectorAll('[data-appointment-form]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        if (!form.checkValidity()) {
            event.preventDefault();
            form.classList.add('was-validated');
        }
    });
});
