<?php
class EcoZone extends PlayerRegion {
    private $VIL_TRIGGER_NAME = "Create 5 Vils";

    private $goldOffset = 10;

    function render() {
        parent::render();
        $goldOffset = $this->getCenter()->offset(0, -$this->goldOffset);
        $lumberCamp = $this->getZoneEnd()->offset(4);
        $treeArea = $this->createAreaRow(1, $this->getZoneEnd());
        $goldArea = AreaSet($goldOffset->asArr());
        $this->trig("Initial Eco Placement");
            $this->create(U_TOWN_CENTER, $this->getCenter());
            $this->createInArea(U_SHEEP, $this->getCenter()->offset(3)->areaFromOffset(0, 6));
            $this->create(U_MINING_CAMP, $goldOffset->offset(-3));
            $this->create(U_LUMBER_CAMP, $lumberCamp);
            $this->createInArea(U_OLD_STONE_HEAD, $this->createAreaRow(0), 0);
        $this->trig("Gold Placement", 1, 1);
            $this->createInArea(U_GOLD_MINE, $goldArea, 0);
        $this->trig("Tree Placement", 1, 1);
            $this->createInArea(U_TREE_A, $treeArea, 0);
    }

    public function placeStoreTriggersAt($storeOrigin) {
        $vilSpawnArea = $this->createAreaRow(1, $this->origin->offset(-3), 5);
        $createVilTech = new Tech(
            $this->VIL_TRIGGER_NAME,
            U_MONK,
            [100, 200, 400, 800]
        );
        $createVilTech->setPlayerId($this->playerId);
        $createVilTech->setTriggerName($this->VIL_TRIGGER_NAME);
        Trig($this->VIL_TRIGGER_NAME);
            foreach (AreaPts($vilSpawnArea) as $i => $p)
                $this->create(($i % 2 == 0) ? U_VILLAGER_F : U_VILLAGER_M, Point::fromArr($p));
        $createVilTech->placeAtLocation($storeOrigin->offset(-9), $this->VIL_TRIGGER_NAME);
    }
}
?>