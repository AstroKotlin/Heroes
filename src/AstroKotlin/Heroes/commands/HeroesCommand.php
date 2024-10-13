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
            $player->sendMessage(self::PREFIX . '§b Command Help:\n§e- /heroes <listenchant|le> - list all enchant id\n§e- /heroes <setname|name> - set name for item\n§e- /heroes <enchant|ec> - add enchant to item\n§e- /heroes scoreboard <on|off> - turn on|off scoreboard\n§e- /heroes prop set - set properties for item\n§e- /heroes prop add - add properties for item\n§e- /heroes prop list - list properties for item');
            return true;
        }


        if($args[0] == "setname") {
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


        if($args[0] == "enchant" or $args[0] == "ec") {
            if(!isset($args[1])) {
                $player->sendMessage(self::PREFIX . "§Please input id enchant for enchant item!");
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
        return false;
    }
}