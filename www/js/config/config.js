import {Game} from "../classes/Game";
import {API} from "../classes/Api";
import {User} from "../classes/User";
import {Update} from "../classes/Update";

const API_URL = 'https://your-api-base-url.com';


DependencyManager.register("Api", () => {
    return new API(API_URL)
});

const api = DependencyManager.get("Api");

DependencyManager.register("Game", () => {

    return new Game(api);
});

DependencyManager.register("User", () => {
    return new User(api);
})

DependencyManager.register("Update", () => {
    const game = DependencyManager.get("Game");
    return new Update(api, game);
})