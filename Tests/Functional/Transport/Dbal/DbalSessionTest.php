<?php
namespace FormaPro\MessageQueue\Tests\Functional\Transport\Dbal;

use Doctrine\DBAL\Exception\DriverException;
use FormaPro\MessageQueue\Transport\Dbal\DbalConnection;
use FormaPro\MessageQueue\Transport\Dbal\DbalSession;

class DbalSessionTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->markTestSkipped('Skipped until functional test environment is ready');

        parent::setUp();

        $this->initClient();

        $this->startTransaction();
    }

//    protected function tearDown()
//    {
//        parent::tearDown();
//
//        $this->rollbackTransaction();
//    }

    public function testShouldCreateMessageQueueTableIfNotExistOnDeclareQueue()
    {
        $connection = $this->createConnection();
        $dbal = $connection->getDBALConnection();

        // guard
        try {
            $dbal->getSchemaManager()->dropTable('message_queue');
        } catch (DriverException $e) {
        }
        $this->assertNotContains('message_queue', $dbal->getSchemaManager()->listTableNames());

        // test
        $session = new DbalSession($connection);
        $session->declareQueue($session->createQueue('name'));

        $this->assertContains('message_queue', $dbal->getSchemaManager()->listTableNames());
    }

    /**
     * @return DbalConnection
     */
    private function createConnection()
    {
        $dbal = $this->getContainer()->get('doctrine.dbal.default_connection');

        return new DbalConnection($dbal, 'message_queue');
    }
}
