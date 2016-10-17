<?php
namespace Formapro\MessageQueue\Transport\Exception;

use Formapro\MessageQueue\Transport\DestinationInterface;

class InvalidDestinationException extends Exception
{
    /**
     * @param DestinationInterface $destination
     * @param string $class
     *
     * @throws static
     */
    public static function assertDestinationInstanceOf(DestinationInterface $destination, $class)
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
