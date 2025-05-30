document.addEventListener('DOMContentLoaded', () => {


    const user = DependencyManager.get("User");

    const loginForm = document.querySelector('form:nth-of-type(1)');
    const loginEmail = loginForm.querySelector('input[type="email"]');
    const loginPassword = loginForm.querySelector('input[type="password"]');
    const loginBtn = loginForm.querySelector('.btn');

    const registerForm = document.querySelector('form:nth-of-type(2)');
    const registerName = registerForm.querySelector('input[type="text"]');
    const registerEmail = registerForm.querySelector('input[type="email"]');
    const registerPassword = registerForm.querySelector('input[type="password"]');
    const registerBtn = registerForm.querySelector('.btn');
    
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const email = loginEmail.value.trim();
        const password = loginPassword.value.trim();

        if (email && password) {
            try {
                const response = await user.login(email, password);
                alert('Zalogowano pomyślnie!');
                console.log(response);
            } catch (error) {
                alert('Błąd logowania! Sprawdź dane.');
                console.error('Login error:', error);
            }
        } else {
            alert('Proszę wypełnić wszystkie pola.');
        }
    });

    registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const name = registerName.value.trim();
        const email = registerEmail.value.trim();
        const password = registerPassword.value.trim();

        if (name && email && password) {
            try {
                const response = await user.register(name, email, password);
                alert('Rejestracja zakończona sukcesem!');
                console.log(response);
            } catch (error) {
                alert('Błąd rejestracji! Spróbuj ponownie.');
                console.error('Registration error:', error);
            }
        } else {
            alert('Proszę wypełnić wszystkie pola.');
        }
    });
});
