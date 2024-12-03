<?php
namespace blackjack;

use blackjack\Helpers\GameDatabaseHelper;

class Game extends GameDatabaseHelper
{
    private Deck $deck;

    /**
     * Game constructor.
     * Initializes the game and the deck instance.
     *
     * @param Deck $deck The Deck object
     */
    public function __construct(Deck $deck)
    {
        parent::__construct();
        $this->deck = $deck;
    }

    /**
     * Check if a game exists in the database.
     *
     * @param string $gameId The ID of the game to check
     * @return bool True if the game exists, false otherwise
     */
    public function checkIfGameExists(string $gameId): bool
    {
        return $this->getGame($gameId);
    }

    /**
     * Create a new game and initialize the deck.
     * The game ID is generated, and a deck is created and stored in the database.
     */
    public function createGame(): void
    {
        $gameId = $this->generateGameId();
        $deck = json_encode($this->deck->createDeck());

        // Initialize the game with the generated game ID and deck
        $this->initGame($gameId, $deck);

        // Commit the transaction
        $this->conn->commit();

        // Send success response
        Response::success("Game created successfully", ['game_id' => $gameId]);
    }

    /**
     * Generate a unique game ID.
     * The ID consists of six characters (alphanumeric).
     *
     * @return string The generated game ID
     */
    private function generateGameId(): string
    {
        $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $gameId = '';
        for ($i = 0; $i < 6; $i++) {
            $gameId .= $characters[random_int(0, strlen($characters) - 1)];
        }
        return $gameId;
    }
}
