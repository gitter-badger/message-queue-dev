<?php
namespace Formapro\MessageQueue\Util;

use Ramsey\Uuid\Uuid as RamseyUuid;

class UUID
{
    /**
     * @return string
     */
    public static function generate()
    {
        return RamseyUuid::uuid4()->toString();
    }
}
