<?php

declare(strict_types=1);

namespace JoinUI;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

class Main extends PluginBase implements Listener {

    public function onEnable(): void {
        $this->saveDefaultConfig();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event): void {
        $player = $event->getPlayer();

        if(!$player->hasPlayedBefore()){
            $player->sendMessage($this->color($this->getConfig()->get("first-join-message")));
            $this->sendForm($player);
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {

        if($command->getName() === "howtooplay"){

            if(!$sender instanceof Player){
                $sender->sendMessage("Run this command in-game.");
                return true;
            }

            $sender->sendMessage($this->color($this->getConfig()->get("command-open-message")));
            $this->sendForm($sender);
        }

        return true;
    }

    public function sendForm(Player $player): void {

        $formAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        if($formAPI === null){
            $player->sendMessage("FormAPI is required.");
            return;
        }

        $form = $formAPI->createSimpleForm(function(Player $player, $data){
            if($data === null){
                return;
            }

            $player->sendMessage($this->color($this->getConfig()->get("close-message")));
        });

        $title = $this->color($this->getConfig()->get("title"));
        $text = $this->color($this->getConfig()->get("text"));
        $button = $this->color($this->getConfig()->get("close-button"));

        $form->setTitle($title);
        $form->setContent($text);
        $form->addButton($button);

        $player->sendForm($form);
    }

    public function color(string $text): string {
        return str_replace("&", "§", $text);
    }
}
