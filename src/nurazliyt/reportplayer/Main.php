<?php

namespace nurazliyt\reportplayer;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\form\Form;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class Main extends PluginBase implements Listener {

    private $reportQueue = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $targetPlayer = $event->getBlock()->getLevel()->getBlock($event->getBlock())->getLevel()->getHighestBlockAt($event->getBlock());

        $this->sendReportForm($player, $targetPlayer);
    }

    public function sendReportForm(Player $player, Player $targetPlayer) {
        $form = $this->getServer()->getPluginManager()->getPlugin("FormAPI")->createCustomForm(function (Player $player, array $data = null) use ($targetPlayer) {
            if ($data !== null) {
                // Handle form response, e.g., add report to queue
                $this->addToReportQueue($player, $targetPlayer, $data[1]);
            }
        });

        $form->setTitle("Report Player");
        $form->addLabel("You are reporting " . $targetPlayer->getName() . ". Please provide details below.");

        // Add a text input for additional details
        $form->addInput("Additional details (optional):");

        $form->sendToPlayer($player);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "report") {
            if ($sender instanceof Player) {
                if (count($args) === 1) {
                    $reportedPlayer = $this->getServer()->getPlayer($args[0]);
                    if ($reportedPlayer !== null && $reportedPlayer->isOnline()) {
                        // Implement logic to show report UI
                        $this->sendReportForm($sender, $reportedPlayer);
                    } else {
                        $sender->sendMessage(TextFormat::RED . "Player not found or not online.");
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . "Usage: /report <player>");
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            }
            return true;
        } elseif ($command->getName() === "reportlist") {
            // Display a list of pending reports to admins
            $sender->sendMessage("Pending Reports:");
            foreach ($this->reportQueue as $index => $report) {
                $sender->sendMessage(($index + 1) . ". " . $report['reporter'] . " reported " . $report['reportedPlayer'] . ": " . $report['details']);
            }
            return true;
        }
        return false;
    }
}
