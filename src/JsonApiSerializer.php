<?php

declare(strict_types=1);

namespace JsonApiMinimal;

use InvalidArgumentException;
use Traversable;
use JsonSerializable;

final class JsonApiSerializer
{
    /**
     * @param array<int, mixed>|Traversable<int, mixed> $items
     * @param array<string, mixed> $meta
     */
    public function serializeCollection(array|Traversable $items, string $type, array $meta = []): string
    {
        $normalizedItems = $items instanceof Traversable ? iterator_to_array($items, false) : $items;

        $data = [];
        foreach ($normalizedItems as $item) {
            $data[] = $this->toResourceObject($item, $type);
        }

        return json_encode(
            [
                'data' => $data,
                'meta' => $this->buildMeta(count($data), $meta),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    /**
     * @param array<string, mixed> $meta
     */
    public function serializeItem(mixed $item, string $type, array $meta = []): string
    {
        $data = $this->toResourceObject($item, $type);

        return json_encode(
            [
                'data' => $data,
                'meta' => $this->buildMeta(1, $meta),
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function toResourceObject(mixed $item, string $fallbackType): array
    {
        if (!is_array($item) && !is_object($item)) {
            throw new InvalidArgumentException('Each item must be an array or object.');
        }

        $payload = $this->normalizeItem($item);

        $id = $payload['id'] ?? null;
        if ($id === null || $id === '') {
            throw new InvalidArgumentException('Each item must contain a non-empty "id".');
        }

        $type = isset($payload['type']) && is_string($payload['type']) && $payload['type'] !== '' ? $payload['type'] : $fallbackType;

        unset($payload['id'], $payload['type']);

        return [
            'type' => $type,
            'id' => (string) $id,
            'attributes' => $payload,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeItem(mixed $item): array
    {
        if (is_array($item)) {
            return $item;
        }

        if ($item instanceof JsonSerializable) {
            $serialized = $item->jsonSerialize();
            if (!is_array($serialized)) {
                throw new InvalidArgumentException('JsonSerializable items must return an array.');
            }

            return $serialized;
        }

        /** @var array<string, mixed> $vars */
        $vars = get_object_vars($item);

        return $vars;
    }

    /**
     * @param array<string, mixed> $meta
     *
     * @return array<string, mixed>
     */
    private function buildMeta(int $count, array $meta): array
    {
        return array_merge($meta, ['count' => $count]);
    }
}
