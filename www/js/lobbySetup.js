const game = DependencyManager.get("Game");

// Event listener for the "Create a Game" button
const createGameButton = document.querySelector('.create-game-button');
createGameButton.addEventListener('click', async () => {
    const result = await game.createGame();
    if (result.success) {
        // Redirect to the game page with the game ID
        window.location.href = `/game.html?gameId=${result.gameId}`;
    } else {
        alert(`Error: ${result.message}`);
    }
});

// Event listener for the "Join a Game" button
const joinGameButton = document.querySelector('.join-game-button');
joinGameButton.addEventListener('click', async () => {
    const gameId = prompt('Enter the Game ID you want to join:');
    if (gameId) {
        const result = await game.joinGame(gameId);
        if (result.success) {
            // Redirect to the game page with the game ID
            window.location.href = `/game.html?gameId=${gameId}`;
        } else {
            alert(`Error: ${result.message}`);
        }
    } else {
        alert('Game ID is required to join a game.');
    }
});
