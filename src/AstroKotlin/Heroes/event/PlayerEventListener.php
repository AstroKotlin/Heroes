<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes\event;

use AstroKotlin\Heroes\Heroes;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;

class PlayerEventListener implements Listener {

    public function onJoin(PlayerJoinEvent $event) {
        $player = $event->getPlayer();

        if(!Heroes::getInstance()->existsPlayerData($player)) {
            //... todo
        }
    }
}