const loginForm = document.getElementById('loginForm');
const loginButton = document.getElementById('loginButton');
const loginError = document.getElementById('loginError');

api.me().then((result) => {
    if (result?.success) window.location.href = 'index.html';
});

loginForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    loginError.hidden = true;
    loginButton.disabled = true;
    loginButton.textContent = 'Đang xác thực...';

    const result = await api.login(
        document.getElementById('username').value.trim(),
        document.getElementById('password').value
    );

    if (result?.success) {
        window.location.href = 'index.html';
        return;
    }

    loginError.textContent = result?.message || 'Đăng nhập không thành công.';
    loginError.hidden = false;
    loginButton.disabled = false;
    loginButton.innerHTML = 'Đăng nhập <span aria-hidden="true">→</span>';
});
