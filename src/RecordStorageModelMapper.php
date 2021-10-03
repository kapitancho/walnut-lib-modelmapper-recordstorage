<?php

namespace Walnut\Lib\ModelMapper\RecordStorage;

use Walnut\Lib\IdentityGenerator\IdentityGenerator;
use Walnut\Lib\ModelMapper\ModelBuilder;
use Walnut\Lib\ModelMapper\ModelMapper;
use Walnut\Lib\ModelMapper\ModelParser;
use Walnut\Lib\RecordStorage\ArrayDataAccessor\ArrayDataAccessor;

/**
 * @template T of object
 * @implements ModelMapper<T>
 */
final class RecordStorageModelMapper implements ModelMapper, IdentityGenerator {
	/**
	 * @param ArrayDataAccessor $dataAccessor
	 * @param ModelBuilder<T> $modelBuilder
	 * @param ModelParser<T> $modelParser
	 */
	public function __construct(
		private /*readonly*/ ArrayDataAccessor $dataAccessor,
		private /*readonly*/ ModelBuilder $modelBuilder,
		private /*readonly*/ ModelParser $modelParser
	) {}

	/**
	 * @param string $entryId
	 * @return T|null
	 */
	public function byId(string $entryId): ?object {
		$entry = $this->dataAccessor->byKey($entryId);
		return $entry ? $this->modelBuilder->build($entry) : null;
	}

	/**
	 * @return T[]
	 */
	public function all(): array {
		/**
		 * @var T[]
		 */
		return array_map(fn(array $entry): object => $this->modelBuilder->build($entry),
			$this->dataAccessor->all());
	}

	/**
	 * @param string $entryId
	 * @param T $entry
	 */
	public function store(string $entryId, object $entry): void {
		$this->dataAccessor->store($entryId, $this->modelParser->parse($entry));
	}

	/**
	 * @param string $entryId
	 */
	public function remove(string $entryId): void {
		$this->dataAccessor->remove($entryId);
	}

	/**
	 * @return string
	 */
	public function generateId(): string {
		return (string)$this->dataAccessor->nextKey();
	}

	/**
	 * @param callable(T): bool $conditionChecker
	 * @return T[]
	 */
	public function byCondition(callable $conditionChecker): array {
		return array_values(
			array_filter(
				$this->all(),
				/**
			      * @param T $item
			      */
				static fn(object $item) => $conditionChecker($item)
			)
		);
	}

	public function exists(string $entryId): bool {
		return (bool)$this->dataAccessor->byKey($entryId);
	}
}
