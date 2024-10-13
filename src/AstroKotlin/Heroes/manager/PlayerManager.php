<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes\manager;

use AstroKotlin\Heroes\Heroes;
use pocketmine\player\Player;

class PlayerManager {

    public function __construct(private Player $player) {}

    public function getPlugin(): Heroes {
        return Heroes::getInstance();
    }

    public function getPlayer(): Player {
        return $this->player;
    }

    public function setClass(string $class) {
        $this->getPlugin()->setClass();
    }
}