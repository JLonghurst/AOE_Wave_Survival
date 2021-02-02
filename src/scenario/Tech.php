<?php
class Tech extends PlayerRegion {
    public $WALL_MATERIAL = U_OLD_STONE_HEAD;

    public $relicName; 
    public $costs; 
    public $requirements = []; 
    public $unitId; 
    public $buildingId;
    // nullable, can make a trigger optionally
    public $triggerName;

    public $row = 0;
    public $positionIndex;

    function __construct($relicName, $unitId, $costs) {
        $this->relicName = $relicName;
        $this->unitId = $unitId;
        $this->costs = $costs;
    }

    public function setRequirements($requirements) {
        $this->requirements = $requirements;
    }

    public function setTriggerName($triggerName) {
        $this->triggerName = $triggerName;
    }

    function getNameIndexString($relicName, $index) {
        return "$relicName $index";
    }

    function placeAtLocation(Point $location, $triggerName) {
        // give meaningful relicNames to data array
        // x is offset by 2 on map
        // lan    $Length_Xs are 2
        $blockLocation = $location;
        $relicLocation = $location->offset(-2);
        $unitLocation = $location->offset(-1);
        $killLocation = $location->offset(1);

        $size = count($this->costs);

        // one time event
        $this->trig(uniqid());
            $this->act($this->getNameIndexString($this->relicName, 0));
            $this->createGaia(U_RELIC, $relicLocation);
            $this->createGaia(U_HAY_STACK, $blockLocation);
        foreach($this->costs as $i => $cost) {
            $this->create($this->unitId, $unitLocation);
            Efft_NameO(0, "{$this->relicName} ($cost stone)", $relicLocation->asArr());

            $this->trig($this->getNameIndexString($this->relicName, $i), 0);
                Cond_Timer(2); // debounce the last purchase
                Cond_Accumulate($this->playerId, $cost, R_STONE_STORAGE);
                Cond_InAreaU($this->playerId, 1, $this->unitId, $killLocation->asArr());
                Efft_Tribute($this->playerId, $cost, R_STONE_STORAGE, 0);
                Efft_KillO($this->playerId, $killLocation->asArr());
                $this->chat("<YELLOW> Bought {$this->relicName} for {$cost} stone");
                Efft_Act($triggerName);
            // place down for another round if it exists
            if ($i != $size - 1) 
                $this->act($this->getNameIndexString($this->relicName, $i + 1));
        }
        $this->trig(uniqId());
            /// will place wall over blocked location
            $this->createInArea($this->WALL_MATERIAL, AreaSet($unitLocation->asArr()), 0);
            $this->createInArea($this->WALL_MATERIAL, AreaSet($killLocation->asArr()), 0);
            Efft_RemoveO(0, $killLocation->asArr());
        
        $this->trig(uniqid());
        if (is_array($this->requirements))
            foreach ($this->requirements as $req)
                Cond_Researched($this->playerId, $req);
        Efft_RemoveO(0, $blockLocation->asArr());
    }
}
?>