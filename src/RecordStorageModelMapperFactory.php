<?php

namespace Walnut\Lib\ModelMapper\RecordStorage;

use Walnut\Lib\ModelMapper\ModelBuilderFactory;
use Walnut\Lib\ModelMapper\ModelMapperFactory;
use Walnut\Lib\ModelMapper\ModelParserFactory;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessorFactory;

final class RecordStorageModelMapperFactory implements ModelMapperFactory {
	/**
	 * @param RecordStorageModelMapperConfiguration $configuration
	 * @param ArrayDataAccessorFactory $accessorFactory
	 * @param ModelBuilderFactory $modelBuilderFactory
	 * @param ModelParserFactory $modelParserFactory
	 */
	public function __construct(
		private /*readonly*/ RecordStorageModelMapperConfiguration $configuration,
		private /*readonly*/ ArrayDataAccessorFactory $accessorFactory,
		private /*readonly*/ ModelBuilderFactory $modelBuilderFactory,
		private /*readonly*/ ModelParserFactory $modelParserFactory,
	) {}

	/**
	 * @template T of object
	 * @param class-string<T> $className
	 * @return RecordStorageModelMapper<T>
	 */
	public function getMapper(string $className): RecordStorageModelMapper {
		return new RecordStorageModelMapper(
			$this->accessorFactory->accessor(
				$this->configuration->storageKeyOf($className)
			),
			$this->modelBuilderFactory->getBuilder($className),
			$this->modelParserFactory->getParser($className),
		);
	}

}
