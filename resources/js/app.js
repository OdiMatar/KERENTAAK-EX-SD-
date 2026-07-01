import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

window.setTimeout(() => {
    document.querySelectorAll('.alert.auto-dismiss').forEach((element) => {
        const alert = bootstrap.Alert.getOrCreateInstance(element);
        alert.close();
    });
}, 3000);
