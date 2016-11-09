<?php
namespace Formapro\MessageQueue\Transport\Stomp;

class StompHeadersEncoder
{
    const PROPERTY_PREFIX = '__property_';
    const TYPE_STRING = 's';
    const TYPE_INT = 'i';
    const TYPE_FLOAT = 'f';
    const TYPE_BOOL = 'b';
    const TYPE_NULL = 'n';

    /**
     * @param array $headers
     * @param array $properties
     *
     * @return array
     */
    public static function encode(array $headers = [], array $properties = [])
    {
        $encoded = [];

        foreach ($headers as $key => $value) {
            $encoded[$key] = self::encodeHeaderValue($value);
        }

        foreach ($properties as $key => $value) {
            $encoded[self::PROPERTY_PREFIX.$key] = self::encodePropertyValue($value);
        }

        return $encoded;
    }

    /**
     * @param array $headers
     *
     * @return array [[headers], [properties]]
     */
    public static function decode(array $headers = [])
    {
        $decodedHeaders = [];
        $decodedProperties = [];
        $prefixLength = strlen(self::PROPERTY_PREFIX);

        foreach ($headers as $key => $value) {
            if (0 === strpos($key, self::PROPERTY_PREFIX)) {
                $decodedProperties[substr($key, $prefixLength)] = self::decodePropertyValue($value);
            } else {
                // does nothing there is no info about value type
                $decodedHeaders[$key] = $value;
            }
        }

        return [$decodedHeaders, $decodedProperties];
    }

    /**
     * @param string|bool|int|float $value
     *
     * @return string
     */
    private static function encodeHeaderValue($value)
    {
        switch ($type = gettype($value)) {
            case 'string':
            case 'integer':
            case 'double':
            case 'NULL':
                return (string) $value;
            case 'boolean':
                return $value ? 'true' : 'false';
            default:
                throw new \LogicException(sprintf('Value type is not valid: "%s"', $type));
        }
    }

    /**
     * @param string $value
     *
     * @return bool|float|int|null
     */
    private static function decodePropertyValue($value)
    {
        if (strlen($value) < 2) {
            throw new \LogicException(sprintf('Invalid length of value, must be 2 or more characters: "%s"', $value));
        }

        if (':' !== $value[1]) {
            throw new \LogicException(sprintf('Seems value is not valid, expected format {s|i|f|b|n}:{value} : "%s"', $value));
        }

        $type = $value[0];
        $v = substr($value, 2);

        switch ($type) {
            case self::TYPE_STRING:
                return (string) $v;
            case self::TYPE_INT:
                return (int) $v;
            case self::TYPE_BOOL:
                return $v === 'true';
            case self::TYPE_FLOAT:
                return (float) $v;
            case self::TYPE_NULL:
                return null;
            default:
                throw new \LogicException(sprintf('Type is invalid: "%s"', $value));
        }
    }

    /**
     * @param bool|float|int|null $value
     *
     * @return string
     */
    private static function encodePropertyValue($value)
    {
        switch ($type = gettype($value)) {
            case 'string':
                return self::TYPE_STRING.':'.$value;
            case 'integer':
                return self::TYPE_INT.':'.$value;
            case 'boolean':
                return self::TYPE_BOOL.':'.($value ? 'true' : 'false');
            case 'double':
                return self::TYPE_FLOAT.':'.$value;
            case 'NULL':
                return self::TYPE_NULL.':null';
            default:
                throw new \LogicException(sprintf('Value type is not valid: "%s"', $type));
        }
    }
}
