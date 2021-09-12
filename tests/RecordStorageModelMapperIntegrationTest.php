<?php

use PHPUnit\Framework\TestCase;
use Walnut\Lib\ModelMapper\ModelBuilder;
use Walnut\Lib\ModelMapper\ModelBuilderFactory;
use Walnut\Lib\ModelMapper\ModelParser;
use Walnut\Lib\ModelMapper\ModelParserFactory;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperConfiguration;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperFactory;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessorFactory;
use Walnut\Lib\RecordStorage\ArrayDataSerializer\PhpArrayDataSerializer;
use Walnut\Lib\RecordStorage\KeyValueStorage\InMemoryKeyValueStorage;
use Walnut\Lib\RecordStorage\SerializedRecordStorage;

require_once __DIR__ . '/mocks.inc.php';

class RecordStorageModelMapperIntegrationTest extends TestCase {

	public const STORAGE_KEY = 'storage-key';
	public const KEY1 = 'key1';
	public const VALUE1 = 'value1';
	public const KEY2 = 'key2';
	public const VALUE2 = 'value2';
	public const VALUE2a = 'value2a';

	private function getFactory(): RecordStorageModelMapperFactory {
		$storage = new SerializedRecordStorage(
			new PhpArrayDataSerializer,
			new InMemoryKeyValueStorage([self::STORAGE_KEY => 'a:0:{}'])
		);

		return new RecordStorageModelMapperFactory(
			new RecordStorageModelMapperConfiguration(
				[MockModel::class => self::STORAGE_KEY]
			),
			new ArrayDataAccessorFactory($storage),
			new MockModelBuilderFactory,
			new MockModelParserFactory
		);
	}

	public function testInMemoryUsingPhpSerializer(): void {
		$factory = $this->getFactory();

		$mapper = $factory->getMapper(MockModel::class);

		$firstEntry = new MockModel(self::KEY1, self::VALUE1);
		$secondEntry = new MockModel(self::KEY2, self::VALUE2);

		$this->assertCount(0, $mapper->all());

		$mapper->store($firstEntry->id, $firstEntry);
		$this->assertCount(1, $mapper->all());

		$mapper->store($secondEntry->id, $secondEntry);
		$this->assertCount(2, $mapper->all());

		$updatedSecondEntry = new MockModel(self::KEY2, self::VALUE2a);
		$mapper->store($updatedSecondEntry->id, $updatedSecondEntry);
		$this->assertCount(2, $mapper->all());

		$this->assertNotNull($mapper->byId($firstEntry->id));
		$mapper->remove($firstEntry->id);
		$this->assertNull($mapper->byId($firstEntry->id));
		$this->assertCount(1, $mapper->all());
	}

}
