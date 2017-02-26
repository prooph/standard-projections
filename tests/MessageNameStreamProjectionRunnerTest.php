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
use Prooph\StandardProjections\MessageNameStreamProjectionRunner;
use ProophTest\StandardProjections\Mock\TestDomainEvent;

class MessageNameStreamProjectionRunnerTest extends TestCase
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
                    TestDomainEvent::withPayloadAndMessageName(['1f'], 1, 'event-a'),
                    TestDomainEvent::withPayloadAndMessageName(['2f'], 2, 'event-b'),
                ])
            )
        );

        $eventStore->create(
            new Stream(
                new StreamName('bar-123'),
                new \ArrayIterator([
                    TestDomainEvent::withPayloadAndMessageName(['1b'], 1, 'event-a'),
                    TestDomainEvent::withPayloadAndMessageName(['2b'], 2, 'event-b'),
                ])
            )
        );

        $eventStore->commit();

        $categoryStreamProjection = new MessageNameStreamProjectionRunner($eventStore);
        $categoryStreamProjection(false);

        $this->assertTrue($eventStore->hasStream(new StreamName('$mn-event-a')));
        $this->assertTrue($eventStore->hasStream(new StreamName('$mn-event-b')));

        $streamEvents = $eventStore->load(new StreamName('$mn-event-a'));

        $this->assertCount(2, $streamEvents);

        $event = $streamEvents->current();

        $this->assertEquals(['1f'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['1b'], $event->payload());

        $streamEvents = $eventStore->load(new StreamName('$mn-event-b'));

        $event = $streamEvents->current();

        $this->assertEquals(['2f'], $event->payload());

        $streamEvents->next();
        $event = $streamEvents->current();

        $this->assertEquals(['2b'], $event->payload());
    }
}
