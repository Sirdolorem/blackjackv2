export class Game {
    constructor(api) {
        this.api = api;
    }

    async createGame() {
        return this.api.request('/api/game/create', 'POST');
    }

    async joinGame(gameId) {
        const data = { gameId };
        return this.api.request('/api/game/join', 'POST', data);
    }

    async leaveGame(gameId) {
        const data = { gameId };
        return this.api.request('/api/game/leave', 'POST', data);
    }

    async startGame() {
        return this.api.request('/api/game/start', 'POST');
    }

    async getGameStatus() {
        return this.api.request('/api/game/status', 'GET');
    }

    async getGameResult() {
        return this.api.request('/api/game/result', 'GET');
    }

    async endGame() {
        return this.api.request('/api/game/end', 'POST');
    }

    async hit() {
        return this.api.request('/api/game/hit', 'POST');
    }

    async stand() {
        return this.api.request('/api/game/stand', 'POST');
    }

    async double() {
        return this.api.request('/api/game/double', 'POST');
    }

    async split() {
        return this.api.request('/api/game/split', 'POST');
    }

    async getHands() {
        return this.api.request('/api/game/hands', 'GET');
    }

    async placeBet(betAmount) {
        const data = { bet: betAmount };
        return this.api.request('/api/game/bet', 'POST', data);
    }

    async updateBet(betAmount) {
        const data = { bet: betAmount };
        return this.api.request('/api/game/bet', 'PUT', data);
    }

    async clearBets() {
        return this.api.request('/api/game/clear_bets', 'POST');
    }

    async getLeaderboard() {
        return this.api.request('/api/leaderboard', 'GET');
    }
}