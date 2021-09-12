<?php

use PHPUnit\Framework\TestCase;
use Walnut\Lib\JsonSerializer\PhpJsonSerializer;
use Walnut\Lib\ModelMapper\ModelBuilder;
use Walnut\Lib\ModelMapper\ModelBuilderFactory;
use Walnut\Lib\ModelMapper\ModelParser;
use Walnut\Lib\ModelMapper\ModelParserFactory;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperConfiguration;
use Walnut\Lib\ModelMapper\RecordStorage\RecordStorageModelMapperFactory;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessorFactory;
use Walnut\Lib\RecordStorage\ArrayDataSerializer\JsonArrayDataSerializer;
use Walnut\Lib\RecordStorage\ArrayDataSerializer\PhpArrayDataSerializer;
use Walnut\Lib\RecordStorage\KeyValueStorage\InMemoryKeyValueStorage;
use Walnut\Lib\RecordStorage\SerializedRecordStorage;

class Client {
	public function __construct(
		public /*readonly*/ string $id,
		public /*readonly*/ string $name
	) {}
}

/**
 * @implements ModelBuilder<Client>
 * @implements ModelParser<Client>
 */
final class ClientSerializer implements ModelBuilder, ModelParser {

	/**
	 * @param array $source
	 * @return Client
	 */
	public function build(array $source): object {
		return new Client(
			$source['id'] ?? '',
			$source['name'] ?? ''
		);
	}

	/**
	 * @param Client $source
	 * @return array
	 */
	public function parse(object $source): array {
		return [
			'id' => $source->id,
			'name' => $source->name
		];
	}
}

final class MapperFactory implements ModelBuilderFactory, ModelParserFactory {

	/**
	 * @param string $className
	 * @return ModelBuilder&ModelParser
	 */
	private function getSerializer(string $className): object /*ModelBuilder&ModelParser*/ {
		return match($className) {
			Client::class => new ClientSerializer,
			default => throw new RuntimeException("Unknown class $className")
		};
	}

	public function getBuilder(string $className): ModelBuilder {
		return $this->getSerializer($className);
	}

	public function getParser(string $className): ModelParser {
		return $this->getSerializer($className);
	}

}

class RecordStorageModelMapperRealTest extends TestCase {

	public function testReal(): void {
		$storage = new SerializedRecordStorage(
			new JsonArrayDataSerializer(
				new PhpJsonSerializer
			),
			new InMemoryKeyValueStorage(['clients' => '{}'])
		);
		$serializerFactory = new MapperFactory;
		$configuration = new RecordStorageModelMapperConfiguration(
			[Client::class => 'clients']
		);

		$modelMapperFactory = new RecordStorageModelMapperFactory(
			$configuration,
			new ArrayDataAccessorFactory($storage),
			$serializerFactory,
			$serializerFactory
		);

		$mapper = $modelMapperFactory->getMapper(Client::class);

		$firstClient = new Client('cl-1', 'Client 1');
		$secondClient = new Client('cl-2', 'Client 2');

		$this->assertCount(0, $mapper->all());

		$mapper->store($firstClient->id, $firstClient);
		$this->assertCount(1, $mapper->all());

		$mapper->store($secondClient->id, $secondClient);
		$this->assertCount(2, $mapper->all());

		$updatedSecondClient = new Client('cl-2', 'Client 2 new name');

		$mapper->store($updatedSecondClient->id, $updatedSecondClient);
		$this->assertCount(2, $mapper->all());

		$this->assertNotNull($mapper->byId($firstClient->id));
		$this->assertInstanceOf(Client::class, $mapper->byId($firstClient->id));
		$this->assertEquals('Client 1', $mapper->byId($firstClient->id)->name);
		$this->assertEquals('Client 2 new name', $mapper->byId($updatedSecondClient->id)->name);
		$mapper->remove($firstClient->id);
		$this->assertNull($mapper->byId($firstClient->id));
		$this->assertCount(1, $mapper->all());
	}

}

