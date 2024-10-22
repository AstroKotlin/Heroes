<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes\commands;

use AstroKotlin\Heroes\Heroes;
use AstroKotlin\Heroes\manager\ItemManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\data\bedrock\EnchantmentIdMap;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use vennv\vapm\SampleMacro;
use vennv\vapm\System;

class HeroesCommand extends Command implements PluginOwned {

    public const PREFIX = "§l§bHEROES§f§r ";
    public function __construct() {
        parent::__construct('heroes', 'Heroes command.', '/heroes');
        $this->setPermission('heroes.command');
    }

    public function getOwningPlugin(): Plugin {
        return Heroes::getInstance();
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool {
        if(!$player instanceof Player) {
            $player->sendMessage('Please use in game!');
            return false;
        }


        if($args[0] == "help" or !isset($args[0])) {
            $player->sendMessage(self::PREFIX . '§b Command Help:\n§e- /heroes <listenchant|le> - list all enchant id\n§e- /heroes <setname|name> - set name for item\n§e- /heroes <enchant|ec> - add enchant to item\n§e- /heroes scoreboard <true|false> - turn on|off scoreboard\n§e- /heroes prop set - set properties for item\n§e- /heroes prop add - add properties for item\n§e- /heroes prop list - list properties for item');
            return true;
        }

        if($args[0] == "setname" or $args[0] == "name") {
            if(!isset($args[1])) {
                $player->sendMessage(self::PREFIX . "§cPlease input name for rename!");
                return false;
            }
            $args[1] = (string)$args[1];

            $item = $player->getInventory()->getItemInHand();
            if($item->isNull()) {
                $player->sendMessage(self::PREFIX . "§cPlease put item in hand!");
                return false;
            }

            $item->setCustomName($args[1]);
            $player->getInventory()->setItemInHand($item);
            $player->sendMessage(self::PREFIX . "§cItem set name successfully!");
            return true;
        }

        if($args[0] == "listenchant" or $args[0] == "le") {
            $player->sendMessage("§ePROTECTION = 0, FIRE_PROTECTION = 1, FEATHER_FALLING = 2, BLAST_PROTECTION = 3, PROJECTILE_PROTECTION = 4, THORNS = 5, RESPIRATION = 6, DEPTH_STRIDER = 7, AQUA_AFFINITY = 8, SHARPNESS = 9, SMITE = 10, BANE_OF_ARTHROPODS = 11, KNOCKBACK = 12, FIRE_ASPECT = 13, LOOTING = 14, EFFICIENCY = 15, SILK_TOUCH = 16, UNBREAKING = 17, FORTUNE = 18, POWER = 19, PUNCH = 20, FLAME = 21, INFINITY = 22, LUCK_OF_THE_SEA = 23, LURE = 24, FROST_WALKER = 25, MENDING = 26, BINDING = 27, VANISHING = 28, IMPALING = 29, RIPTIDE = 30, LOYALTY = 31, CHANNELING = 32, MULTISHOT = 33, PIERCING = 34, QUICK_CHARGE = 35, SOUL_SPEED = 36");
            return true;
        }


        if($args[0] == "enchant" or $args[0] == "ec") {
            if(!isset($args[1])) {
                $player->sendMessage(self::PREFIX . "§cPlease input id enchant for enchant item!");
                return false;
            }

            if(!isset($args[2])) {
                $args[2] = 1;
            }

            $item = $player->getInventory()->getItemInHand();

            if($item->isNull()) {
                $player->sendMessage(self::PREFIX . "§cYou not has item in hand!");
                return false;
            }
            if(!is_numeric($args[1])) {
                $player->sendMessage(self::PREFIX . "§cEnchant item must be id enchant! /heroes <listenchant|le>");
                return false;
            }
            if(!is_numeric($args[2])) {
                $player->sendMessage(self::PREFIX . "§cPlease input level enchant!");
                return false;
            }

            if($args[2] > 30000) {
                $player->sendMessage(self::PREFIX . "§cPlease input level enchant < 30000!");
                return false;
            }

            $enchantment = EnchantmentIdMap::getInstance()->fromId($args[1]);
            if($enchantment === null) {
                $player->sendMessage(self::PREFIX . "§cThis enchant id doesn't exist!");
                return false;
            }
            $toItem = new ItemManager($item);
            $toItem->addEnchant($enchantment, $args[2]);
            $player->getInventory()->setItemInHand($toItem->getItem());
            $player->sendMessage(self::PREFIX . "§aSucces add enchant to item!");
            return true;
        }



        if($args[0] == "scoreboard") {
            if(!isset($args[1])) {
                $player->sendMessage(self::PREFIX . "§Please input true or false!");
                return false;
            }

            if($args[1] == "true") {
                $player->sendMessage(self::PREFIX . "§l§aSuccessfully set scoreboard to true!");
                Heroes::getInstance()->scoreData->set("active", true);
                Heroes::getInstance()->scoreData->save();
                if(Heroes::getInstance()->sb !== null) return true;

                Heroes::getInstance()->sb = System::setInterval(function() {
                    foreach(Heroes::getInstance()->getServer()->getOnlinePlayers() as $player) {
                        Heroes::getInstance()->scoreProvider->create($player, 'Heroes', Heroes::getInstance()->scoreData->get('title'));
                        foreach(Heroes::getInstance()->scoreData->get('scoreboard') as $line => $text) {
                            Heroes::getInstance()->scoreProvider->setLine($player, $line, Heroes::getInstance()->replaceData($player, $text));
                        }
                        Heroes::getInstance()->scoreProvider->update($player);
                    }
                }, Heroes::getInstance()->scoreData->get("update-second") * 1000);
                return true;
            }

            if($args[1] == "false") {
                $player->sendMessage(self::PREFIX . "§l§aSuccessfully set scoreboard to false!");
                Heroes::getInstance()->scoreData->set("active", false);
                Heroes::getInstance()->scoreData->save();
                if(Heroes::getInstance()->sb instanceof SampleMacro) System::clearInterval(Heroes::getInstance()->sb);
                return true;
            }

            if($args[1] == "reload") {
                if(Heroes::getInstance()->scoreData->get("active") !== false) {
                    if(Heroes::getInstance()->sb instanceof SampleMacro) System::clearInterval(Heroes::getInstance()->sb);
                }else{
                    if(Heroes::getInstance()->sb instanceof SampleMacro) System::clearInterval(Heroes::getInstance()->sb);

                    Heroes::getInstance()->sb = System::setInterval(function() {
                        foreach(Heroes::getInstance()->getServer()->getOnlinePlayers() as $player) {
                            Heroes::getInstance()->scoreProvider->create($player, 'Heroes', Heroes::getInstance()->scoreData->get('title'));
                            foreach(Heroes::getInstance()->scoreData->get('scoreboard') as $line => $text) {
                                Heroes::getInstance()->scoreProvider->setLine($player, $line, Heroes::getInstance()->replaceData($player, $text));
                            }
                            Heroes::getInstance()->scoreProvider->update($player);
                        }
                    }, Heroes::getInstance()->scoreData->get("update-second") * 1000);
                }
                return true;
            }
        }
        return false;
    }
}