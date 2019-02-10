<?php
namespace GapCD;
use pocketmine\entity\projectile\EnchantedGapple;
use pocketmine\event\entity\ProjectileLaunchEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\item\Item;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
class Plugin extends PluginBase implements Listener
{
	private $coolDown = 60;
	private $timer = [];
	public function onEnable()
	{
		@mkdir($this->getDataFolder());
		$this->saveDefaultConfig();
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->coolDown = $this->getConfig()->get("cooldown-timer");
	}
	public function onLaunch(ProjectileLaunchEvent $event)
	{
		$thrower = $event->getEntity()->getOwningEntity();
		if ($thrower instanceof Player) {
			if ($event->getEntity() instanceof EnchantedGapple) {
				$name = strtolower($thrower->getDisplayName());
				if (!isset($this->timer[$name]) or time() > $this->timer[$name]) {
					$this->timer[$name] = time() + $this->coolDown;
				} else {
					$thrower->sendMessage($this->getConfig()->get("cooldown-message") . " " . strval($this->timer[$name] - time()) . " seconds remain");
					$event->setCancelled();
				}
			}
			if ($event->isCancelled()) {
				$this->needToBeGivenEnchantedGapple[$thrower->getName()] = $thrower->getName();
				return;
			}
		}
	}
	public function onMove(PlayerMoveEvent $event): void
	{
		{
			$player = $event->getPlayer();
			if ($player instanceof Player) {
				if (isset($this->needToBeGivenEnchantedGapple[$player->getName()])) {
					$player->getInventory()->addItem(Item::get(466));
					unset($this->needToBeGivenEnchantedGapple[$player->getName()]);
				}
			}
		}
	}
	public function onInteract(PlayerInteractEvent $event): void
	{
		{
			$player = $event->getPlayer();
			if ($player instanceof Player) {
				if (isset($this->needToBeGivenEnchantedGapple[$player->getName()])) {
					if (time() + intval($this->coolDown) - time() <= 15) {
						$player->getInventory()->addItem(Item::get(466));
						unset($this->needToBeGivenEnchantedGapple[$player->getName()]);
					}
				}
			}
		}
	}
}
