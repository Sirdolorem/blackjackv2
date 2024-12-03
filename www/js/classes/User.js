export class User {
    constructor(api) {
        this.api = api;
    }

    async register(username, password) {
        const data = { username, password };
        return this.api.request('/api/register', 'POST', data);
    }

    async login(username, password) {
        const data = { username, password };
        const result = await this.api.request('/api/login', 'POST', data);
        this.api.setToken(result.token);  // Store the JWT token
        return result;
    }

    async validateToken() {
        return this.api.request('/api/validate', 'GET');
    }
}

