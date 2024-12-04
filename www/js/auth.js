function checkAuth() {
    const token = localStorage.getItem('authToken');

    if (!token) {
        window.location.href = '/login.html';
    }

    // fetch('/validate-token.php', { headers: { 'Authorization': `Bearer ${token}` } })
    //   .then(response => response.json())
    //   .then(data => {
    //     if (!data.valid) {
    //       window.location.href = '/login.html'; // Redirect to login if token is invalid
    //     }
    //   });
}

window.onload = checkAuth;
