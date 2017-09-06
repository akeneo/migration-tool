<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\Cli;

use Akeneo\PimMigration\Domain\Command\Console;
use Akeneo\PimMigration\Domain\Command\UnixCommand;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\MySqlDumpCommand;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\MySqlQueryCommand;
use Akeneo\PimMigration\Infrastructure\DatabaseServices\MysqlRawCommand;
use Akeneo\PimMigration\Domain\Pim\DestinationPim;
use Akeneo\PimMigration\Domain\Pim\Pim;
use Akeneo\PimMigration\Domain\Command\SymfonyCommand;

/**
 * Your Class description.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractConsole implements Console
{
    protected function getProcessedCommand(UnixCommand $command, Pim $pim): string
    {
        if ($command instanceof SymfonyCommand) {
            if ($pim instanceof DestinationPim) {
                return sprintf('bin/console %s', $command);
            }

            return sprintf('app/console %s', $command);
        }

        $mysqlConnection = sprintf(
            'mysql --host=%s --port=%s -u%s -p%s %s',
            $pim->getMysqlHost(),
            strval($pim->getMysqlPort()),
            $pim->getDatabaseUser(),
            $pim->getDatabasePassword(),
            $pim->getDatabaseName()
        );

        if ($command instanceof MySqlQueryCommand) {
            return sprintf(
                '%s -s -e "%s;"',
                $mysqlConnection,
                $command->getCommand()
            );
        }

        if ($command instanceof MysqlRawCommand) {
            return sprintf(
                '%s %s',
                $mysqlConnection,
                $command->getCommand()
            );
        }

        if ($command instanceof MySqlDumpCommand) {
            return sprintf(
                'mysqldump --port=%s -u%s -p%s %s %s',
                strval($pim->getMysqlPort()),
                $pim->getDatabaseUser(),
                $pim->getDatabasePassword(),
                $pim->getDatabaseName(),
                $command->getCommand()
            );
        }

        return '';
    }
}