export class API {
    constructor(baseURL) {
        this.baseURL = baseURL;
        this.token = null;
    }

    // Set JWT token for authenticated requests
    setToken(token) {
        this.token = token;
    }

    // Utility to handle API calls with authentication
    async request(endpoint, method = 'POST', data = null) {
        const headers = {
            'Content-Type': 'application/json',
            'Authorization': this.token ? `Bearer ${this.token}` : '',
        };

        const options = {
            method,
            headers,
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${this.baseURL}${endpoint}`, options);
            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Something went wrong');
            }
            return result;
        } catch (error) {
            console.error('API Request Error:', error);
            throw error;
        }
    }
}