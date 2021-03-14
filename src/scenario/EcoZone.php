<?php
class EcoZone extends PlayerRegion {
    private $VIL_TRIGGER_NAME = "Create 5 Vils";

    private $goldOffset = 10;

    function render() {
        parent::render();
        $goldOffset = $this->getCenter()->offset(0, -$this->goldOffset);
        $lumberCamp = $this->getZoneEnd()->offset(6);
        $treeArea = AreaAdvanced(
            $this->getZoneEnd()->asArr(), 
            'S', 
            $this->width, 
            4
        );
        $goldArea = AreaSet($goldOffset->asArr());

        $this->trig("Initial Eco Placement");
            $this->create(U_TOWN_CENTER, $this->getCenter());
            $this->createInArea(U_SHEEP, $this->getCenter()->offset(3)->areaFromOffset(0, 10));
            $this->create(U_MINING_CAMP, $goldOffset->offset(-3));
            $this->create(U_LUMBER_CAMP, $lumberCamp);
            $this->createInArea(U_OLD_STONE_HEAD, $this->createAreaRow(0), 0);
        $this->trig("Gold Placement", 1, 1);
            $this->createInArea(U_GOLD_MINE, $goldArea, 0);
        $this->trig("Tree Placement", 1, 1);
            $this->createInArea(U_TREE_A, $treeArea, 0);
    }

    public function placeStoreTriggersAt($storeOrigin) {
        $vilSpawnArea = AreaAdvanced(
            $this->origin->offset(-5)->asArr(), 
            $this->orientation, 
            15, 
            2
        );

        $createVilTech = new Tech(
            $this->VIL_TRIGGER_NAME,
            U_MONK,
            [100, 200, 400, 800]
        );
        $createVilTech->setPlayerId($this->playerId);
        $createVilTech->setTriggerName($this->VIL_TRIGGER_NAME);
        $vilId = ($i % 2 == 0) ? U_VILLAGER_F : U_VILLAGER_M;

        Trig($this->VIL_TRIGGER_NAME);
            foreach (AreaPts($vilSpawnArea) as $i => $p) {
                $vilId = ($i % 2 == 0) ? U_VILLAGER_F : U_VILLAGER_M;
                $this->create($vilId, Point::fromArr($p));
            }
        
        $createVilTech->placeAtLocation($storeOrigin->offset(-9), $this->VIL_TRIGGER_NAME);
    }
}
?>