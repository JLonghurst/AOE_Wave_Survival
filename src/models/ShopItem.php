<?php
class ShopItem {
    public string $name;
    public integer $shopUnitId;
    public integer $stoneCost;
    public array $technologyRequirements;
    public array $researchEffects;

    /**
     * Creates a new PlayerRegion
     *
     * @param int $playerId - the playerId of the region
     * @param int $terrainId - the terrainId of the region
     * @param int $width - the width of the region
     * @param int $depth - the depth of the region
     */
    function __construct($terrainId, $width, $depth) {
        $this->terrainId = $terrainId;
        $this->width = $width;
        $this->depth = $depth;
    }
}
?>