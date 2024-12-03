export class Update {
    constructor(api, game) {
        this.api = api;
        this.game = game
        this.dealerCardsContainer = document.querySelector('.dealer .cards');
        this.playerCardsContainer = document.querySelector('.player .cards');
        this.opponentsCardsContainers = document.querySelectorAll('.opponent .cards');
        this.chipsContainer = document.querySelector('.player .chips');
        this.actionsContainer = document.querySelector('.actions');
    }

    // Method to update the game status in the DOM
    updateGameStatus(gameStatus) {
        // Update dealer's cards
        this.updateCards(this.dealerCardsContainer, gameStatus.dealerCards);

        // Update player's cards
        this.updateCards(this.playerCardsContainer, gameStatus.playerCards);

        // Update opponents' cards
        gameStatus.opponents.forEach((opponent, index) => {
            this.updateCards(this.opponentsCardsContainers[index], opponent.cards);
        });

        // Update chips (player's bet)
        this.updateChips(this.chipsContainer, gameStatus.playerBet);

        // Update actions (Enable/Disable buttons based on game status)
        this.updateActions(gameStatus);
    }

    // Method to update cards in a given container
    updateCards(container, cards) {
        container.innerHTML = '';  // Clear previous cards

        cards.forEach(card => {
            const cardElement = document.createElement('img');
            cardElement.src = `cards_img/${card}`;  // Assuming the card file names are in this format
            cardElement.classList.add('card');
            container.appendChild(cardElement);
        });
    }

    // Method to update the player's chips (bet amount)
    updateChips(container, betAmount) {
        container.innerHTML = '';  // Clear previous chips

        // Assuming chip images are in multiples of 10, 25, etc.
        const chipValues = [25, 10];  // Can be extended to support more chip values
        chipValues.forEach(value => {
            const chipCount = Math.floor(betAmount / value);
            for (let i = 0; i < chipCount; i++) {
                const chipElement = document.createElement('img');
                chipElement.src = `chips/chip_${value}.png`;
                chipElement.classList.add('chip');
                container.appendChild(chipElement);
            }
        });
    }

    // Method to enable/disable action buttons based on game state
    updateActions(gameStatus) {
        const hitButton = document.querySelector('.action-button.hit');
        const standButton = document.querySelector('.action-button.stand');

        // Enable or disable actions based on the player's turn and game status
        hitButton.disabled = !gameStatus.canHit;

        standButton.disabled = !gameStatus.canStand;
    }

    // Method to start the game and periodically refresh the game status
    startGame() {
        // Fetch the initial game status
        this.game.getGameStatus();

        // Optionally, refresh the game status periodically
        setInterval(() => {
            this.game.getGameStatus();
        }, 5000);  // Refresh every 5 seconds (adjust as needed)
    }
}

// Instantiate the BlackjackGame class
const game = new BlackjackGame('https://your-api-base-url.com');

// Start the game by calling startGame method
game.startGame();
