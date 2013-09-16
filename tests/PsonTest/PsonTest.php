<?php
namespace PsonTest;

use PHPUnit_Framework_TestCase;
use Pson\Pson;

class PsonTest extends PHPUnit_Framework_TestCase {

    public function testConstructor()
    {
        $pson = new Pson();
        $this->assertNotNull($pson);
    }

}