<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Command;

use ApiPlatform\JsonSchema\SchemaFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use Dsr\Optirez\Application\Location\LocationQuery;
use Dsr\Optirez\Controller\IbeEndpointsController;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;

class SchemaCommandController extends CommandController
{
    public function __construct(
        private readonly SchemaFactoryInterface $factory,
    ) {
        parent::__construct();
    }

    public function createCommand(): void
    {
        \Neos\Flow\var_dump($this->factory->buildSchema(LocationQuery::class));
    }
}
