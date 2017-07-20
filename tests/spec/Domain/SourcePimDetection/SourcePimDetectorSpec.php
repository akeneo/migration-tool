<?php

namespace spec\Akeneo\PimMigration\Domain\SourcePimDetection;

use Akeneo\PimMigration\Domain\SourcePimConfiguration\ComposerJson;
use Akeneo\PimMigration\Domain\SourcePimConfiguration\SourcePimConfiguration;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePimDetectionException;
use Akeneo\PimMigration\Domain\SourcePimDetection\SourcePimDetector;
use Ds\Map;
use PhpSpec\ObjectBehavior;

/**
 * Spec for SourcePimDetector.
 *
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 */
class SourcePimDetectorSpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(SourcePimDetector::class);
    }

    public function it_throws_an_exception_if_it_is_not_a_standard(
        ComposerJson $composerJson,
        SourcePimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('a-repo');
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM name should be either akeneo/pim-community-standard or either akeneo/pim-enterprise-standard, currently a-repo'
            ))->during('detect', [$sourcePimConfiguration]);
    }

    public function it_throws_an_eception_if_it_is_not_a_one_dot_seven(
        ComposerJson $composerJson,
        SourcePimConfiguration $sourcePimConfiguration
    ) {
        $composerJson->getRepositoryName()->willReturn('akeneo/pim-community-standard');
        $composerJson->getDependencies()->willReturn(new Map(['akeneo/pim-community-dev' => '~1.6']));
        $sourcePimConfiguration->getComposerJson()->willReturn($composerJson);

        $this->shouldThrow(
            new SourcePimDetectionException(
                'Your PIM version should be 1.7'
            ))->during('detect', [$sourcePimConfiguration]);
    }
}
