<?php

declare(strict_types=1);

namespace Zxin\Tests;

use Phinx\Config\Config;
use Phinx\Console\Command\AbstractCommand;
use Phinx\Console\PhinxApplication;
use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Migration\Manager;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class ManagerTest extends TestCase
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Manager
     */
    protected $manager;

    protected $configFile = __DIR__.'/_file/config.php';

    protected function setUp(): void
    {
        $this->config = Config::fromPhp($this->configFile);
        $this->manager = new Manager($this->config, new ArrayInput([]), new NullOutput());
    }

    /**
     * @param null $env
     */
    public function getAdapter($env = null): AdapterInterface
    {
        return $this->manager->getEnvironment($env ?? 'production')->getAdapter();
    }

    public function testMigrateSchema()
    {
        $adapter = $this->getAdapter();
        $adapter->dropDatabase($adapter->getOption('name'));
        $adapter->createDatabase($adapter->getOption('name'));
        $adapter->disconnect();

        $this->callCommand('migrate', ['-t' => '20190125021334'], AbstractCommand::CODE_SUCCESS);

        $this->assertTrue($adapter->hasTable('system'));
        $this->assertTrue($adapter->hasPrimaryKey('system', ['label']));
        $this->assertTrue($adapter->hasPrimaryKey('system', ['label']));
        $this->assertEquals(28, \count($adapter->getColumns('system')));
        $this->assertTrue($adapter->hasIndexByName('permission', 'hash'));

        $this->assertTrue($adapter->hasColumn('permission', 'blob1'));
        $this->assertTrue($adapter->hasColumn('permission', 'blob2'));

        $this->callCommand('breakpoint', ['-t' => '20190125021334'], AbstractCommand::CODE_SUCCESS);

        $this->callCommand('migrate', ['-t' => '20191022071225'], AbstractCommand::CODE_SUCCESS);

        $this->assertFalse($adapter->hasIndexByName('permission', 'hash'));
        $this->assertTrue($adapter->hasIndexByName('permission', 'name'));
        $column = null;
        foreach ($adapter->getColumns('system') as $column) {
            if ('string' === $column->getName()) {
                break;
            }
        }
        $this->assertTrue(isset($column));
        $this->assertTrue('string' === $column->getName());
        $this->assertTrue(512 === $column->getLimit());

        $this->callCommand('migrate', ['-t' => '20200522035054'], AbstractCommand::CODE_SUCCESS);

        $this->assertFalse($adapter->hasColumn('system', 'text'));
        $this->assertFalse($adapter->hasColumn('system', 'json'));

        $this->callCommand('rollback', ['-t' => '20191022071225'], AbstractCommand::CODE_SUCCESS);

        $this->assertTrue($adapter->hasColumn('system', 'text'));
        $this->assertTrue($adapter->hasColumn('system', 'json'));

        $this->callCommand('rollback', ['-t' => '0'], AbstractCommand::CODE_SUCCESS);

        $this->assertFalse($adapter->hasIndexByName('permission', 'name'));
        $this->assertTrue($adapter->hasTable('system'));

        $this->callCommand('breakpoint', ['-t' => '20190125021334', '--unset' => true], AbstractCommand::CODE_SUCCESS);
        $this->callCommand('rollback', ['-t' => '0'], AbstractCommand::CODE_SUCCESS);

        $this->assertFalse($adapter->hasTable('system'));
    }

    /**
     * @param int $successCode
     */
    public function callCommand(string $name, array $parameters = [], $successCode = AbstractCommand::CODE_SUCCESS)
    {
        $parameters['-c'] = $this->configFile;
        $this->call($name, $parameters, $exitCode);
        $this->assertEquals($successCode, $exitCode, "call migrate:{$name} fail");
    }

    /**
     * @param int $exitCode
     *
     * @return OutputInterface
     *
     * @noinspection PhpDocMissingThrowsInspection
     */
    public function call(string $command, array $parameters = [], &$exitCode = 0)
    {
        $input = new ArrayInput($parameters);
        $output = new ConsoleOutput();

        $app = new PhinxApplication();
        $app->setAutoExit(false);
        $app->setCatchExceptions(false);
        $exitCode = $app
            ->find($command)
            ->run($input, $output);

        return $output;
    }
}
