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
use jojoe77777\FormAPI\CustomForm;

class Main extends PluginBase implements Listener {

    private $reportQueue = [];
    private $messages = [];
    private $exemptPlayers = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->saveDefaultConfig();
        $this->loadConfiguration();
        $this->loadReportsFromFile();
    }

    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();
        $block = $event->getBlock();
        $world = $block->getPosition()->getWorld();
        $targetPlayer = $world->getNearestEntity($block->getPosition(), 10, Player::class);

        if ($targetPlayer instanceof Player) {
            $this->sendReportForm($player, $targetPlayer);
        }
    }

    public function sendReportForm(Player $player, Player $targetPlayer) {
        if (in_array($targetPlayer->getName(), $this->exemptPlayers)) {
            $player->sendMessage(TextFormat::RED . "You cannot report this player.");
            return;
        }

        $form = new CustomForm(function (Player $player, ?array $data) use ($targetPlayer) {
            if ($data !== null) {
                // Handle form response, e.g., add report to queue
                $this->addToReportQueue($player, $targetPlayer, $data[1]);
            }
        });

        $form->setTitle("Report Player");
        $form->addLabel("You are reporting " . $targetPlayer->getName() . ". Please provide details below.");
        $form->addInput("Additional details (optional):");
        $player->sendForm($form);
    }

    private function addToReportQueue(Player $reporter, Player $reportedPlayer, string $details) {
        $this->reportQueue[] = [
            'reporter' => $reporter->getName(),
            'reportedPlayer' => $reportedPlayer->getName(),
            'details' => $details,
        ];
        $reporter->sendMessage(TextFormat::GREEN . $this->messages['reportSuccess']);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if ($command->getName() === "report") {
            if ($sender instanceof Player) {
                if (count($args) === 1) {
                    $reportedPlayer = $this->getServer()->getPlayerExact($args[0]);
                    if ($reportedPlayer !== null && $reportedPlayer->isOnline()) {
                        // Show report UI
                        $this->sendReportForm($sender, $reportedPlayer);
                    } else {
                        $sender->sendMessage(TextFormat::RED . $this->messages['playerNotFound']);
                    }
                } else {
                    $sender->sendMessage(TextFormat::RED . $this->messages['usageReport']);
                }
            } else {
                $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
            }
            return true;
        } elseif ($command->getName() === "reportlist") {
            if ($sender instanceof Player && $sender->hasPermission("reportplayer.viewlist")) {
                $sender->sendMessage($this->messages['reportListHeader']);
                if (empty($this->reportQueue)) {
                    $sender->sendMessage(TextFormat::YELLOW . $this->messages['noReports']);
                } else {
                    foreach ($this->reportQueue as $index => $report) {
                        $sender->sendMessage(($index + 1) . ". " . $report['reporter'] . " reported " . $report['reportedPlayer'] . ": " . $report['details']);
                    }
                }
            } else {
                $sender->sendMessage(TextFormat::RED . $this->messages['noPermission']);
            }
            return true;
        }
        return false;
    }

    // New methods start here

    private function loadConfiguration() {
        $config = yaml_parse_file($this->getDataFolder() . "playerreport.yml");
        $this->messages = $config['messages'] ?? [];
        $this->exemptPlayers = $config['exemptPlayers'] ?? [];
    }

    public function processReports() {
        foreach ($this->reportQueue as $report) {
            $reporter = $this->getServer()->getPlayerExact($report['reporter']);
            $reportedPlayer = $this->getServer()->getPlayerExact($report['reportedPlayer']);

            if ($reportedPlayer !== null && $reporter !== null) {
                $reportedPlayer->sendMessage(TextFormat::RED . "You have been reported by " . $reporter->getName() . ".");
                $reporter->sendMessage(TextFormat::GREEN . "Your report against " . $reportedPlayer->getName() . " has been processed.");
            }
        }
    }

    public function clearReportQueue() {
        $this->reportQueue = [];
        $this->getServer()->broadcastMessage(TextFormat::YELLOW . $this->messages['allReportsCleared']);
    }

    public function saveReportsToFile() {
        $filePath = $this->getDataFolder() . ($this->messages['reportFileName'] ?? "reports.json");
        file_put_contents($filePath, json_encode($this->reportQueue));
        $this->getLogger()->info("Reports have been saved to file.");
    }

    public function loadReportsFromFile() {
        $filePath = $this->getDataFolder() . ($this->messages['reportFileName'] ?? "reports.json");
        if (file_exists($filePath)) {
            $this->reportQueue = json_decode(file_get_contents($filePath), true);
            $this->getLogger()->info("Reports have been loaded from file.");
        } else {
            $this->getLogger()->warning("No report file found to load.");
        }
    }

    public function getReportCount() {
        return count($this->reportQueue);
    }

    public function onDisable(): void {
        if ($this->messages['saveReportsToFile'] ?? true) {
            $this->saveReportsToFile();
        }
    }
}
