<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes;

use AstroKotlin\Heroes\commands\HeroesCommand;
use AstroKotlin\Heroes\provider\ScoreProvider;
use pocketmine\player\Player;
use pocketmine\plugin\PLuginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;
use vennv\vapm\System;
use vennv\vapm\VapmPMMP;

class Heroes extends PLuginBase {

    use SingletonTrait;
    public Config $itemCfg;
    public Config $playerData;
    public Config $classData;
    public Config $scoreData;

    public ScoreProvider $scoreProvider;

    protected function onLoad(): void {
        self::setInstance($this);
    }

    protected function onEnable(): void {
        VapmPMMP::init($this);
        $this->saveResource("item.yml");
        $this->saveResource("class.yml");
        $this->saveResource("scoreboard.yml");

        $this->scoreProvider = new ScoreProvider();
        $this->itemCfg = new Config($this->getDataFolder() . "item.yml", Config::YAML);
        $this->playerData = new Config($this->getDataFolder() . "player-data.yml", Config::YAML);
        $this->classData = new Config($this->getDataFolder() . "class.yml", Config::YAML);
        $this->scoreData = new Config($this->getDataFolder() . "scoreboard.yml", Config::YAML);

        $this->getServer()->getCommandMap()->register("heroes", new HeroesCommand($this));

        System::setInterval(function() {
            foreach($this->getServer()->getOnlinePlayers() as $player) {
                $this->scoreProvider->create($player, 'Heroes', $this->scoreData->get('title'));
                foreach($this->scoreData->get('scoreboard') as $line => $text) {
                    $this->scoreProvider->setLine($player, $line, $this->replaceData($player, $text));
                }
                $this->scoreProvider->update($player);
            }
        }, $this->scoreData->get("update-second") * 1000);

        $this->getLogger()->info("Heroes has on enabled.");
    }

    public function replaceData(Player $player, string $text): string {
        return str_replace(["{player.name}"], [$player->getName()], $text);
    }

    public function getPlayerData(): Config {
        return $this->playerData;
    }

    public function existsPlayerData(Player $player): bool {
        return $this->getPlayerData()->exists($player->getName());
    }

    public function addPlayerData(Player $player, string $class): void {
        $this->getPlayerData()->set($player->getName(), ['class' => $class, 'stats' => ['health'=> 0, 'mana' => 0, 'armor' => 0, 'strength' => 0], 'effects' => []]);
        $this->getPlayerData()->save();
    }

    public function getLoreFormat(): string {
        return (string)$this->itemCfg->get("item-lorem-format");
    }

    public function getProp(string $prop): string {
        return $this->itemCfg->get('properties')[$prop];
    }
}