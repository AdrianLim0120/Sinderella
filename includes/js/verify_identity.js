document.getElementById('verifyIdentityForm').addEventListener('submit', function(event) {
    const icNumber = document.getElementById('ic_number').value;
    const errorMessage = document.getElementById('error-message');

    // Clear previous error message
    errorMessage.innerText = '';

    // Validate IC number
    if (!/^\d{12}$/.test(icNumber)) {
        errorMessage.innerText = 'IC number must be a 12-digit numeric value.';
        event.preventDefault();
        return;
    }
});