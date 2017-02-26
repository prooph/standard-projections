<?php
/**
 * This file is part of the prooph/standard-projections.
 * (c) 2016-2017 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2017 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace ProophTest\StandardProjections;

use PHPUnit\Framework\TestCase;
use Prooph\Common\Event\ProophActionEventEmitter;
use Prooph\EventStore\InMemoryEventStore;
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

        $allStreamProjection = new AllStreamProjectionRunner($eventStore);
        $allStreamProjection(false);

        $this->assertTrue($eventStore->hasStream(new StreamName('$all')));

        $stream = $eventStore->load(new StreamName('$all'));

        $this->assertCount(8, $stream);

        $event = $stream->current();

        $this->assertEquals(['1'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['2'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['3'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['4'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['a'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['b'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['c'], $event->payload());

        $stream->next();
        $event = $stream->current();

        $this->assertEquals(['d'], $event->payload());
    }
}
