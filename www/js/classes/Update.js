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

    updateGameStatus(gameStatus) {
        this.updateCards(this.dealerCardsContainer, gameStatus.dealerCards);

        this.updateCards(this.playerCardsContainer, gameStatus.playerCards);


        gameStatus.opponents.forEach((opponent, index) => {
            this.updateCards(this.opponentsCardsContainers[index], opponent.cards);
        });

        this.updateChips(this.chipsContainer, gameStatus.playerBet);


        this.updateActions(gameStatus);
    }


    updateCards(container, cards) {
        container.innerHTML = '';

        cards.forEach(card => {
            const cardElement = document.createElement('img');
            cardElement.src = `cards_img/${card}`;
            cardElement.classList.add('card');
            container.appendChild(cardElement);
        });
    }


    updateChips(container, betAmount) {
        container.innerHTML = '';


        const chipValues = [25, 10];
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


    updateActions(gameStatus) {
        const hitButton = document.querySelector('.action-button.hit');
        const standButton = document.querySelector('.action-button.stand');

        hitButton.disabled = !gameStatus.canHit;

        standButton.disabled = !gameStatus.canStand;
    }

    startGame() {
        this.game.getGameStatus();

        setInterval(() => {
            this.game.getGameStatus();
        }, 5000);
    }
}

const game = new BlackjackGame('https://your-api-base-url.com');

game.startGame();
