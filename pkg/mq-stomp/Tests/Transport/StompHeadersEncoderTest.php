<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueueStompTransport\Transport\StompHeadersEncoder;

class StompHeadersEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function headerOriginalValuesDataProvider()
    {
        return [
            [['key' => 'Lorem ipsum'], ['key' => 'Lorem ipsum', '_type_key' => 's']],
            [['key' => 1234], ['key' => '1234', '_type_key' => 'i']],
            [['key' => 123.45], ['key' => '123.45', '_type_key' => 'f']],
            [['key' => true], ['key' => 'true', '_type_key' => 'b']],
            [['key' => false], ['key' => 'false', '_type_key' => 'b']],
            [['key' => null], ['key' => '', '_type_key' => 'n']],
        ];
    }

    /**
     * @dataProvider headerOriginalValuesDataProvider
     */
    public function testShouldEncodeHeaders($originalValue, $encodedValue)
    {
        $this->assertSame($encodedValue, StompHeadersEncoder::encode($originalValue));
    }

    /**
     * @dataProvider headerOriginalValuesDataProvider
     */
    public function testShouldDecodeHeaders($originalValue, $encodedValue)
    {
        $this->assertSame($originalValue, StompHeadersEncoder::decode($encodedValue));
    }

    public function testShouldKeepTypeAsIsIfHereIsNoTypeField()
    {
        $this->assertSame(['key' => 123.45], StompHeadersEncoder::decode(['key' => 123.45]));
    }
}
