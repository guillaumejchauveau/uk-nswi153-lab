<?php

class Nim
{
    /**
     * 0: initial
     * 1: user playing
     * 2: computer playing
     * 3: user won
     * 4: computer won
     * @var int
     */
    protected $state;
    /**
     * @var int
     */
    protected $initial;
    /**
     * @var int
     */
    protected $matches;
    /**
     * @var int
     */
    protected $taken;
    /**
     * @var int
     */
    protected $nextSeed;

    public function __construct($initial, $matches, $seed)
    {
        if ($initial === null) {
            $this->state = 0;
        } else {
            $this->initial = intval($initial);
            if (!is_numeric($initial) || $this->initial < 2 || $this->initial > 50) {
                $this->badRequest("Invalid initial count");
            }
            if ($matches === null) {
                $this->state = 1;
                $this->matches = $this->initial;
            } else {
                $this->matches = intval($matches);
                if (!is_numeric($matches) || $this->matches < 0 || $this->matches > $this->initial) {
                    $this->badRequest("Invalid matches count");
                }
                if ($this->matches === 0) {
                    $this->state = 4;
                    $this->taken = 0;
                } elseif ($this->matches === 1) {
                    $this->state = 3;
                    $this->matches = 0;
                    $this->taken = 1;
                } else {
                    $this->state = 2;
                }
            }
        }

        // Use the seed specified in the url for consistency.
        if ($seed !== null) {
            if (!is_numeric($seed)) {
                $this->badRequest("Invalid seed");
            }
            srand(intval($seed));
        }
        // Generate a new seed for next game step.
        $this->nextSeed = time();
    }

    public function play(): void
    {
        if ($this->state !== 2) {
            return;
        }
        // Matches > 1.
        $this->taken = ($this->matches - 1) % 4;
        if ($this->taken === 0) {
            $this->taken = rand(1, 3);
        }
        $this->matches -= $this->taken;
    }

    public function display(): void
    {
        require "template.php";
    }

    protected function badRequest(string $message): void
    {
        echo $message;
        http_response_code(400);
        exit();
    }

    public static function run()
    {
        $game = new Nim($_GET['initial'] ?? null, $_GET['matches'] ?? null, $_GET['seed'] ?? null);
        $game->play();
        $game->display();
    }
}

Nim::run();
