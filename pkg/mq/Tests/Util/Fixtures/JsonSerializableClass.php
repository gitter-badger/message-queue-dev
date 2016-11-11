<?php
namespace Formapro\MessageQueue\Tests\Util\Fixtures;

class JsonSerializableClass implements \JsonSerializable
{
    public $keyPublic = 'public';

    public function jsonSerialize()
    {
        return [
            'key' => 'value',
        ];
    }
}
