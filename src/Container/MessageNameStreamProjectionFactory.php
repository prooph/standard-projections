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

namespace Prooph\StandardProjections\Container;

use Interop\Container\ContainerInterface;
use Prooph\StandardProjections\MessageNameStreamProjection;

final class MessageNameStreamProjectionFactory extends AbstractStreamProjectionFactory
{
    public function __invoke(ContainerInterface $container): MessageNameStreamProjection
    {
        $projection = $this->createProjection($container, false);

        return new MessageNameStreamProjection($projection);
    }

    protected function streamName(): string
    {
        return '$by_message_name';
    }

    public function dimensions(): array
    {
        return ['prooph', 'standard_projections', 'message_name'];
    }
}
