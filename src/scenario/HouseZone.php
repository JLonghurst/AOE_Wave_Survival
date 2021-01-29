<?php
class HouseZone extends PlayerRegion {
    function render() {
        parent::render();
        $this->trig("House Place");
            $this->createInArea(U_HOUSE, $this->getArea());
    }
}
?>