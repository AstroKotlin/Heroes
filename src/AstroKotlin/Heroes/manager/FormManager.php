<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes\manager;

use AstroKotlin\Heroes\form\SimpleForm;
use AstroKotlin\Heroes\Heroes;
use pocketmine\player\Player;

class FormManager {

    public static function chooseClass(Player $player): void {
        $player->sendForm((new SimpleForm("Choose class", "Choose class for starter", function (Player $player, $data) {
            if($data === null) {
                self::chooseClass($player);
                return;
            }

            Heroes::getInstance()->addPlayerData($player, );
        })));
    }
}