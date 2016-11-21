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

use Interop\Config\ConfigurationTrait;
use Interop\Config\ProvidesDefaultOptions;
use Interop\Config\RequiresConfig;
use Interop\Config\RequiresMandatoryOptions;
use Interop\Container\ContainerInterface;
use PDO;
use Prooph\StandardProjections\AllStreamProjection;

final class AllStreamProjectionFactory implements ProvidesDefaultOptions, RequiresConfig, RequiresMandatoryOptions
{
    use ConfigurationTrait;

    /**
     * @var array
     */
    private $pdoDriverSchemeAliases = [
        'pdo_mysql' => 'mysql',
        'pdo_pgsql' => 'pgsql',
    ];

    private $pdoDriverSchemeSeparators = [
        'pdo_mysql' => ';',
        'pdo_pgsql' => ' ',
    ];

    public function __invoke(ContainerInterface $container): AllStreamProjection
    {
        $config = $container->get('config');
        $config = $this->options($config);

        $eventStore = $container->get($config['event_store']);

        switch (strtolower($config['type'])) {
            case 'mysql':
                $projection = new \Prooph\EventStore\PDO\Projection\MySQLEventStoreProjection(
                    $eventStore,
                    $this->getPDOConnection($container, $config),
                    '$all',
                    $config['event_streams_table'],
                    $config['projections_table'],
                    $config['lock_timeout_ms'],
                    true
                );
                break;
            case 'postgres':
                $projection = new \Prooph\EventStore\PDO\Projection\PostgresEventStoreProjection(
                    $eventStore,
                    $this->getPDOConnection($container, $config),
                    '$all',
                    $config['event_streams_table'],
                    $config['projections_table'],
                    $config['lock_timeout_ms'],
                    true
                );
                break;
            case 'inmemory':
                $projection = new \Prooph\EventStore\Projection\InMemoryEventStoreProjection(
                    $eventStore,
                    '$all',
                    true
                );
                break;
            default:
                throw new \InvalidArgumentException('Unknown projection type given');
        }

        return new AllStreamProjection($projection);
    }

    public function dimensions(): array
    {
        return ['prooph', 'standard_projections'];
    }

    public function mandatoryOptions(): array
    {
        return [
            'event_store',
            'type',
        ];
    }

    public function defaultOptions(): array
    {
        return [
            'connection_options' => [
                'driver' => 'pdo_mysql',
                'user' => 'root',
                'password' => '',
                'host' => '127.0.0.1',
                'dbname' => 'event_store',
                'port' => 3306,
            ],
            'event_streams_table' => 'event_streams',
            'projections_table' => 'projection',
            'lock_timeout_ms' => 1000,
        ];
    }

    protected function getPDOConnection(ContainerInterface $container, $config): PDO
    {
        if (isset($config['connection_service'])) {
            $connection = $container->get($config['connection_service']);
        } else {
            $separator = $this->pdoDriverSchemeSeparators[$config['connection_options']['driver']];
            $dsn = $this->pdoDriverSchemeAliases[$config['connection_options']['driver']] . ':';
            $dsn .= 'host=' . $config['connection_options']['host'] . $separator;
            $dsn .= 'port=' . $config['connection_options']['port'] . $separator;
            $dsn .= 'dbname=' . $config['connection_options']['dbname'] . $separator;
            $dsn = rtrim($dsn);
            $user = $config['connection_options']['user'];
            $password = $config['connection_options']['password'];
            $connection = new PDO($dsn, $user, $password);
        }

        return $connection;
    }
}
