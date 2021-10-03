<?php

use PHPUnit\Framework\TestCase;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperConfiguration;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperFactory;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessorFactory;
use Walnut\Lib\RecordStorage\RecordStorage;

require_once __DIR__ . '/mocks.inc.php';

final class MockRecordStorage implements RecordStorage {

	public function __construct(private /*readonly*/ TestCase $testCase) {}

	public function retrieveRecords(string $key): array {
		return [MockModel::class => [
			RecordStorageModelMapperTest::KEY1 => [
				'id' => RecordStorageModelMapperTest::KEY1,
				'name' => RecordStorageModelMapperTest::NAME1,
			],
			RecordStorageModelMapperTest::KEY2 => [
				'id' => RecordStorageModelMapperTest::KEY2,
				'name' => RecordStorageModelMapperTest::NAME2,
			]
		]][$key];
	}

	public function storeRecords(string $key, array $records): void {
		$this->testCase->assertNotCount(2, $records);
	}
}

final class RecordStorageModelMapperTest extends TestCase {

	public const KEY1 = 'key1';
	public const NAME1 = 'name1';
	public const KEY2 = 'key2';
	public const NAME2 = 'name2';
	public const INVALID_KEY = 'key3';

	public function getFactory(): RecordStorageModelMapperFactory {
		return new RecordStorageModelMapperFactory(
			new RecordStorageModelMapperConfiguration,
			new ArrayDataAccessorFactory(new MockRecordStorage($this)),
			new MockModelBuilderFactory,
			new MockModelParserFactory
		);
	}

	public function testMapper(): void {
		$mapper = $this->getFactory()->getMapper(MockModel::class);
		$this->assertEquals(self::NAME1, $mapper->byId(self::KEY1)->name);
		$this->assertTrue( $mapper->exists(self::KEY1));
		$this->assertFalse( $mapper->exists(self::INVALID_KEY));
		$this->assertCount(2,  $mapper->all());
		$this->assertEquals(1,  $mapper->generateId());
		$this->assertCount(1,  $mapper->byCondition(
			fn(object $target): bool =>
				$target->id === RecordStorageModelMapperTest::KEY1
		));
		$this->assertGreaterThan(0,
			$this->getFactory()->getIdentityGenerator(MockModel::class)->generateId());
	}

	public function testMapperStore(): void {
		$mapper = $this->getFactory()->getMapper(MockModel::class);
		$mapper->store(self::INVALID_KEY, new MockModel(self::INVALID_KEY, self::INVALID_KEY));
	}

	public function testMapperRemove(): void {
		$mapper = $this->getFactory()->getMapper(MockModel::class);
		$mapper->remove(self::KEY1);
	}

}
