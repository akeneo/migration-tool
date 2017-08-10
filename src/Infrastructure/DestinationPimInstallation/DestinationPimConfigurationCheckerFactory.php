<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Infrastructure\DestinationPimInstallation;

use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimEditionChecker;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimSystemRequirementsChecker;
use Akeneo\PimMigration\Domain\DestinationPimInstallation\DestinationPimConfigurationChecker;

/**
 * Factory for Destination Pim Configuration Checker.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class DestinationPimConfigurationCheckerFactory
{
    public function createDestinationPimConfigurationChecker(
        DestinationPimEditionChecker $destinationPimEditionChecker,
        DestinationPimSystemRequirementsChecker $destinationPimSystemRequirementsChecker
    ): DestinationPimConfigurationChecker {
        return new DestinationPimConfigurationChecker($destinationPimEditionChecker, $destinationPimSystemRequirementsChecker);
    }
}