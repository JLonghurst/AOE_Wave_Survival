<?php
class PlayerRegion extends PlayerContext {
    public $orientation = 'N';
    public $terrainId;
    /**
     * @var Point $origin: the origin of the areaRegion
     */
    public $origin;
    public $width;

    public $depth;

    /**
     * Creates a new PlayerRegion
     *
     * @param int $playerId - the playerId of the region
     * @param int $terrainId - the terrainId of the region
     * @param int $width - the width of the region
     * @param int $depth - the depth of the region
     */
    function __construct($playerId, $terrainId, $width, $depth) {
        parent::__construct($playerId);
        $this->terrainId = $terrainId;
        $this->width = $width;
        $this->depth = $depth;
    }
    
    public function setOrigin($oPt) {
        $this->origin = $oPt;
    }

    public function setWidth($width) {
        $this->width = $width;
    }

    public function setDepth($depth) {
        $this->depth = $depth;
    }

    public function getArea() {
        return AreaAdvanced(
            $this->origin->asArr(), 
            $this->orientation, 
            $this->width, 
            $this->depth
        );
    }

    public function getAreaWithWidth($width) {
        return AreaAdvanced(
            $this->origin->asArr(),
            $this->orientation, 
            $width, 
            $this->depth
        );
    }

    public function getCenter() {
        return $this->origin->offset(round(-$this->depth / 2) + 1);
    }
    
    public function getZoneEnd() {
        return $this->origin->offset(-$this->depth);
    }

    public function placeStoreTriggersAt($storeOrigin) { }

    public function createAreaRow($offsetX, $origin = null, $width = null) {
        $origin = $origin != null ? $origin : $this->origin;
        $origin = $origin->offset($offsetX);
        $width = $width != null ? $width : $this->width;
        return AreaAdvanced($origin->asArr(), $this->orientation, $width, 1);
    }

    // renders this zone for a player
    public function render() {
        foreach(AreaPts($this->getArea()) as $pt) 
            setCell($pt, $this->terrainId);
    }
}
?>