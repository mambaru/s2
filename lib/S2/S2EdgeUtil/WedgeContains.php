<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 29.06.2018
 * Time: 12:49
 */

namespace S2\S2EdgeUtil;

use S2\S2;
use S2\S2Point;

class WedgeContains implements WedgeRelation
{
    /**
     * Given two edge chains (see WedgeRelation above), this function returns +1
     * if the region to the left of A contains the region to the left of B, and
     * 0 otherwise.
     */

    public function test(S2Point $a0, S2Point $ab1, S2Point $a2, S2Point $b0, S2Point $b2) {
        // For A to contain B (where each loop interior is defined to be its left
        // side), the CCW edge order around ab1 must be a2 b2 b0 a0. We split
        // this test into two parts that test three vertices each.
        return S2::orderedCCW($a2, $b2, $b0, $ab1) && S2::orderedCCW($b0, $a0, $a2, $ab1) ? 1 : 0;
    }
}