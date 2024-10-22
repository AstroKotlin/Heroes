<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes;

use AstroKotlin\Heroes\commands\HeroesCommand;
use AstroKotlin\Heroes\provider\ScoreProvider;
use pocketmine\player\Player;
use pocketmine\plugin\PLuginBase;
use pocketmine\utils\SingletonTrait;
use pocketmine\utils\Config;
use vennv\vapm\SampleMacro;
use vennv\vapm\System;
use vennv\vapm\VapmPMMP;

class Heroes extends PLuginBase {

    use SingletonTrait;

    public Config $itemCfg;

    public Config $playerData;

    public Config $classData;

    public Config $scoreData;

    public ScoreProvider $scoreProvider;

    public ?SampleMacro $sb;

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

        if($this->scoreData->get("active") === true)
        $this->sb = System::setInterval(function() {
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

    public function addPlayerData(Player $player, string $className): void {
        $class = $this->getClass($className);
        $this->getPlayerData()->set($player->getName(), ['class' => $className, 'stats' => ['health'=> $class["health"], 'mana' => $class["mana"], 'armor' => $class["armor"], 'strength_bonus' => $class["strength_bonus"]], 'effects' => []]);
        $this->getPlayerData()->save();
    }

    public function getLoreFormat(): string {
        return (string)$this->itemCfg->get("item-lorem-format");
    }

    public function getClass(string $class): array {
        return $this->classData->get($class);
    }

    public function getProp(string $prop): string {
        return $this->itemCfg->get('properties')[$prop];
    }

    public function getClassName(Player $player): string {
        return $this->classData->get($player->getName())["class"];
    }

    public function getMana(Player $player): int {
        return $this->playerData->get($player->getName())["stats"]["mana"];
    }

    public function getArmor(Player $player): int {
        return $this->playerData->get($player->getName())["stats"]["armor"];
    }

    public function getStrengthBonus(Player $player): int {
        return $this->playerData->get($player->getName())["stats"]["strength_bonus"];
    }

    public function getEffects(Player $player): array {
        return $this->playerData->get($player->getName())["stats"]["effects"];
    }
}