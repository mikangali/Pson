<?php
namespace MikangaliTest\Pson;

use PHPUnit_Framework_TestCase;
use Mikangali\Pson\Pson;

class PsonTest extends PHPUnit_Framework_TestCase {

    public function testConstructor()
    {
        $pson = new Pson();
        $this->assertNotNull($pson);
    }

}