<?php
namespace Suricate;

class Collection implements \IteratorAggregate, \Countable, \ArrayAccess, Interfaces\ICollection
{
    
    protected $items            = array();
    protected $mapping          = array(); // to be deprecated ?

    public $pagination = array(
        'nbPages'   => 0,
        'page'      => 1,
        'nbItems'   => 0,
        );
    
    //protected $iteratorPosition  = 0;

    public function __construct($items = array())
    {
        $this->items = $items;
    }

    public function paginate($nbItemPerPage, $currentPage = 1)
    {
        $this->pagination['page']       = $currentPage;
        $this->pagination['nbItems']    = count($this->items);
        $this->pagination['nbPages']    = ceil($this->pagination['nbItems'] / $nbItemPerPage);

        $this->items = array_slice($this->items, ($currentPage - 1) * $nbItemPerPage, $nbItemPerPage);

        return $this;
    }

    public function getPossibleValuesFor($args, $key = null)
    {
        if (!is_array($args)) {
            $args = array('format' => '%s', 'data' => array($args));
        }

        $values = array();
        foreach ($this->items as $itemKey => $item) {
            $itemValues = array();
            foreach ($args['data'] as $arg) {
                $itemValues[] = dataGet($item, $arg);
            }
            $arrayKey = ($key !== null) ? dataGet($item, $key) : null;
            $values[$arrayKey] = vsprintf($args['format'], $itemValues);
        }

        return $values;
    }

    public function getValuesFor($name)
    {
        $values = array();
        foreach ($this->items as $item) {
            $values[] = dataGet($item, $name);
        }

        return $values;
    }

    public function getItems()
    {
        return $this->items;
    }

    /*public function addItemLink($linkId)
    {
        $this->items[$this->itemOffset] = $linkId;
        // add mapping between item->index and $position in items pool
        $this->mapping[$this->itemOffset] = $linkId;

        $this->itemOffset++;
    }*/

    

    public function getItemFromKey($key)
    {
        $invertedMapping = array_flip($this->mapping);
        if (isset($invertedMapping[$key])) {
            return $this->items[$invertedMapping[$key]];
        }
    }


    // Implementation of Countable Interface
    public function count()
    {
        return count($this->items);
    }

    // Implementation of IteratorAggregate Interface
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    // Implementation of ArrayAccess Interface
    public function offsetExists($offset)
    {
        return isset($this->items[$offset]);
    }

    public function offsetGet($offset)
    {
        $item =isset($this->items[$offset]) ? $this->items[$offset] : null;
        if (gettype($item) == 'object' || $item == null) {
            return $item;
        } else {
            // Lazy load
            $itemType = $this::ITEM_TYPE;
            $itemToLoad = new $itemType;
            $itemToLoad->load($this->items[$offset]);

            $this->items[$offset] = $itemToLoad;

            return $this->items[$offset];
        }
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->items[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->items[$offset]);
    }

    private function cleanStr($str)
    {

        $str = mb_strtolower($str, 'utf-8');
        $str = strtr(
            $str,
            array(
                'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'a'=>'a', 'a'=>'a', 'a'=>'a', 'ç'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'c'=>'c', 'd'=>'d', 'd'=>'d', 'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'e'=>'e', 'g'=>'g', 'g'=>'g', 'g'=>'g', 'h'=>'h', 'h'=>'h', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', 'i'=>'i', '?'=>'i', 'j'=>'j', 'k'=>'k', '?'=>'k', 'l'=>'l', 'l'=>'l', 'l'=>'l', '?'=>'l', 'l'=>'l', 'ñ'=>'n', 'n'=>'n', 'n'=>'n', 'n'=>'n', '?'=>'n', '?'=>'n', 'ð'=>'o', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'o'=>'o', 'o'=>'o', 'o'=>'o', 'œ'=>'o', 'ø'=>'o', 'r'=>'r', 'r'=>'r', 's'=>'s', 's'=>'s', 's'=>'s', 'š'=>'s', '?'=>'s', 't'=>'t', 't'=>'t', 't'=>'t', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'u'=>'u', 'w'=>'w', 'ý'=>'y', 'ÿ'=>'y', 'y'=>'y', 'z'=>'z', 'z'=>'z', 'ž'=>'z'
            )
        );

        return $str;
    }

    // to be deprecated
    public function getFirstItem()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    // to be deprecated
    public function getRandom($nb = 1)
    {
        $keys = (array) array_rand($this->items, $nb);
        $result = array();
        foreach ($keys as $currentKey) {
            $result[$currentKey] = $this->items[$currentKey];
        }

        return $result;
    }

    // Helpers
    public function first()
    {
        foreach ($this->items as $currentItem) {
            return $currentItem;
        }
    }

    public function last()
    {
        if (count($this->items)) {
            return end($this->items);
        } else {
            return null;
        }
    }

    public function isEmpty()
    {
        return empty($this->items);
    }

    public function sum($field = null)
    {
        if ($field == null) {
            return array_sum($this->items);
        }
        $result = 0;
        foreach ($this->items as $item) {
            $result += dataGet($item, $field);
        }
        return $result;
    }

    public function random($nbItems = 1)
    {
        if ($this->isEmpty()) {
            return null;
        }

        $keys = array_rand($this->items, $nbItems);

        if (is_array($keys)) {
            return array_intersect_key($this->items, array_flip($keys));
        } else {
            return $this->items[$keys];
        }
    }

    public function shuffle()
    {
        shuffle($this->items);

        return $this;
    }

    public function unique()
    {
        return new static(array_unique($this->items));
    }

    public function each(\Closure $callback)
    {
        array_map($callback, $this->items);
        return $this;
    }

    public function sort(\Closure $closure)
    {
        uasort($this->items, $closure);

        return $this;
    }

    public function sortBy($field, $reverse = false)
    {
        if ($reverse) {
            $sortFunction = function ($a, $b) use ($field) {
                $first = dataGet($a, $field);
                $second = dataGet($b, $field);
                if ($first == $second) {
                    return 0;
                }
                return ($first > $second) ? -1 : 1;
            };
        } else {
            $sortFunction = function ($a, $b) use ($field) {
                $first = dataGet($a, $field);
                $second = dataGet($b, $field);
                if ($first == $second) {
                    return 0;
                }
                return ($first < $second) ? -1 : 1;
            };
        }


        usort($this->items, $sortFunction);

        return $this;
    }

    public function filter(\Closure $closure)
    {
        return new static(array_filter($this->items, $closure));
    }

    public function search($value, $strict = false)
    {
        return array_search($value, $this->items, $strict);
    }

    public function has($key)
    {
        return $this->offsetExists($key);
    }

    public function keys()
    {
        return array_keys($this->items);
    }

    public function prepend($item)
    {
        array_unshift($this->items, $item);

        return $this;
    }

    public function push($item)
    {
        $this->items[] = $item;

        return $this;
    }

    public function put($key, $val)
    {
        $this->items[$key] = $val;

        return $this;
    }
    public function shift()
    {
        return array_shift($this->items);
    }
    
    public function pop()
    {
        return array_pop($this->items);
    }

    public function reverse()
    {
        return new static(array_reverse($this->items));
    }

    public function reduce(callable $callback, $initial = null)
    {
        return array_reduce($this->items, $callback, $initial);
    }

    public function slice($offset, $length = null, $preserveKeys = false)
    {
        return new static(array_slice($this->items, $offset, $length, $preserveKeys));
    }

    public function take($limit = null)
    {
        if ($limit < 0) {
            return $this->slice(abs($limit), $limit);
        } else {
            return $this->slice(0, $limit);
        }
    }

    public function splice($offset, $length = null, $replacement = array())
    {
        return new static(array_splice($this->items, $offset, $length, $replacement));
    }

    public function chunk($size, $preserveKeys = false)
    {
        $result = new static;
        foreach (array_chunk($this->items, $size, $preserveKeys) as $chunk) {
            $result->push(new static($chunk));
        }
        return $result;
    }
}
