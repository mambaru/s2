<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 29.06.2018
 * Time: 12:48
 */

namespace S2\S2EdgeUtil;

use S2\S2Point;

/**
 * A wedge relation's test method accepts two edge chains A=(a0,a1,a2) and
 * B=(b0,b1,b2) where a1==b1, and returns either -1, 0, or 1 to indicate the
 * relationship between the region to the left of A and the region to the left
 * of B. Wedge relations are used to determine the local relationship between
 * two polygons that share a common vertex.
 *
 *  All wedge relations require that a0 != a2 and b0 != b2. Other degenerate
 * cases (such as a0 == b2) are handled as expected. The parameter "ab1"
 * denotes the common vertex a1 == b1.
 */
interface WedgeRelation
{
    function test(S2Point $a0, S2Point $ab1, S2Point $a2, S2Point $b0, S2Point $b2);
}