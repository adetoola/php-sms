<?php

use Adetoola\SMS\SMS;

use PHPUnit\Framework\TestCase;

class SMSTest extends TestCase
{
    public function setUp()
    {
        $this->SMS = new SMS();
    }

    public function testSenderCanBeSet()
    {
        // One Character
        $this->SMS->sender('A');
        $this->assertEquals('A', $this->SMS->getSender());

        // Three Characters
        $this->SMS->sender('ADE');
        $this->assertEquals('ADE', $this->SMS->getSender());

        // Eleven Characters
        $this->SMS->sender('08123456789');
        $this->assertEquals('08123456789', $this->SMS->getSender());
    }

    public function testCountryCodeCanBeSet()
    {
        $this->SMS->country('1');
        $this->assertEquals('1', $this->SMS->getCountry());

        $this->SMS->country('234');
        $this->assertEquals('234', $this->SMS->getCountry());
    }

    public function testCountryCodeCanBeFormatted()
    {
        // Remove '+' sign
        $this->SMS->country('+234');
        $this->assertEquals('234', $this->SMS->getCountry());

        // Remove '-' sign
        $this->SMS->country('23-4');
        $this->assertEquals('234', $this->SMS->getCountry());

        // Remove space characters
        $this->SMS->country('1 345');
        $this->assertEquals('1345', $this->SMS->getCountry());

        // Remove all of '+', '-' signs & space characters
        $this->SMS->country('+1 345');
        $this->assertEquals('1345', $this->SMS->getCountry());
    }

    /**
     * @expectedException Adetoola\SMS\Exception\InvalidArgumentException
     */
    public function testSenderLengthCanNotBeEmpty()
    {
        // Empty String
        $this->SMS->sender('');
        $this->assertEquals('', $this->SMS->getSender());
    }

    /**
     * @expectedException Adetoola\SMS\Exception\InvalidArgumentException
     */
    public function testSenderLengthCanNotBeLongerThanLimit()
    {
        // Twelve Characters
        $this->SMS->sender('asdfghjklqwe');
        $this->assertEquals('asdfghjklqwe', $this->SMS->getSender());

        // Sixteen Characters
        $this->SMS->sender('qwertyuioplkjhgf');
        $this->assertEquals('qwertyuioplkjhgf', $this->SMS->getSender());
    }

    public function testCredentialCanBeSet()
    {
        $credentials = [
            'session_id' => 'SESSION_ID'
        ];

        $this->SMS->credentials($credentials);
        $this->assertEquals($credentials, $this->SMS->getCredentials());
    }

    public function testGatewayCanBeSet()
    {
        $credentials = [
            'session_id' => 'SESSION_ID'
        ];
        $this->SMS->sender('08123456789')->country('234')->credentials($credentials)->gateway('SMSLive247');
        $this->assertEquals('08123456789', $this->SMS->getsender());
        $this->assertEquals('234', $this->SMS->getCountry());
        $this->assertEquals($credentials, $this->SMS->getCredentials());
    }
}
