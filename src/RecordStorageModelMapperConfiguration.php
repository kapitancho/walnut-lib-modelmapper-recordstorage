<?php

namespace Walnut\Lib\ModelMapper\RecordStorage;

final class RecordStorageModelMapperConfiguration {
	/**
	 * @param array<class-string, string> $storageKeys
	 */
	public function __construct(
		private /*readonly*/ array $storageKeys = []
	) {}

	public function storageKeyOf(string $model): string {
		return $this->storageKeys[$model] ?? $model;
	}

}
