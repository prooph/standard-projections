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

namespace Prooph\StandardProjections;

use Prooph\EventStore\Projection\Projection;

class AllStreamProjection
{
    /**
     * @var Projection
     */
    private $projection;

    public function __construct(Projection $projection)
    {
        $this->projection = $projection;
    }

    public function __invoke(bool $keepRunning = true): void
    {
        $this->projection
            ->fromAll()
            ->whenAny(function ($state, $event): void {
                $this->emit($event);
            })
            ->run($keepRunning);
    }
}
