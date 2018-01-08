<?php

namespace Ffcms\Core\Traits;

/**
 * Assistance tools for fast using some magical things and opportunity of classic object-oriented programming
 */
trait ClassTools
{

    /**
     * Create hash string from current class properties and itself values.
     * This method is good stuff for caching dynamic instances
     * @return string|null
     */
    public function createStringClassSnapshotHash()
    {
        $hash = null;
        foreach ($this as $property => $value) {
            $hash = md5($hash . $property . '=' . $value);
        }
        return $hash;
    }
}
