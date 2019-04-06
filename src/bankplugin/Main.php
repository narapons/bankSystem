<?php

namespace bankplugin;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\entity\Attribute;
use pocketmine\utils\Config;

class Main extends PluginBase implements Listener{

	public function onEnable(){
		$PluginName = "bankSystem";
		$version = "1.0.0";
		$this->getlogger()->info($PluginName." v".$version."を読み込みました。作者:masaiwasa");
    	$this->getlogger()->warning("製作者偽りと二次配布、改造、改造配布は禁止します");

    	#システム
    	$this->getServer()->getPluginManager()->registerEvents($this,$this);
    	if($this->getServer()->getPluginManager()->getPlugin("EconomyAPI") != null){
			$this->EconomyAPI = $this->getServer()->getPluginManager()->getPlugin("EconomyAPI");
			$this->getLogger()->notice("EconomyAPIを検出しました。");
		}else{
			$this->getLogger()->error("EconomyAPIが見つかりませんでした");
			$this->getServer()->getPluginManager()->disablePlugin($this);
		}
		if(!file_exists($this->getDataFolder())){
          mkdir($this->getDataFolder(), 0756, true);
       }
       $this->money = new Config($this->getDataFolder() . "bankmoney.yml", Config::YAML);
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		  switch ($command->getName()) {//コマンド名で条件分岐
		    case "bank":
						if($sender instanceof Player) {
		        if(!isset($args[0])){
							$sender->sendMessage("§b[銀行]>>>§f/bank dp <金額> §a銀行にお金を預けます");
							$sender->sendMessage("§b[銀行]>>>§fbank wp <金額> §a銀行からお金を引き出します");
							$sender->sendMessage("§b[銀行]>>>§f/bank info §a銀行に預けてる金額を確認します");
							$name = $sender->getName();
							$money = $this->EconomyAPI->myMoney($name);
		        }else{
		        switch($args[0]){
		          case "dp":
							$name = $sender->getName();
							$money = $this->EconomyAPI->myMoney($name);
							if(isset($args[1])){
								$price = intval($args[1]);
								if($money >= $price){
									$this->addMoneyBank($name, $price);
									$newmoney = $this->nowMoneyBank($name);
									$this->EconomyAPI->reduceMoney($name, $price);
									$sender->sendMessage("§b[銀行]>>>§b$".$price."§f預けました。");
									return true;
								}else{
									$sender->sendMessage("§b[銀行]>>>§c所持金が足りません。");
									return true;
								}
							}else{
								$sender->sendMessage("§b[銀行]>>>§c預ける金額を入力して下さい。");
								return true;
							}
		          break;

		          case "wp":
							$name = $sender->getName();
							$money = $this->EconomyAPI->myMoney($name);
							if(isset($args[1])){
								$price = intval($args[1]);
								if($this->removeMoneyBank($name, $price)){
									$newmoney = $this->nowMoneyBank($name);
									$this->EconomyAPI->addMoney($name, $price);
									$sender->sendMessage("§b[銀行]>>>§b$".$price."§f引き出しました。");
									return true;
								}else{
									$sender->sendMessage("§b[銀行]>>>§c預金額が足りません。");
									return true;
								}
							}else{
								$sender->sendMessage("§b[銀行]>>>§c引き出す金額を入力して下さい。");
								return true;
							}
		          break;

							case "info":
							$name = $sender->getName();
							$money = $this->EconomyAPI->myMoney($name);
							$name = $sender->getName();
							$nowmoney = $this->nowMoneyBank($name);
							$sender->sendMessage("§b[銀行]>>>".$name."§f様の銀行口座情報");
							$sender->sendMessage("§b[銀行]>>>§fただいまの預金額は§b$".$nowmoney."§fです。");
							return true;
							break;


									default:
										$sender->sendMessage("§b[銀行]>>>§f/bank dp <金額> §a銀行に$<金額>預けます");
										$sender->sendMessage("§b[銀行]>>>§fbank wp <金額> §a銀行から$<金額>引き出します");
										$sender->sendMessage("§b[銀行]>>>§f/bank info §a銀行に預けてる金額を確認します");
										return true;
										break;
					}
				}
			}
			}
			return true;
		}



	public function addMoneyBank($name, $price){
		$price = intval($price);
		$nowmoney = intval($this->money->get($name));
		$newmoney = $nowmoney + $price;
		$this->money->set($name, $newmoney);
		$this->money->save();
		return true;
	}

	public function removeMoneyBank($name, $price){
		$price = intval($price);
		$nowmoney = intval($this->money->get($name));
		if($nowmoney >= $price){
			$newmoney = $nowmoney - $price;
			$this->money->set($name, $newmoney);
			$this->money->save();
			return true;
		}else{
			return false;
		}
	}

	public function nowMoneyBank($name){
		$nowmoney = intval($this->money->get($name));;
		return $nowmoney;
	}

	public function setMoneyBank($name, $price){
		$price = intval($price);
		$this->money->set($name, $price);
		$this->money->save();
		return true;
	}
}
