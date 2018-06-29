<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 29.06.2018
 * Time: 15:12
 */

namespace S2\S2EdgeUtil;

use S2\R1Interval;
use S2\S2;
use S2\S2LatLng;
use S2\S2LatLngRect;
use S2\S2Point;

/**
 * This class computes a bounding rectangle that contains all edges defined by
 * a vertex chain v0, v1, v2, ... All vertices must be unit length. Note that
 * the bounding rectangle of an edge can be larger than the bounding rectangle
 * of its endpoints, e.g. consider an edge that passes through the north pole.
 */
class RectBounder
{
    // The previous vertex in the chain.
    private $a;

    // The corresponding latitude-longitude.

    /**
     * @var S2LatLng
     */
    private $aLatLng;

    // The current bounding rectangle.

    /**
     * @var S2LatLngRect
     */
    private $bound;

    public function __construct() {
        $this->bound = S2LatLngRect::emptya();
    }

    /**
     * This method is called to add each vertex to the chain. 'b' must point to
     * fixed storage that persists for the lifetime of the RectBounder.
     */
    public function addPoint(S2Point $b) {
        // assert (S2.isUnitLength(b));

        $bLatLng = new S2LatLng($b);

        if ($this->bound->isEmpty()) {
            $this->bound = $this->bound->addPoint($bLatLng);
        } else {
            // We can't just call bound.addPoint(bLatLng) here, since we need to
            // ensure that all the longitudes between "a" and "b" are included.
            $this->bound = $this->bound->union(S2LatLngRect::fromPointPair($this->aLatLng, $bLatLng));

            // Check whether the min/max latitude occurs in the edge interior.
            // We find the normal to the plane containing AB, and then a vector
            // "dir" in this plane that also passes through the equator. We use
            // RobustCrossProd to ensure that the edge normal is accurate even
            // when the two points are very close together.
            $aCrossB = S2::robustCrossProd($this->a, $b);
            $dir = S2Point::sCrossProd($aCrossB, new S2Point(0, 0, 1));
            $da = $dir->dotProd($this->a);
            $db = $dir->dotProd($b);

            if ($da * $db < 0) {
                // Minimum/maximum latitude occurs in the edge interior. This affects
                // the latitude bounds but not the longitude bounds.
                $absLat = acos(abs($aCrossB->get(2) / $aCrossB->norm()));
                $lat = $this->bound->lat();
                if ($da < 0) {
                    // It's possible that absLat < lat.lo() due to numerical errors.
                    $lat = new R1Interval($lat->lo(), max($absLat, $this->bound->lat()->hi()));
                } else {
                    $lat = new R1Interval(min(-$absLat, $this->bound->lat()->lo()), $lat->hi());
                }
                $this->bound = new S2LatLngRect($lat, $this->bound->lng());
            }
        }
        $this->a = $b;
        $this->aLatLng = $bLatLng;
    }

    /**
     * Return the bounding rectangle of the edge chain that connects the
     * vertices defined so far.
     */
    public function getBound() {
        return $this->bound;
    }
}
