
import { API_URL } from './config/config.js';

const { API } = await import('./classes/API.js');
const { User } = await import('./classes/User.js');
const { Game } = await import('./classes/Game.js');

const api = new API(API_URL);
const game = new Game(api);



document.querySelector('.hit-button').addEventListener('click', () => {
    game.hit()
        .then(response => {
            console.log('Card dealt:', response);
        })
        .catch(error => {
            console.error('Hit action error:', error);
        });
});

document.querySelector('.stand-button').addEventListener('click', () => {
    game.stand()
        .then(response => {
            console.log('Player stands:', response);
        })
        .catch(error => {
            console.error('Stand action error:', error);
        });
});
