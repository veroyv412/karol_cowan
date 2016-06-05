<?php

class Swift_Events_TransportChangeEventTest extends \PHPUnit_Framework_TestCase
{
    public function testGetTransportReturnsTransport()
    {
        $transport = $this->createTransport();
        $evt = $this->createEvent($transport);
        $ref = $evt->getTransport();
        $this->assertEquals($transport, $ref);
    }

    public function testSourceIsTransport()
    {
        $transport = $this->createTransport();
        $evt = $this->createEvent($transport);
        $ref = $evt->getSource();
        $this->assertEquals($transport, $ref);
    }

    // -- Creation Methods

    private function createEvent(Swift_Transport $source)
    {
        return new Swift_Events_TransportChangeEvent($source);
    }

    private function createTransport()
    {
        return $this->getMock('Swift_Transport');
    }
}
