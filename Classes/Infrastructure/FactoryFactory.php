<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Infrastructure;

use ApiPlatform\JsonSchema\SchemaFactory;
use ApiPlatform\Metadata\Property\Factory\AttributePropertyMetadataFactory;
use ApiPlatform\Metadata\Property\Factory\PropertyInfoPropertyNameCollectionFactory;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
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

        return new SchemaFactory(
            null,
            $this->metadataCollectionFactory,
            new PropertyInfoPropertyNameCollectionFactory(new PropertyInfoExtractor(
                [$extractor],
                [$extractor],
                [new PhpDocExtractor()],
                [$extractor],
                [$extractor],
            )),
            new AttributePropertyMetadataFactory()
        );
    }
}
