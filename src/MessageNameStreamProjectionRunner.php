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

namespace Prooph\StandardProjections;

use Prooph\Common\Messaging\Message;
use Prooph\EventStore\Projection\ProjectionManager;

class MessageNameStreamProjectionRunner
{
    /**
     * @var ProjectionManager
     */
    private $projectionManager;

    public function __construct(ProjectionManager $projectionManager)
    {
        $this->projectionManager = $projectionManager;
    }

    public function __invoke(bool $keepRunning = true): void
    {
        $this->projectionManager
            ->createProjection('$by_message_name')
            ->fromAll()
            ->whenAny(function ($state, Message $event): void {
                $messageName = $event->messageName();

                $this->linkTo('$mn-' . $messageName, $event);
            })
            ->run($keepRunning);
    }
}
