<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes;

use pocketmine\plugin\PLuginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;

class Heroes extends PLuginBase {

    use SingletonTrait;
    public Config $itemCfg;
    public Config $playerData;
    public Config $classData;

    protected function onEnable(): void {
        $this->saveResource("item.yml");
        $this->itemCfg = new Config($this->getDataFolder() . "item.yml", Config::YAML);
        $this->playerData = new Config($this->getDataFolder() . "player-data.yml", Config::YAML);
        $this->classData = new Config($this->getDataFolder() . "class.yml", Config::YAML);
        $this->getLogger()->info("Heroes has on enabled.");
    }

    public function getPlayerData(): Config {
        return $this->playerData;
    }

    public function getLoreFormat(): string {
        return (string)$this->itemCfg->get("item-lorem-format");
    }

    public function getProp(string $prop): string {
        return $this->itemCfg->get('properties')[$prop];
    }
}