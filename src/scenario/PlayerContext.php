<?php
class PlayerContext {
    public $playerId;

    public $DEBUG_CONTEXT = false;

    function __construct($playerId) {
        $this->playerId = $playerId;
    }

    public function setPlayerId($playerId) {
        $this->playerId = $playerId;
    }

    public function getName($objectName) {
        return "$objectName P{$this->playerId}";
    }

    public function trig($triggerName, $S = 1, $L = 0, $P = 0, $E = 0, $D = '', $R = '') {
        $triggerName = $this->getName($triggerName);
        if ($this->DEBUG_CONTEXT) print($triggerName."\n");
        Trig($triggerName, $S, $L, $P, $E, $D, $R);
        return $triggerName;
    }

    public function getEnemyId() {
        return $this->playerId + 1;
    }

    public function act($triggerName) {
        Efft_Act($this->getName($triggerName));
    }

    public function runTrigger($name, $triggerFunction, $delayTime = 0) {
        $triggerName = $this->getName($name);
        if ($this->DEBUG_CONTEXT) print($triggerName."\n");
        Efft_Act($triggerName);
        Trig($triggerName, 0);
            Cond_Timer($delayTime);
            // run the code of the trigger
            $triggerFunction();
    }

    public function chat($text) {
        Efft_Chat($this->playerId, $text);
    }

    /**
     * Create on object for the specified player at the specified location
     *
     * @param integer $objectId the id of the object to create
     * @param Point $pt to location to create it at 
     * @param integer $playerId the if of the player id (defaults to this player)
     * @return void
     */
    public function create($objectId, $pt, $playerId = NULL) {
        if (!$playerId) $playerId = $this->playerId;
        Efft_Create($playerId, $objectId, $pt->asArr());
        setCell($pt->asArr(), TERRAIN_SNOW_DIRT_BUILDING_RESIDUE);
    }

    /**
     * Create an object for gaia
     *
     * @param integer $objectId the id of the object to create
     * @param Point $pt to location to create it at 
     * @return void
     */
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