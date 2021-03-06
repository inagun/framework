<?php
class CollectionTest extends PHPUnit_Framework_TestCase {
    public function testConstruct()
    {
        $arr = array(1,2,3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals($arr, $collection->getItems());
    }

    public function testIsEmpty()
    {
        $arr = array();
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(true, $collection->isEmpty());
    }

    public function testUnique()
    {
       $arr = array(1, 2, 1,3);
       $collection = new \Suricate\Collection($arr);
        $this->assertEquals([0 => 1, 1 => 2,3 => 3], $collection->unique()->getItems()); 
    }

    public function testCount()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(3, $collection->count());
    }

    public function testFirst()
    {
        $arr = array(4, 5, 6);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(4, $collection->first());
    }

    public function testLast()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(3, $collection->last());


        $collection = new \Suricate\Collection();
        $this->assertEquals(null, $collection->last());
    }

    public function testSum()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(6, $collection->sum());
    }

    public function testHas()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(true, $collection->has('b'));
        $this->assertEquals(false, $collection->has('d'));
    }

	public function testKeys()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$this->assertEquals(['a', 'b', 'c'], $collection->keys());
	}

	public function testPrepend()
	{
		$arr = [4, 5, 6];
        $collection = new \Suricate\Collection($arr);
		$collection->prepend(99);
		$this->assertEquals([99, 4, 5, 6], $collection->getItems());
	}
	
	public function testPut()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$collection->put('z', 99);
		$this->assertEquals(['a' => 1, 'b' => 2, 'c' => 3, 'z' => 99], $collection->getItems());
	}

	public function testShift()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$t = $collection->shift();
		$this->assertEquals(1, $t);
		$this->assertEquals(['b' => 2, 'c' => 3], $collection->getItems());
	}

	public function testPop()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$t = $collection->pop();
		$this->assertEquals(3, $t);
		$this->assertEquals(['a' => 1, 'b' => 2], $collection->getItems());
	}

	public function testReverse()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$t = $collection->reverse();
		$this->assertEquals(['c' => 3, 'b' => 2, 'a' => 1], $t->getItems());
	}

	public function testReduce()
	{

	}

	public function testSlice()
	{

	}
	
	public function testTake()
	{
		$arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
		$t = $collection->take(2);
		$this->assertEquals(['a' => 1, 'b' => 2], $t->getItems());
		
	}


    public function testFilter()
    {
        $arr = array('a' => 1, 'b' => 2, 'c' => 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals(['b' => 2],  $collection->filter(
            function($value) {
                return ($value % 2) === 0;
            })->getItems()
        );
    }

    public function testPush()
    {
        $arr = array(1, 2, 3);
        $collection = new \Suricate\Collection($arr);
        $this->assertEquals([1, 2, 3, 4], $collection->push(4)->getItems());
    }
}
