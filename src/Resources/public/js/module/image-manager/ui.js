export function showModal(selector) {
    const el = document.querySelector(selector);
    if (el) new bootstrap.Modal(el).show();
}

export function hideModal(selector) {
    const el = document.querySelector(selector);
    if (el) bootstrap.Modal.getInstance(el)?.hide();
}

export function showAlert(selector, message) {
    const el = document.querySelector(selector);
    if (el) {
        el.innerHTML = message;
        el.style.display = 'block';
    }
}

export function hideAlert(selector) {
    const el = document.querySelector(selector);
    if (el) el.style.display = 'none';
}

export function disableButton(button, loadingText = null) {
    if (button) {
        button.disabled = true;
        if (loadingText) button.innerHTML = loadingText;
    }
}

export function enableButton(button, text = null) {
    if (button) {
        button.disabled = false;
        if (text) button.innerHTML = text;
    }
}
