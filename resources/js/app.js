import 'bootstrap';
import 'admin-lte';

document.addEventListener('DOMContentLoaded', () => {
    const deleteModal = document.getElementById('deleteProductModal');

    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', (event) => {
            const button = event.relatedTarget;
            const form = deleteModal.querySelector('form');
            const nameTarget = deleteModal.querySelector('[data-delete-product-name]');

            form.action = button.getAttribute('data-delete-action');
            nameTarget.textContent = button.getAttribute('data-delete-name');
        });
    }

    document.querySelectorAll('form.needs-validation').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });
    });

    document.querySelectorAll('.alert-auto-dismiss').forEach((alert) => {
        window.setTimeout(() => {
            window.bootstrap.Alert.getOrCreateInstance(alert).close();
        }, 5000);
    });

    document.querySelectorAll('[data-auto-submit]').forEach((select) => {
        select.addEventListener('change', () => {
            select.form.submit();
        });
    });

    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((element) => {
        window.bootstrap.Tooltip.getOrCreateInstance(element);
    });
});
