<?php
namespace Formapro\MessageQueueBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="formapro_message_queue_job_unique")
 */
class JobUnique
{
    /**
     * @ORM\Id
     * @ORM\Column(name="name", type="string")
     */
    protected $name;
}
