<?php

namespace blackjack;

class DependencyManager
{
    private static array $instances = [];
    private static ?\mysqli $mysqliConnection = null;

    /**
     * Initialize the DependencyManager with a mysqli connection
     */
    public static function init(): void
    {
        self::$mysqliConnection = Database::getInstance()->getConnection();
    }

    /**
     * Get an instance of a class
     *
     * @param string $class
     * @return object
     * @throws \Exception
     */
    public static function get(string $class): object
    {
        if (!isset(self::$instances[$class])) {
            self::$instances[$class] = self::createInstance($class);
        }

        return self::$instances[$class];
    }

    /**
     * Create a new instance of a class
     *
     * @param string $class
     * @return object
     * @throws \Exception
     */
    private static function createInstance(string $class): object
    {
        return match ($class) {
            JWTAuth::class => new JWTAuth(),
            Game::class => new Game(
                self::$mysqliConnection,
                self::get(Deck::class)
            ),
            Player::class => new Player(
                self::$mysqliConnection,
                self::get(Deck::class),
                self::get(Game::class)
            ),
            Response::class => new Response(),
            User::class => new User(
                self::$mysqliConnection,
                self::get(JWTAuth::class)
            ),
            GameActions::class => new GameActions(
                self::$mysqliConnection,
                self::get(Deck::class),
                self::get(Player::class)
            ),
            Middleware::class => new Middleware(),
            Deck::class => new Deck(
                self::$mysqliConnection,
                self::get(Player::class)
            ),
            Bet::class => new Bet(
                self::$mysqliConnection
            ),
            ActionCheck::class => new ActionCheck(
                self::$mysqliConnection,
                self::get(Player::class)
            ),
            default => throw new \Exception("Class $class not recognized in DependencyManager."),
        };
    }
}
