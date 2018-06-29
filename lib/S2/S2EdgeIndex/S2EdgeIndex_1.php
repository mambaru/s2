<?php
/**
 * Created by PhpStorm.
 * User: mix
 * Date: 29.06.2018
 * Time: 14:46
 */

namespace S2\S2EdgeIndex;

use S2\S2EdgeIndex;

class S2EdgeIndex_1 extends S2EdgeIndex
{


    protected function  getNumEdges() {
        return $this->numVertices;
    }


    protected function edgeFrom(int $index) {
        return $this->vertex($index);
    }


    protected function edgeTo(int $index) {
        return $this->vertex($index + 1);
    }
}