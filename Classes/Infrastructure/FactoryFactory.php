<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Infrastructure;

use ApiPlatform\JsonSchema\Metadata\Property\Factory\SchemaPropertyMetadataFactory;
use ApiPlatform\JsonSchema\SchemaFactory;
use ApiPlatform\JsonSchema\TypeFactory;
use ApiPlatform\Metadata\Property\Factory\DefaultPropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\PropertyInfoPropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\PropertyInfoPropertyNameCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\AttributesResourceNameCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\ResourceClassResolver;
use Neos\Flow\Annotations as Flow;
use Symfony\Component\PropertyInfo\Extractor\PhpDocExtractor;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\PropertyInfo\PropertyInfoExtractor;

#[Flow\Scope('singleton')]
class FactoryFactory
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $metadataCollectionFactory,
    ) {
    }

    public function createSchemaFactory(): SchemaFactory
    {
        $extractor = new ReflectionExtractor();
        $propertyInfoExtractor = new PropertyInfoExtractor(
            [$extractor],
            [$extractor],
            [new PhpDocExtractor()],
            [$extractor],
            [$extractor],
        );

        return new SchemaFactory(
            null,
            $this->metadataCollectionFactory,
            new PropertyInfoPropertyNameCollectionFactory($propertyInfoExtractor),
            new SchemaPropertyMetadataFactory(
                new ResourceClassResolver(
                    new AttributesResourceNameCollectionFactory([])
                ),
                new PropertyInfoPropertyMetadataFactory($propertyInfoExtractor)
            )
        );
    }
}
