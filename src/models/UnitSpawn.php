<?php
class UnitSpawn {
    public $unitId;
    public $unitCount;
    public $unitName;
    public UnitStats $unitStats;

    public function __construct($unitId, $unitCount) {
        $this->unitId =  $unitId;
        $this->unitCount =  $unitCount;
        $this->unitName = unitNameById($unitId);
        //print($this->unitName."\n");
        //print($GLOBALS['UNIT_STAT_MAP'][$this->unitName]);
        $this->unitStats = $GLOBALS['UNIT_STAT_MAP']["$this->unitName"];
        //print_r($this->unitStats);
        if ($this->unitStats == null) {
            print("\n no stat for name: {$this->unitName}\n");
        }
    }

    public function getName() {
        return "{$this->unitCount} {$this->unitName}";
    }

    public function getProductionRequirement() {
        return $this->unitStats->createTime * $this->unitCount;
    }
}
?>