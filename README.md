# Game API

This API allows users to manage and interact with a game, including user authentication, game creation, player actions, and leaderboard management.

## Authentication

### Endpoints
| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/register` | POST | Register a new user |
| `/api/login` | POST | Log in an existing user |
| `/api/validate` | GET | Validate JWT token |

## Game Management

### Endpoints
| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/game/create` | POST | Create a new game |
| `/api/game/join` | POST | Join an existing game |
| `/api/game/leave` | POST | Leave a game |
| `/api/game/start` | POST | Start the game |
| `/api/game/status` | GET | Get the current game status |
| `/api/game/result` | GET | Retrieve the game's result |
| `/api/game/end` | POST | End the game (admin/system action) |

## Player Actions

### Endpoints
| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/game/hit` | POST | Deal another card to player |
| `/api/game/stand` | POST | End the player's turn |
| `/api/game/double` | POST | Double the bet and hit once |
| `/api/game/split` | POST | Split the player's hand |
| `/api/game/hands` | GET | Get all player hands |

## Bet Management

### Endpoints
| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/game/bet` | POST | Place a bet before the game starts |
| `/api/game/bets` | GET | View all bets for a game |
| `/api/game/bet` | PUT | Update an existing bet |
| `/api/game/clear_bets` | POST | Clear all bets (admin/system) |

## Leaderboard

### Endpoints
| Endpoint | Method | Description |
| -------- | ------ | ----------- |
| `/api/leaderboard` | GET | Get the top players by score |

## Database Schema

### Users
| Field     | Type          | Description       |
| --------- | ------------- | ----------------- |
| `user_id` | varchar(6)    | Unique user ID    |
| `username`| varchar(50)   | Username          |
| `password`| varchar(255)  | Hashed password   |

### Games
| Field     | Type          | Description          |
| --------- | ------------- | -------------------- |
| `game_id` | varchar(6)    | Unique game ID       |
| `game_name`| varchar(50)  | Name of the game     |
| `status`  | enum          | Game status (ongoing, finished) |

### Bets
| Field     | Type          | Description          |
| --------- | ------------- | -------------------- |
| `bet_id`  | int           | Auto-increment ID    |
| `game_id` | varchar(6)    | Associated game ID   |
| `player_id`| varchar(6)   | Associated player ID |
| `amount`  | decimal(10,2) | Bet amount           |


