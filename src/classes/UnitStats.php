<?php
class UnitStats {
    private $FOOD_GATHER_RATE = 0.33;
    private $GOLD_GATHER_RATE = 0.359;
    private $WOOD_GATHER_RATE = 0.388;

    public $foodCount;
    public $woodCount;
    public $goldCount;

    /**
     * Creates a new unit statstic
     *
     * @param integer $foodCount the ammount of food the unit costs
     * @param integer $woodCount the ammount of wood the unit costs
     * @param integer $goldCount the ammount of gold the unit costs
     */
    function __construct($foodCount, $woodCount, $goldCount = 0) {
        $this->foodCount = $foodCount;
        $this->woodCount = $woodCount;
        $this->goldCount = $goldCount;
    }

    /**
     * returns a float representing the ammount of villager
     * time is require to create the unit
     *
     * @return void
     */
    function getComputedVillagerTime() {
        return $this->foodCount * $FOOD_GATHER_RATE 
            + $this->woodCount * $WOOD_GATHER_RATE 
            + $this->goldCount * $GOLD_GATHER_RATE;
    }
}
?>