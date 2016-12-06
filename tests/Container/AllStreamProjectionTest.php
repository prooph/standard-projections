<?php
/**
 * This file is part of the prooph/standard-projections.
 * (c) 2016-2016 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2016 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\StandardProjections\Container;

use Interop\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\Common\Messaging\MessageConverter;
use Prooph\Common\Messaging\MessageFactory;
use Prooph\EventStore\EventStore;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\PDO\IndexingStrategy;
use Prooph\EventStore\PDO\MySQLEventStore;
use Prooph\EventStore\PDO\PostgresEventStore;
use Prooph\EventStore\PDO\TableNameGeneratorStrategy;
use Prooph\StandardProjections\AllStreamProjection;
use Prooph\StandardProjections\Container\AllStreamProjectionFactory;

class AllStreamProjectionTest extends TestCase
{
    /**
     * @test
     */
    public function it_can_create_myql_projection(): void
    {
        $config = [
            'prooph' => [
                'standard_projections' => [
                    'all' => [
                        'event_store' => 'mysql',
                        'connection_service' => 'pdo',
                    ],
                ],
            ],
        ];

        $mysqlEventStore = new MySQLEventStore(
            new ProophActionEventEmitter(),
            $this->prophesize(MessageFactory::class)->reveal(),
            $this->prophesize(MessageConverter::class)->reveal(),
            $this->prophesize(\PDO::class)->reveal(),
            $this->prophesize(IndexingStrategy::class)->reveal(),
            $this->prophesize(TableNameGeneratorStrategy::class)->reveal(),
            1000,
            'event_streams'
        );

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get('pdo')->willReturn($this->prophesize(\PDO::class)->reveal())->shouldBeCalled();
        $container->get('mysql')->willReturn($mysqlEventStore)->shouldBeCalled();

        $factory = new AllStreamProjectionFactory();
        $projection = $factory($container->reveal());

        $this->assertInstanceOf(AllStreamProjection::class, $projection);
    }

    /**
     * @test
     */
    public function it_can_create_postgres_projection(): void
    {
        $config = [
            'prooph' => [
                'standard_projections' => [
                    'all' => [
                        'event_store' => 'postgres',
                        'connection_service' => 'pdo',
                    ],
                ],
            ],
        ];

        $postgresEventStore = new PostgresEventStore(
            new ProophActionEventEmitter(),
            $this->prophesize(MessageFactory::class)->reveal(),
            $this->prophesize(MessageConverter::class)->reveal(),
            $this->prophesize(\PDO::class)->reveal(),
            $this->prophesize(IndexingStrategy::class)->reveal(),
            $this->prophesize(TableNameGeneratorStrategy::class)->reveal(),
            1000,
            'event_streams'
        );

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get('pdo')->willReturn($this->prophesize(\PDO::class)->reveal())->shouldBeCalled();
        $container->get('postgres')->willReturn($postgresEventStore)->shouldBeCalled();

        $factory = new AllStreamProjectionFactory();
        $projection = $factory($container->reveal());

        $this->assertInstanceOf(AllStreamProjection::class, $projection);
    }

    /**
     * @test
     */
    public function it_can_create_inmemory_projection(): void
    {
        $config = [
            'prooph' => [
                'standard_projections' => [
                    'all' => [
                        'event_store' => 'inmemory',
                    ],
                ],
            ],
        ];

        $inMemoryEventStore = new InMemoryEventStore(new ProophActionEventEmitter());

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get('inmemory')->willReturn($inMemoryEventStore)->shouldBeCalled();

        $factory = new AllStreamProjectionFactory();
        $projection = $factory($container->reveal());

        $this->assertInstanceOf(AllStreamProjection::class, $projection);
    }

    /**
     * @test
     */
    public function it_throws_exception_for_unknown_event_store_implementations(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $config = [
            'prooph' => [
                'standard_projections' => [
                    'all' => [
                        'event_store' => 'unknown_implementation',
                    ],
                ],
            ],
        ];

        $container = $this->prophesize(ContainerInterface::class);
        $container->get('config')->willReturn($config)->shouldBeCalled();
        $container->get('unknown_implementation')->willReturn($this->prophesize(EventStore::class)->reveal())->shouldBeCalled();

        $factory = new AllStreamProjectionFactory();
        $factory($container->reveal());
    }
}
