<?php

namespace S2;

class S2PhpUtils {

    /**
     * Replacement for unsigned right shift operator >>>.
     *
     * @param $a
     * @param $b
     * @return int
     */
    public static function unsignedRightShift($a, $b) {
        if($b == 0) return $a;
        return ($a >> $b) & ~(1<<(8*PHP_INT_SIZE-1)>>($b-1));
    }
}