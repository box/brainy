<?php

namespace Box\Brainy\Templates;


class ForEachSpecialVariable extends Variable
{
    public $total = 0;
    public $iteration = 0;
    public $index = 0;
    public $show = false;
    public $_loop = false;

    public $first = false;
    public $last = false;

    public $source = null;
    public $key = null;
    public $value = null;

    /**
     * @return string
     */
    public function __toString()
    {
        return 'foreach';
    }

    /**
     * Sets the source
     * @param mixed $source
     * @return void
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * Sets the count based on the source
     * @return void
     */
    public function setCount()
    {
        $this->total = self::getCount($this->source);
    }

    /**
     * Returns the size of an item
     * @param  mixed $value A value to count the members of
     * @return int
     */
    public static function getCount($value)
    {
        if (is_array($value) || $value instanceof \Countable) {
            return count($value);
        }

        if ($value instanceof \IteratorAggregate) {
            // Note: getIterator() returns a Traversable, not an Iterator
            // thus rewind() and valid() methods may not be present
            return iterator_count($value->getIterator());
        } elseif ($value instanceof \Iterator) {
            return iterator_count($value);
        } elseif ($value instanceof \PDOStatement) {
            return $value->rowCount();
        } elseif ($value instanceof \Traversable) {
            return iterator_count($value);
        } elseif ($value instanceof \ArrayAccess) {
            if ($value->offsetExists(0)) {
                return 1;
            }
        } elseif (is_object($value)) {
            return count($value);
        }

        return 0;
    }

}
