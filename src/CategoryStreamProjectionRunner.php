<?php

/**
 * This file is part of prooph/standard-projections.
 * (c) 2016-2024 prooph software GmbH <contact@prooph.de>
 * (c) 2016-2024 Sascha-Oliver Prolic <saschaprolic@googlemail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Prooph\StandardProjections;

use Prooph\EventStore\Projection\ProjectionManager;

class CategoryStreamProjectionRunner
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
            ->createProjection('$by_category')
            ->fromAll()
            ->whenAny(function ($state, $event): void {
                $streamName = $this->streamName();
                $pos = \strpos($streamName, '-');

                if (false === $pos) {
                    return;
                }

                $category = \substr($streamName, 0, $pos);

                $this->linkTo('$ct-' . $category, $event);
            })
            ->run($keepRunning);
    }
}
