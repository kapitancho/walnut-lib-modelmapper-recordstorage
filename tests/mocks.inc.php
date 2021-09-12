<?php

use Walnut\Lib\ModelMapper\ModelBuilder;
use Walnut\Lib\ModelMapper\ModelBuilderFactory;
use Walnut\Lib\ModelMapper\ModelParser;
use Walnut\Lib\ModelMapper\ModelParserFactory;

final class MockModel {
	public function __construct(
		public /*readonly*/ string $id,
		public /*readonly*/ string $name
	) {}
}

/**
 * @implements ModelBuilder<MockModel>
 */
final class MockModelBuilder implements ModelBuilder {
	public function build(array $source): MockModel {
		return new MockModel($source['id'] ?? '', $source['name'] ?? '');
	}
}

/**
 * @implements ModelParser<MockModel>
 */
final class MockModelParser implements ModelParser {
	/**
	 * @param MockModel $source
	 * @return array
	 */
	public function parse(object $source): array {
		return ['id' => $source->id, 'name' => $source->name];
	}
}

final class MockModelBuilderFactory implements ModelBuilderFactory {

	public function getBuilder(string $className): ModelBuilder {
		return new MockModelBuilder;
	}

}

final class MockModelParserFactory implements ModelParserFactory {

	public function getParser(string $className): ModelParser {
		return new MockModelParser;
	}

}
