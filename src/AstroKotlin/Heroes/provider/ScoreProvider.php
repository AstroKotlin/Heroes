<?php
declare(strict_types=1);

namespace AstroKotlin\Heroes\provider;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\player\Player;
use pocketmine\utils\SingletonTrait;

final class ScoreProvider {
    use SingletonTrait;

    public const MIN_SCORE = 0;
    public const MAX_SCORE = 14;

    protected array $scoreboards = [];
    protected array $change_entries = [];
    protected array $remove_entries = [];

    public function create(Player $player, string $objectiveName, string $displayName) : self{
        if (isset($this->scoreboards[$player->getName()])) {
            $this->remove($player);
        }
        $player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, $objectiveName, $displayName, "dummy", SetDisplayObjectivePacket::SORT_ORDER_ASCENDING));
        $this->scoreboards[$player->getName()] = $objectiveName;
        return $this;
    }

    public function getObjectiveName(Player $player) : ?string{
        return $this->scoreboards[$player->getName()] ?? null;
    }

    public function setDisplayName(Player $player, string $displayName) : self{
        if (!isset($this->scoreboards[$player->getName()])) {
            return $this;
        }
        $player->getNetworkSession()->sendDataPacket(SetDisplayObjectivePacket::create(SetDisplayObjectivePacket::DISPLAY_SLOT_SIDEBAR, $this->scoreboards[$player->getName()], $displayName, "dummy", SetDisplayObjectivePacket::SORT_ORDER_ASCENDING));
        return $this;
    }

    public function remove(Player $player) : void{
        if (!isset($this->scoreboards[$player->getName()])) {
            return;
        }
        $player->getNetworkSession()->sendDataPacket(RemoveObjectivePacket::create($this->scoreboards[$player->getName()]));
        $this->clearPlayerCache($player);
    }

    public function setLine(PLayer $player, int $line, string $context) : self{
        if ($line < self::MIN_SCORE || $line > self::MAX_SCORE || !isset($this->scoreboards[$player->getName()])) {
            return $this;
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->scoreboards[$player->getName()];
        $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;
        $entry->customName = $context . str_repeat("\0", $line);
        $entry->score = $line;
        $entry->scoreboardId = $line;

        $this->change_entries[$player->getName()][] = $entry;
        return $this;
    }

    public function removeLine(PLayer $player, int $line, bool $brutal = false) : self{
        if (!$brutal) {
            $this->setLine($player, $line, "");
            return $this;
        }
        if ($line < self::MIN_SCORE || $line > self::MAX_SCORE || !isset($this->scoreboards[$player->getName()])) {
            return $this;
        }
        $entry = new ScorePacketEntry();
        $entry->objectiveName = $this->getObjectiveName($player);
        $entry->score = $line;
        $entry->scoreboardId = $line;

        $this->remove_entries[$player->getName()][] = $entry;
        return $this;
    }

    public function floodLine(Player $player, int $start = self::MIN_SCORE, int $end = self::MAX_SCORE, string $flood = "") : self{
        while ($start <= $end) {
            $this->setLine($player, $start++, $flood);
        }

        return $this;
    }

    public function update(Player $player) : self{
        if (!empty($this->change_entries[$player->getName()])) {
            $player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_CHANGE, $this->change_entries[$player->getName()]));

        }
        if (!empty($this->remove_entries[$player->getName()])) {
            $player->getNetworkSession()->sendDataPacket(SetScorePacket::create(SetScorePacket::TYPE_REMOVE, $this->remove_entries[$player->getName()]));
        }
        unset($this->change_entries[$player->getName()], $this->change_entries[$player->getName()]);
        return $this;
    }

    public function clearPlayerCache(Player $player) : void{
        unset($this->scoreboards[$player->getName()], $this->change_entries[$player->getName()], $this->remove_entries[$player->getName()]);
    }

    public function clearCache() : void{
        unset($this->scoreboards, $this->change_entries, $this->remove_entries);
    }
}