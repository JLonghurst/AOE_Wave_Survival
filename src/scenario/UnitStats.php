<?php
class UnitStats {
    // units: food / vil*sec
    private $FOOD_GATHER_RATE = 0.33;
    private $GOLD_GATHER_RATE = 0.359;
    private $WOOD_GATHER_RATE = 0.388;

    public $foodCount;
    public $woodCount;
    public $goldCount;
    public $createTime;
    public $unitName;

    /**
     * Creates a new unit statstic
     *
     * @param float $foodCount the ammount of food the unit costs
     * @param float $woodCount the ammount of wood the unit costs
     * @param float $goldCount the ammount of gold the unit costs
     */
    function __construct(
        $foodCount, 
        $woodCount = 0, 
        $goldCount = 0, 
        $createTime
    ) {
        $this->foodCount = $foodCount;
        $this->woodCount = $woodCount;
        $this->goldCount = $goldCount;
        $this->createTime = $createTime;
    }

    /**
     * returns a float representing the ammount of villager
     * time is require to create the unit
     *
     * @return float units: vil/sec
     */
    function getComputedVillagerTime() {
        return $this->foodCount * $this->FOOD_GATHER_RATE 
            + $this->woodCount * $this->WOOD_GATHER_RATE 
            + $this->goldCount * $this->GOLD_GATHER_RATE;
    }

    public function __debugInfo()
    {
        return json_decode(json_encode($this), true);
    }
}
?>