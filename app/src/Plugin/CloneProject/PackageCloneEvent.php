<?php

/*
 * Copyright (c) Terramar Labs
 *
 * For the full copyright and license information, please view the LICENSE file
 * that was distributed with this source code.
 */

namespace Terramar\Packages\Plugin\CloneProject;

use Terramar\Packages\Entity\Package;
use Terramar\Packages\Event\PackageEvent;

class PackageCloneEvent extends PackageEvent
{
    /**
     * @var string
     */
    private $repositoryPath;

    /**
     * Constructor.
     *
     * @param Package $package The updated package
     * @param string $repositoryPath The path to the cloned repository
     */
    public function __construct(Package $package, $repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;

        parent::__construct($package);
    }

    /**
     * @return string
     */
    public function getRepositoryPath()
    {
        return $this->repositoryPath;
    }
}
