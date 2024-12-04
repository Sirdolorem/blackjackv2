CREATE DATABASE IF NOT EXISTS blackjack;

USE blackjack;

GRANT ALL PRIVILEGES ON blackjack.* TO 'blackjack'@'%';
FLUSH PRIVILEGES;

CREATE TABLE users (
                       user_id VARCHAR(6) PRIMARY KEY,
                       username VARCHAR(50) NOT NULL UNIQUE,
                       password VARCHAR(255) NOT NULL,
                       chips INT DEFAULT 1000
);

CREATE TABLE games (
                       game_id VARCHAR(6) PRIMARY KEY,
                       deck JSON DEFAULT NULL,
                       active_user VARCHAR(6) DEFAULT NULL,
                       dealer JSON DEFAULT NULL,
                       status ENUM('waiting', 'active', 'finished', 'cancelled') DEFAULT 'waiting',
                       FOREIGN KEY (active_user) REFERENCES users(user_id)
);


CREATE TABLE game_bets (
                           id INT AUTO_INCREMENT PRIMARY KEY,
                           game_id VARCHAR(6) NOT NULL,
                           user_id VARCHAR(6) NOT NULL,
                           bet INT NOT NULL,
                           is_double BOOLEAN DEFAULT FALSE,
                           FOREIGN KEY (game_id) REFERENCES games(game_id),
                           FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE actions (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         game_id VARCHAR(6) NOT NULL,
                         user_id VARCHAR(6) NOT NULL,
                         action ENUM('hit', 'stand', 'double', 'split', 'surrender') NOT NULL,
                         card VARCHAR(50) DEFAULT NULL,
                         timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                         FOREIGN KEY (game_id) REFERENCES games(game_id) ON DELETE CASCADE,
                         FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE players (
                         id INT AUTO_INCREMENT PRIMARY KEY,
                         game_id VARCHAR(6) NOT NULL,
                         user_id VARCHAR(6) NOT NULL,
                         slot INT NOT NULL,
                         status ENUM('win', 'bust', 'stand', 'active', 'waiting') DEFAULT 'waiting',
                         FOREIGN KEY (game_id) REFERENCES games(game_id),
                         FOREIGN KEY (user_id) REFERENCES users(user_id),
                         UNIQUE (game_id, slot)
);


CREATE TABLE hands (
                       id INT AUTO_INCREMENT PRIMARY KEY,
                       game_id VARCHAR(6) NOT NULL,
                       user_id VARCHAR(6) NOT NULL,
                       hand JSON DEFAULT NULL,
                       FOREIGN KEY (game_id) REFERENCES games(game_id),
                       FOREIGN KEY (user_id) REFERENCES users(user_id)
);
