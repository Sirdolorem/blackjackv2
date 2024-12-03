<?php

namespace blackjack;

class DependencyManager
{
    private static array $instances = [];
    private static ?\mysqli $mysqliConnection = null;
    private static array $resolvingStack = [];

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

        if (in_array($class, self::$resolvingStack, true)) {
            throw new \Exception("Circular dependency detected for class: $class");
        }

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
            Player::class => new Player(
                self::get(Game::class)
            ),
            Response::class => new Response(),
            User::class => new User(
                self::get(JWTAuth::class)
            ),
            Game::class => new Game(
                self::get(Deck::class)
            ),
            GameActions::class => new GameActions(
                self::get(Deck::class),
                self::get(Player::class),
                self::get(ActionCheck::class),
            ),
            Middleware::class => new Middleware(),
            Deck::class => new Deck(
                self::get(Player::class),
            ),
            Bet::class => new Bet(),
            ActionCheck::class => new ActionCheck(
                self::get(Player::class),
                self::get(Bet::class),
            ),
            default => throw new \Exception("Class $class not recognized in DependencyManager."),
        };
    }
}
