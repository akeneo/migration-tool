<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\PimDetection;

/**
 * Abstract PIM representation.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractPim
{
    protected const PIM_ENTERPRISE_STANDARD = 'akeneo/pim-enterprise-standard';
    protected const PIM_COMMUNITY_STANDARD = 'akeneo/pim-community-standard';
    protected const PIM_COMMUNITY_DEV = 'akeneo/pim-community-dev';

    /** @var string */
    private $mysqlHost;

    /** @var int */
    private $mysqlPort;

    /** @var string */
    private $databaseName;

    /** @var string */
    private $databaseUser;

    /** @var string */
    private $databasePassword;

    /** @var bool */
    private $isEnterpriseEdition;

    /** @var null|string */
    private $enterpriseRepository;

    /** @var string */
    private $path;

    public function __construct(
        string $mysqlHost,
        int $mysqlPort,
        string $databaseName,
        string $databaseUser,
        string $databasePassword,
        bool $isEnterpriseEdition,
        ?string $enterpriseRepository,
        string $path
    ) {
        $this->mysqlHost = $mysqlHost;
        $this->mysqlPort = $mysqlPort;
        $this->databaseName = $databaseName;
        $this->databaseUser = $databaseUser;
        $this->databasePassword = $databasePassword;
        $this->isEnterpriseEdition = $isEnterpriseEdition;
        $this->enterpriseRepository = $enterpriseRepository;
        $this->path = $path;
    }

    abstract protected static function getPimVersionAllowed(): string;

    public function getMysqlHost(): string
    {
        return $this->mysqlHost;
    }

    public function getMysqlPort(): int
    {
        return $this->mysqlPort;
    }

    public function getDatabaseName(): string
    {
        return $this->databaseName;
    }

    public function getDatabaseUser(): string
    {
        return $this->databaseUser;
    }

    public function getDatabasePassword(): string
    {
        return $this->databasePassword;
    }

    public function isEnterpriseEdition(): bool
    {
        return $this->isEnterpriseEdition;
    }

    public function getEnterpriseRepository(): ?string
    {
        return $this->enterpriseRepository;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
