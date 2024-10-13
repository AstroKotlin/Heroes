<?php

declare(strict_types=1);

namespace AstroKotlin\Heroes\manager;

use AstroKotlin\Heroes\Heroes;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ListTag;

class ItemManager {

    public function __construct(private Item $item) {
        if($this->getItem()->getNamedTag()->getTag('properties') === null) $this->getItem()->getNamedTag()->setTag('properties', new ListTag([]));
    }

    public function getItem(): Item {
        return $this->item;
    }

    public function getItemName(): string {
        return $this->item->getName();
    }

    public function getItemType(): string {
        return $this->item->getType();
    }

    public function getProperties(): array {
        return $this->getItem()->getNamedTag()->getListTag("properties")->getValue();
    }

    public function addProperties(string $propName, int $level): void {
        $item = $this->getItem();

        $item->getNamedTag()->getListTag('properties')->push(new ListTag([$propName, $level]));

        $this->item = $item;
    }

    public function getPropertiesName() {
        $prop = '';

        foreach($this->getProperties() as $prop) {
            $listTag = $prop->getValue();

            $format = Heroes::getInstance()->getProp($listTag[0]);

            $text = str_replace(['lv', 'level'], [$listTag[1], $listTag[1]], $format);

            $prop .= $text.'\n';
        }

        return $prop;
    }

    public function setLore(string $text): self {
        $item = $this->getItem();

        $item->getNamedTag()->setString("Lore", str_replace(['{l}', '{line}'], ['\n', '\n'], $text));

        $item->setLore(lines: [str_replace(['{l}', '{line}', '{text}', 'properties'], replace: ['\n', '\n', $text, $this->getPropertiesName()], subject: Heroes::getInstance()->getLoreFormat())]);

        $this->item = $item;

        return $this;
    }

    public function addEnchant(Enchantment $enchant, int $level): self {
        $item = $this->getItem();

        $enchInstance = new EnchantmentInstance($enchant, $level);
        $item->addEnchantment($enchInstance);

        $this->item = $item;

        return $this;
    }
}