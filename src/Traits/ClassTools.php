<?php

namespace Ffcms\Core\Traits;

use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Obj;

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
            if (Any::isArray($value)) {
                $value = implode('.', $value);
            }
            $hash = md5($hash . $property . '=' . $value);
        }
        return $hash;
    }

    /**
     * Get method required arguments count
     * @param $class
     * @param string $method
     * @return int
     */
    public function getMethodRequiredArgCount($class, string $method): int
    {
        $instance = new \ReflectionMethod($class, $method);
        $count = 0;
        // calculate method defined arguments count
        foreach ($instance->getParameters() as $arg) {
            if (!$arg->isOptional()) {
                $count++;
            }
        }

        return $count;
    }
}
