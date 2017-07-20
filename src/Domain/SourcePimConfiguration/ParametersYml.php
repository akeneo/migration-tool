<?php

declare(strict_types=1);

namespace Akeneo\PimMigration\Domain\SourcePimConfiguration;

use Akeneo\PimMigration\Domain\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Representation of a parameters.yml file.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class ParametersYml implements File
{
    /** @var string */
    private $localPath;

    /** @var array */
    private $fullContent;

    public function __construct(string $localPath)
    {
        $this->localPath = $localPath;
        if (file_exists($localPath)) {
            $this->fullContent = Yaml::parse(file_get_contents($localPath))['parameters'];
        }
    }

    public function getDatabaseHost(): string
    {
        return $this->fullContent['database_host'];
    }

    public function getDatabasePort(): ?int
    {
        return $this->fullContent['database_port'] ?? 3306;
    }

    public function getDatabaseUser(): string
    {
        return $this->fullContent['database_user'];
    }

    public function getDatabasePassword(): string
    {
        return $this->fullContent['database_password'];
    }

    public function getDatabaseName(): string
    {
        return $this->fullContent['database_name'];
    }

    public function getPath(): string
    {
        return $this->localPath;
    }

    public static function getFileName(): string
    {
        return 'parameters.yml';
    }
}
