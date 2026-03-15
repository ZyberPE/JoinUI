<?php

declare(strict_types=1);

namespace JoinUI;

use pocketmine\plugin\PluginBase;
use pocketmine\player\Player;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

    private Config $data;

    public function onEnable() : void{
        $this->saveDefaultConfig();

        @mkdir($this->getDataFolder());
        $this->data = new Config($this->getDataFolder() . "data.yml", Config::YAML, [
            "players" => []
        ]);

        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function onJoin(PlayerJoinEvent $event) : void{
        $player = $event->getPlayer();
        $name = strtolower($player->getName());

        $players = $this->data->get("players");
        $version = $this->getConfig()->get("guide-version");

        if(!isset($players[$name]) || $players[$name] < $version){

            $player->sendMessage($this->color($this->getConfig()->getNested("messages.first-join")));
            $this->openGuide($player);

            $players[$name] = $version;
            $this->data->set("players", $players);
            $this->data->save();
        }
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{

        if($command->getName() === "howtooplay"){

            if(!$sender instanceof Player){
                $sender->sendMessage("Use this command in-game.");
                return true;
            }

            $sender->sendMessage($this->color($this->getConfig()->getNested("messages.command-open")));
            $this->openGuide($sender);
        }

        return true;
    }

    private function openGuide(Player $player) : void{

        $formAPI = $this->getServer()->getPluginManager()->getPlugin("FormAPI");

        if($formAPI === null){
            $player->sendMessage("FormAPI plugin is required.");
            return;
        }

        $form = $formAPI->createSimpleForm(function(Player $player, $data){
            if($data === null){
                return;
            }

            $player->sendMessage($this->color($this->getConfig()->getNested("messages.reopen")));
        });

        $form->setTitle($this->color($this->getConfig()->getNested("form.title")));
        $form->setContent($this->color($this->getConfig()->getNested("form.content")));
        $form->addButton($this->color($this->getConfig()->getNested("form.close-button")));

        $player->sendForm($form);
    }

    private function color(string $text) : string{
        return str_replace("&", "§", $text);
    }
}
