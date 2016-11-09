<?php
namespace Formapro\MessageQueueStompTransport\Tests\Transport;

use Formapro\MessageQueueStompTransport\Transport\StompHeadersEncoder;

class StompHeadersEncoderTest extends \PHPUnit_Framework_TestCase
{
    public function headerOriginalValuesDataProvider()
    {
        return [
            ['Lorem ipsum', 'Lorem ipsum'],
            [1234, '1234'],
            [123.45, '123.45'],
            [true, 'true'],
            [false, 'false'],
            [null, ''],
        ];
    }

    /**
     * @dataProvider headerOriginalValuesDataProvider
     */
    public function testShouldEncodeHeaders($originalValue, $encodedValue)
    {
        $this->assertSame(['key' => $encodedValue], StompHeadersEncoder::encode(['key' => $originalValue]));
    }

    public function headerEncodedValuesDataProvider()
    {
        // header decoder returns same data type as original type is unknown
        return [
            ['Lorem ipsum', 'Lorem ipsum'],
            ['1234', '1234'],
            ['123.45', '123.45'],
            ['true', 'true'],
            ['false', 'false'],
            ['', ''],
        ];
    }

    /**
     * @dataProvider headerEncodedValuesDataProvider
     */
    public function testShouldDecodeHeaders($originalValue, $encodedValue)
    {
        $this->assertSame([['key' => $originalValue], []], StompHeadersEncoder::decode(['key' => $encodedValue]));
    }

    public function propertyValuesDataProvider()
    {
        return [
            ['Lorem ipsum', 's:Lorem ipsum'],
            [1234, 'i:1234'],
            [123.45, 'f:123.45'],
            [true, 'b:true'],
            [false, 'b:false'],
            [null, 'n:null'],
        ];
    }

    /**
     * @dataProvider propertyValuesDataProvider
     */
    public function testShouldEncodeProperties($originalValue, $encodedValue)
    {
        $this->assertSame(['__property_key' => $encodedValue], StompHeadersEncoder::encode([], ['key' => $originalValue]));
    }

    /**
     * @dataProvider propertyValuesDataProvider
     */
    public function testShouldDecodeProperties($originalValue, $encodedValue)
    {
        $this->assertSame([[], ['key' => $originalValue]], StompHeadersEncoder::decode(['__property_key' => $encodedValue]));
    }

    public function testShouldThrowLogicExceptionIfHeaderValueIsNotScalar()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Value type is not valid: "object"');

        StompHeadersEncoder::encode(['key' => new \stdClass()], []);
    }

    public function testShouldThrowLogicExceptionIfPropertyValueIsNotScalar()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Value type is not valid: "object"');

        StompHeadersEncoder::encode([], ['key' => new \stdClass()]);
    }

    public function testShouldThrowLogicExceptionIfEncodedPropertyValueIsLessThanTwoChars()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid length of value, must be 2 or more characters: "s"');

        StompHeadersEncoder::decode(['__property_key' => 's']);
    }

    public function testShouldThrowLogicExceptionIfEncodedPropertyValueHasInvalidFormat()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Seems value is not valid, expected format {s|i|f|b|n}:{value} : "st"');

        StompHeadersEncoder::decode(['__property_key' => 'st']);
    }

    public function testShouldThrowLogicExceptionIfEncodedPropertyValueHasUnknownType()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Type is invalid: "g:value"');

        StompHeadersEncoder::decode(['__property_key' => 'g:value']);
    }
}
