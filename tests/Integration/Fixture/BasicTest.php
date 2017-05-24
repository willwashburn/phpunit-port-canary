<?php

use PHPUnit_Framework_TestCase as TestCase;

class BasicTest extends TestCase {

    public function test_no_port_crossed() {

        $this->assertTrue(true);
    }

    public function test_crosses_port() {

        $ch = curl_init('https://www.tailwindapp.com');
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_exec($ch);
        $this->assertTrue(true);
    }



}