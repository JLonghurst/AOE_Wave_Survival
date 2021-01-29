<?php
class PlayerContext {
    public $playerId;

    function __construct($playerId) {
        $this->playerId = $playerId;
    }

    public function setPlayerId($playerId) {
        $this->playerId = $playerId;
    }

    public function getName($objectName) {
        return "{$this->playerId}: $objectName";
    }

    public function trig($triggerName, $S = 1, $L = 0, $P = 0, $E = 0, $D = '', $R = '') {
        $triggerName = $this->getName($triggerName);
        Trig($triggerName, $S, $L, $P, $E, $D, $R);
        return $triggerName;
    }

    public function getEnemyId() {
        return $this->playerId + 1;
    }

    public function act($triggerName) {
        Efft_Act($this->getName($triggerName));
    }

    public function chat($text) {
        Efft_Chat($this->playerId, $text);
    }

    // pt is of type pt
    public function create($objectId, $pt, $playerId = NULL) {
        if (!$playerId) $playerId = $this->playerId;
        Efft_Create($playerId, $objectId, $pt->asArr());
        setCell($pt->asArr(), TERRAIN_SNOW_DIRT_BUILDING_RESIDUE);
    }

    // pt is of type pt
    public function createGaia($objectId, $pt) {
        Efft_Create(0, $objectId, $pt->asArr());
        setCell($pt->asArr(), TERRAIN_SNOW_DIRT_BUILDING_RESIDUE);
    }

    function createInArea($objectId, $area, $playerId = NULL) {
        foreach (AreaPts($area) as $pt) 
            if ($playerId === 0) 
                $this->createGaia($objectId, new Point($pt[0], $pt[1]));
            else 
                $this->create($objectId, new Point($pt[0], $pt[1]), $playerId);
    }
}
?>