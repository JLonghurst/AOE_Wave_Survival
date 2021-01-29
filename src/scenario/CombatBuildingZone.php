<?php
class CombatBuildingZone extends PlayerRegion {
    private $DISTANCE = 6;
    function render() {
        parent::render();
        $this->trig("Initial Placement");
            $this->create(U_BARRACKS, $this->getBaseOffsetForObject(U_BARRACKS));
        $this->trig("Feudal Placement");
            Cond_Researched($this->playerId, T_FEUDAL_AGE);
            foreach ([U_ARCHERY_RANGE, U_STABLE] as $buildingId)
                $this->create($buildingId, $this->getBaseOffsetForObject($buildingId));
    }

    public function getBaseOffsetForObject($buildingId) {
        $barracksPoint = $this->origin->offset(-$this->DISTANCE + 1);
        switch($buildingId) {
            case U_STABLE:
                return $barracksPoint->offset(0, $this->DISTANCE);
            case U_ARCHERY_RANGE:
                return $barracksPoint->offset(0, -$this->DISTANCE);
            case U_BARRACKS:
                return $barracksPoint;
        }
    }

    public function placeStoreTriggersAt($storeOrigin) {
        $base = $storeOrigin->offset(-4);
        foreach ($GLOBALS['TECH_DATA'] as $i => $techRaw) {
            $tech = new Tech(
                $techRaw[0],
                $techRaw[1],
                $techRaw[2],
            );
            $tech->setPlayerId($this->playerId);
            $tech->setRequirements($techRaw[3]);
            $flag = 0;
            if (in_array($tech->unitId, [U_LONG_SWORDSMAN, U_CROSSBOWMAN, U_KNIGHT])) {
                $flag = 1;
            } else if (in_array($tech->unitId, [U_CHAMPION, U_ARBALEST, U_PALADIN])) {
                $flag = 2;
            }
            $buildingId = $techRaw[5];
            $origin = $this->getBaseOffsetForObject($buildingId);
            $nameUniq = "$i {$tech->relicName} yeet";
            Trig($nameUniq, 0, 1);
                $this->create($buildingId, $origin->offset(-$flag*$this->DISTANCE));
                foreach ((array)$techRaw[4] as $t) {
                    Efft_Research($this->playerId, $t);
                }
            $tech->placeAtLocation($base->offset(0, -8 + 2*$i), $nameUniq);
        }   
    }
}
?>