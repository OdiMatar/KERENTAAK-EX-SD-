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

const deleteModal = document.querySelector('[data-delete-modal]');
const deleteForm = document.querySelector('[data-delete-form]');
const deleteName = deleteModal?.querySelector('[data-delete-name]');

document.querySelectorAll('[data-delete-modal-open]').forEach((button) => {
    button.addEventListener('click', () => {
        if (!deleteModal || !deleteForm || !deleteName) {
            return;
        }

        deleteForm.action = button.dataset.deleteAction;
        deleteName.textContent = button.dataset.deleteName;
        deleteModal.classList.remove('d-none');
        deleteModal.setAttribute('aria-hidden', 'false');
    });
});

document.querySelectorAll('[data-delete-modal-close]').forEach((button) => {
    button.addEventListener('click', () => {
        deleteModal?.classList.add('d-none');
        deleteModal?.setAttribute('aria-hidden', 'true');
    });
});

const klantSearch = document.querySelector('[data-klant-search]');
const klantRows = document.querySelectorAll('[data-klant-row]');
const klantTable = document.querySelector('[data-klant-table]');
const klantEmpty = document.querySelector('[data-klant-empty]');

klantSearch?.addEventListener('input', () => {
    const zoekterm = klantSearch.value.trim().toLowerCase();
    let zichtbareRijen = 0;

    klantRows.forEach((row) => {
        const zichtbaar = row.dataset.klantName.includes(zoekterm);
        row.classList.toggle('d-none', !zichtbaar);
        zichtbareRijen += zichtbaar ? 1 : 0;
    });

    klantTable?.classList.toggle('d-none', zichtbareRijen === 0 && zoekterm !== '');
    klantEmpty?.classList.toggle('d-none', zichtbareRijen > 0 || zoekterm === '');
});

document.querySelectorAll('[data-klant-form]').forEach((form) => {
    const fields = {
        naam: form.querySelector('[name="naam"]'),
        adres: form.querySelector('[name="adres"]'),
        telefoonnummer: form.querySelector('[name="telefoonnummer"]'),
        email: form.querySelector('[name="email"]'),
    };
    const existingCustomers = JSON.parse(form.dataset.existingCustomers || '[]');
    const formAlert = form.querySelector('[data-klant-form-alert]');

    const setError = (field, message) => {
        const wrapper = field.closest('div');
        let error = wrapper.querySelector('.form-text.text-danger');

        if (!error) {
            error = document.createElement('div');
            error.className = 'form-text text-danger';
            wrapper.append(error);
        }

        field.classList.add('is-invalid');
        error.textContent = message;
    };

    const clearError = (field) => {
        field.classList.remove('is-invalid');
        field.closest('div')?.querySelector('.form-text.text-danger')?.remove();
    };

    const validate = () => {
        let valid = true;

        Object.values(fields).forEach(clearError);
        formAlert?.classList.add('d-none');

        if (!fields.naam.value.trim()) {
            setError(fields.naam, 'Naam is een verplicht veld en mag niet leeg zijn');
            valid = false;
        }

        if (!fields.telefoonnummer.value.trim()) {
            setError(fields.telefoonnummer, 'Telefoonnummer is een verplicht veld en mag niet leeg zijn');
            valid = false;
        }

        if (!fields.adres.value.trim()) {
            setError(fields.adres, 'Adres is een verplicht veld en mag niet leeg zijn');
            valid = false;
        }

        if (!fields.email.value.trim() || !fields.email.value.includes('@')) {
            setError(fields.email, 'Vul een geldig e-mailadres in');
            valid = false;
        }

        const duplicateCustomer = existingCustomers.some((customer) => (
            customer.email === fields.email.value.trim().toLowerCase()
            && customer.adres === fields.adres.value.trim().toLowerCase()
        ));

        if (duplicateCustomer) {
            setError(fields.email, 'Er bestaat al een klant met dit adres en e-mailadres');
            valid = false;
        }

        return valid;
    };

    const hasCompleteAddress = (value) => {
        const postcodePattern = /\b[1-9][0-9]{3}\s?[a-z]{2}\b/i;
        const addressWithoutPostcode = value.replace(postcodePattern, '');
        const hasPostcode = postcodePattern.test(value);
        const hasHouseNumber = /\b[0-9]+[a-z]?\b/i.test(addressWithoutPostcode);
        const hasStreet = /[a-zÀ-ÿ]{2,}.*\b[0-9]+[a-z]?\b/i.test(addressWithoutPostcode);
        const hasCity = /\b[1-9][0-9]{3}\s?[a-z]{2}\b[\s,]+[a-zÀ-ÿ][a-zÀ-ÿ\s-]{1,}$/i.test(value);

        return hasStreet && hasHouseNumber && hasPostcode && hasCity;
    };

    const hasRequiredAddressParts = (value) => {
        const postcodePattern = /\b[1-9][0-9]{3}\s?[a-z]{2}\b/i;
        const addressWithoutPostcode = value.replace(postcodePattern, '');
        const hasPostcode = postcodePattern.test(value);
        const hasHouseNumber = /\b[0-9]+[a-z]?\b/i.test(addressWithoutPostcode);
        const hasStreet = /[^\d\s,][^\d,]*\b[0-9]+[a-z]?\b/i.test(addressWithoutPostcode);
        const hasCity = /\b[1-9][0-9]{3}\s?[a-z]{2}\b[\s,]+[^\d,]{2,}$/i.test(value);

        return hasStreet && hasHouseNumber && hasPostcode && hasCity;
    };

    form.addEventListener('submit', (event) => {
        const valid = validate();

        if (fields.adres.value.trim() && !hasRequiredAddressParts(fields.adres.value.trim())) {
            setError(fields.adres, 'Straatnaam, huisnummer, postcode en stad zijn verplicht om te vullen.');
        }

        if (!valid || fields.adres.classList.contains('is-invalid')) {
            event.preventDefault();
            formAlert?.classList.remove('d-none');
            formAlert?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    Object.values(fields).forEach((field) => {
        field.addEventListener('input', () => {
            clearError(field);

            if (!form.querySelector('.is-invalid')) {
                formAlert?.classList.add('d-none');
            }
        });
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
