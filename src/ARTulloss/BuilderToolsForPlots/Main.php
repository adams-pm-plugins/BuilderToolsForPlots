<?php

declare(strict_types=1);

namespace ARTulloss\BuilderToolsForPlots;

use function count;
use czechpmdevs\buildertools\event\BuilderToolsEvent;
use czechpmdevs\buildertools\utils\Math;
use MyPlot\MyPlot;
use pocketmine\block\Block;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;

class Main extends PluginBase implements Listener {

	public function onEnable(): void{
	    $this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onBuilderTools(BuilderToolsEvent $event): void{
	    $player = $event->getPlayer();
        if ($player->hasPermission("myplot.admin.build")) return;
	    $pos1 = $event->getPos1();
	    $pos2 = $event->getPos2();
	    $myplot = MyPlot::getInstance();
	    $blocks = $this->getBlocks($player->getLevel(), $pos1, $pos2);
	    $max = $this->getConfig()->get('Max blocks');
	    if(count($blocks) > $max) {
	        $event->setCancelled();
	        $player->sendMessage(TextFormat::RED . "You have exceeded the max blocks of $max");
	        return;
        }
	    foreach ($blocks as $block) {
	        $plot = $myplot->getPlotByPosition($block);
	        if($plot !== null && ($player->hasPermission("myplot.admin.build.plot") || $plot->owner === $player->getName() || $plot->isHelper($player->getName()))) {
	            continue;
            }
            $event->setCancelled();
            $player->sendMessage(TextFormat::RED . "You don't have permission to use this command here!");
            return;
        }
    }

    /**
     * Return blocks in between two vertexes
     * @param Level $level
     * @param Vector3 $pos1
     * @param Vector3 $pos2
     * @return Block[]
     */
    public function getBlocks(Level $level, Vector3 $pos1, Vector3 $pos2): array {
        $x1 = $pos1->getX();
        $y1 = $pos1->getY();
        $z1 = $pos1->getZ();
        $x2 = $pos2->getX();
        $y2 = $pos2->getY();
        $z2 = $pos2->getZ();
        $array = [];
        for($x = min($x1, $x2); $x <= max($x1, $x2); $x++) {
            for ($y = min($y1, $y2); $y <= max($y1, $y2); $y++) {
                for ($z = min($z1, $z2); $z <= max($z1, $z2); $z++) {
                    $vec = Math::roundVector3(new Vector3($x, $y, $z));
                    $array[] = $level->getBlock($vec);
                }
            }
        }
        return $array;
    }

}
