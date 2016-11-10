<?php
namespace Formapro\Jms\Exception;

use Formapro\Jms\Destination;

class InvalidDestinationException extends Exception
{
    /**
     * @param Destination $destination
     * @param string $class
     *
     * @throws static
     */
    public static function assertDestinationInstanceOf(Destination $destination, $class)
    {
        if (!$destination instanceof $class) {
            throw new static(sprintf(
                'The destination must be an instance of %s but it is %s.',
                $class,
                get_class($destination)
            ));
        }
    }
}
