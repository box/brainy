<?php

namespace Box\Brainy\Compiler\Wrappers;

class StaticWrapper
{
    private $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * The __toString() method un-statics the contents
     * @return string
     */
    public function __toString()
    {
        return (string) $this->code;
    }

    /**
     * Combine two values which may be static wrappers. The output is only a
     * static wrapper if both the left and the right are static wrappers.
     * @param string|StaticWrapper $left
     * @param string|StaticWrapper $right
     * @return string|StaticWrapper
     */
    public static function concat($left, $right)
    {
        if ($left instanceof StaticWrapper && $right instanceof StaticWrapper) {
            return new StaticWrapper($left . $right);
        }
        return $left . $right;
    }

    /**
     * Combine two values which may be static wrappers. The output is a static
     * wrapper if either the left or the right are static wrappers. This is
     * only to be used in cases where the left or the right is known not to
     * affect whether the result is a static wrapper.
     * @param string|StaticWrapper $left
     * @param string|StaticWrapper $right
     * @return string|StaticWrapper
     */
    public static function staticConcat($left, $right)
    {
        if ($left instanceof StaticWrapper || $right instanceof StaticWrapper) {
            return new StaticWrapper($left . $right);
        }
        return $left . $right;
    }

    /**
     * If all of the values in $conditions are static wrappers, the result is a
     * static wrapper of $code. Otherwise, $code is returned.
     * @param string $code
     * @param array  $conditions
     * @return string|StaticWrapper
     */
    public static function staticIfAll($code, $conditions)
    {
        foreach ($conditions as $cond) {
            if (!($cond instanceof StaticWrapper)) {
                return $code;
            }
        }
        return new StaticWrapper($code);
    }
}
