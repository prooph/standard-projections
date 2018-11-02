<?php

/**
 * This file is part of the prooph/standard-projections.
 * (c) 2016-2018 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2018 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
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
use Prooph\StandardProjections\CategoryStreamProjectionRunner;
use ProophTest\EventStore\Mock\TestDomainEvent;

class CategoryStreamProjectionRunnerTest extends TestCase
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
                new StreamName('foo-123'),
                new \ArrayIterator([
                    TestDomainEvent::with(['1'], 1),
                    TestDomainEvent::with(['2'], 2),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('bar-123'),
                new \ArrayIterator([
                    TestDomainEvent::with(['a'], 1),
                    TestDomainEvent::with(['b'], 2),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('foo-234'),
                new \ArrayIterator([
                    TestDomainEvent::with(['3'], 3),
                    TestDomainEvent::with(['4'], 4),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('bar-234'),
                new \ArrayIterator([
                    TestDomainEvent::with(['c'], 3),
                    TestDomainEvent::with(['d'], 4),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('baz'),
                new \ArrayIterator([
                    TestDomainEvent::with(['1b'], 1),
                    TestDomainEvent::with(['2b'], 2),
                ])
            )
        );

        $eventStore->commit();

        $projectionManager = new InMemoryProjectionManager($eventStore);
        $categoryStreamProjection = new CategoryStreamProjectionRunner($projectionManager);
        $categoryStreamProjection(false);

        $this->assertTrue($eventStore->hasStream(new StreamName('$ct-foo')));
        $this->assertTrue($eventStore->hasStream(new StreamName('$ct-bar')));

        $streamEvents = $eventStore->load(new StreamName('$ct-foo'));

        $this->assertCount(4, $streamEvents);

        $event = $streamEvents->current();

        $this->assertEquals(['1'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['2'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['3'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['4'], $event->payload());

        $streamEvents = $eventStore->load(new StreamName('$ct-bar'));

        $this->assertCount(4, $streamEvents);

        $event = $streamEvents->current();

        $this->assertEquals(['a'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['b'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['c'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['d'], $event->payload());
    }
}
