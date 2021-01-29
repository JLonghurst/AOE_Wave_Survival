<?php
class Point {
    public $x;
    public $y;

    function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

    function equals($o) {
        if ($this->x == $o->x && $this->y == $o->y) {
            return 1;
        } else {
            return -1;
        }
    }

    function compare($o) {
        return $this->y - $o->y;
    }
    
    function offset($dx, $dy = 0) {
        return new Point($this->x + $dx, $this->y + $dy);
    }

    function asArr() {
        return array($this->x, $this->y);
    }

    function asLoc() {
        return [$this->asArr()];
    }

    public static function fromArr($arr) {
        return new Point($arr[0], $arr[1]);
    }

    /**
     * Returns the area created by the bounding box
     * of the two points
     *
     * @param Point $p2
     * @return Area
     */
    public function areaFromP2($p2) {
        return Area($this->x, $this->y, $p2->x, $p2->y);
    }
    
    public function areaFromOffset($dx, $dy = 0) {
        return $this->areaFromP2($this->offset($dx, $dy));
    }
}
?>