<?php

namespace Walnut\Lib\ModelMapper\RecordStorage;

use Walnut\Lib\IdentityGenerator\IdentityGenerator;
use Walnut\Lib\ModelMapper\MappingNotAvailable;
use Walnut\Lib\ModelMapper\ModelBuilderFactory;
use Walnut\Lib\ModelMapper\ModelIdentityGeneratorFactory;
use Walnut\Lib\ModelMapper\ModelMapperFactory;
use Walnut\Lib\ModelMapper\ModelParserFactory;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessorFactory;

final class RecordStorageModelMapperFactory implements ModelMapperFactory, ModelIdentityGeneratorFactory {
	/**
	 * @param RecordStorageModelMapperConfiguration $configuration
	 * @param ArrayDataAccessorFactory $accessorFactory
	 * @param ModelBuilderFactory $modelBuilderFactory
	 * @param ModelParserFactory $modelParserFactory
	 */
	public function __construct(
		private readonly RecordStorageModelMapperConfiguration $configuration,
		private readonly ArrayDataAccessorFactory $accessorFactory,
		private readonly ModelBuilderFactory $modelBuilderFactory,
		private readonly ModelParserFactory $modelParserFactory,
	) {}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return RecordStorageModelMapper<T>
	 * @throw MappingNotAvailable
	 */
	private function load(string $className): RecordStorageModelMapper {
		return new RecordStorageModelMapper(
			$this->accessorFactory->accessor(
				$this->configuration->storageKeyOf($className)
			),
			$this->modelBuilderFactory->getBuilder($className),
			$this->modelParserFactory->getParser($className),
		);
	}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return RecordStorageModelMapper<T>
	 * @throw MappingNotAvailable
	 */
	public function getMapper(string $className): RecordStorageModelMapper {
		return $this->load($className);
	}

	/**
	 * @param class-string $className
	 * @return IdentityGenerator
	 * @throw MappingNotAvailable
	 */
	public function getIdentityGenerator(string $className): IdentityGenerator {
		return $this->load($className);
	}
}
