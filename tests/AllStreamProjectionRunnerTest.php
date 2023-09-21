<?php

/**
 * This file is part of prooph/standard-projections.
 * (c) 2016-2023 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2023 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\StandardProjections;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventStore\InMemoryEventStore;
use Prooph\EventStore\Projection\InMemoryProjectionManager;
use Prooph\EventStore\Stream;
use Prooph\EventStore\StreamName;
use Prooph\StandardProjections\AllStreamProjectionRunner;
use ProophTest\EventStore\Mock\TestDomainEvent;

class AllStreamProjectionRunnerTest extends TestCase
{
    /**
     * @test
     */
    public function it_emits_events_for_all_streams()
    {
        $eventStore = new InMemoryEventStore(new ProophActionEventEmitter());

        $eventStore->beginTransaction();

        $eventStore->create(
            new Stream(
                new StreamName('foo'),
                new \ArrayIterator([
                    TestDomainEvent::with(['1'], 1),
                    TestDomainEvent::with(['2'], 2),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('bar'),
                new \ArrayIterator([
                    TestDomainEvent::with(['a'], 1),
                    TestDomainEvent::with(['b'], 2),
                ])
            )
        );

        $eventStore->appendTo(
            new StreamName('foo'),
            new \ArrayIterator([
                TestDomainEvent::with(['3'], 3),
                TestDomainEvent::with(['4'], 4),
            ])
        );

        $eventStore->appendTo(
            new StreamName('bar'),
            new \ArrayIterator([
                TestDomainEvent::with(['c'], 3),
                TestDomainEvent::with(['d'], 4),
            ])
        );

        $eventStore->commit();

        $projectionManager = new InMemoryProjectionManager($eventStore);
        $allStreamProjection = new AllStreamProjectionRunner($projectionManager);
        $allStreamProjection(false);

        $this->assertTrue($eventStore->hasStream(new StreamName('$all')));

        $streamEvents = $eventStore->load(new StreamName('$all'));

        $this->assertCount(8, $streamEvents);

        $event = $streamEvents->current();

        $this->assertEquals(['1'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['2'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['a'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['b'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['3'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['4'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['c'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['d'], $event->payload());
    }
}
