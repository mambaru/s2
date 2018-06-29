<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 29.06.2018
 * Time: 13:52
 */

namespace S2\S2EdgeUtil;

use S2\S2;
use S2\S2Point;

/**
 * This class allows a vertex chain v0, v1, v2, ... to be efficiently tested
 * for intersection with a given fixed edge AB.
 */
class EdgeCrosser
{
    // The fields below are all constant.

    private $a;

    private $b;

    private $aCrossB;

    // The fields below are updated for each vertex in the chain.

    // Previous vertex in the vertex chain.
    /**
     * @var S2Point
     */
    private $c;

    // The orientation of the triangle ACB.
    private $acb;

    /**
     * AB is the given fixed edge, and C is the first vertex of the vertex
     * chain. All parameters must point to fixed storage that persists for the
     * lifetime of the EdgeCrosser object.
     */
    public function __construct(S2Point $a, S2Point $b, S2Point $c) {
        $this->a = $a;
        $this->b = $b;
        $this->aCrossB = S2Point::sCrossProd($a, $b);
        $this->restartAt($c);
    }

    /**
     * Call this function when your chain 'jumps' to a new place.
     */
    public function restartAt(S2Point $c) {
        $this->c = $c;
        $this->acb = -S2::robustCCW($this->a, $this->b, $c, $this->aCrossB);
    }

    /**
     * This method is equivalent to calling the S2EdgeUtil.robustCrossing()
     * function (defined below) on the edges AB and CD. It returns +1 if there
     * is a crossing, -1 if there is no crossing, and 0 if two points from
     * different edges are the same. Returns 0 or -1 if either edge is
     * degenerate. As a side effect, it saves vertex D to be used as the next
     * vertex C.
     */
    public function robustCrossing(S2Point $d) {
        // For there to be an edge crossing, the triangles ACB, CBD, BDA, DAC must
        // all be oriented the same way (CW or CCW). We keep the orientation
        // of ACB as part of our state. When each new point D arrives, we
        // compute the orientation of BDA and check whether it matches ACB.
        // This checks whether the points C and D are on opposite sides of the
        // great circle through AB.

        // Recall that robustCCW is invariant with respect to rotating its
        // arguments, i.e. ABC has the same orientation as BDA.
        $bda = S2::robustCCW($this->a, $this->b, $d, $this->aCrossB);

        if ($bda == -$this->acb && $bda != 0) {
            // Most common case -- triangles have opposite orientations.
            $result = -1;
        } else if (($bda & $this->acb) == 0) {
            // At least one value is zero -- two vertices are identical.
            $result = 0;
        } else {
            // assert (bda == acb && bda != 0);
            $result = $this->robustCrossingInternal($d); // Slow path.
        }
        // Now save the current vertex D as the next vertex C, and also save the
        // orientation of the new triangle ACB (which is opposite to the current
        // triangle BDA).
        $this->c = $d;
        $this->acb = -$bda;

        return $result;
    }

    /**
     * This method is equivalent to the S2EdgeUtil.edgeOrVertexCrossing() method
     * defined below. It is similar to robustCrossing, but handles cases where
     * two vertices are identical in a way that makes it easy to implement
     * point-in-polygon containment tests.
     */
    public function edgeOrVertexCrossing(S2Point $d) {
        // We need to copy c since it is clobbered by robustCrossing().
        $c2 = new S2Point($this->c->get(0), $this->c->get(1), $this->c->get(2));

        $crossing = $this->robustCrossing($d);
        if ($crossing < 0) {
            return false;
        }
        if ($crossing > 0) {
            return true;
        }

        return $this->vertexCrossing($this->a, $this->b, $c2, $d);
    }

    /**
     * This function handles the "slow path" of robustCrossing().
     */
    private function robustCrossingInternal(S2Point $d) {
        // ACB and BDA have the appropriate orientations, so now we check the
        // triangles CBD and DAC.
        $cCrossD = S2Point::sCrossProd($this->c, $d);
        $cbd = -S2::robustCCW($this->c, $d, $this->b, $cCrossD);
        if ($cbd != $this->acb) {
            return -1;
        }

        $dac = S2::robustCCW($this->c, $d, $this->a, $cCrossD);

        return ($dac == $this->acb) ? 1 : -1;
    }
}
