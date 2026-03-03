<?php

declare(strict_types=1);

namespace JsonApiMinimal\Tests;

use Illuminate\Support\Collection;
use InvalidArgumentException;
use JsonApiMinimal\JsonApiSerializer;
use PHPUnit\Framework\TestCase;

final class JsonApiSerializerTest extends TestCase
{
    public function testSerializeCollectionFromArray(): void
    {
        $serializer = new JsonApiSerializer();
        $json = $serializer->serializeCollection(
            [
                ['id' => 1, 'name' => 'Ada'],
                ['id' => 2, 'name' => 'Grace'],
            ],
            'users'
        );

        $this->assertSame(
            '{"data":[{"type":"users","id":"1","attributes":{"name":"Ada"}},{"type":"users","id":"2","attributes":{"name":"Grace"}}],"meta":{"count":2}}',
            $json
        );
    }

    public function testSerializeCollectionFromTraversable(): void
    {
        $serializer = new JsonApiSerializer();
        $items = new \ArrayIterator([
            ['id' => 10, 'name' => 'Alice'],
        ]);

        $json = $serializer->serializeCollection($items, 'people');

        $this->assertSame(
            '{"data":[{"type":"people","id":"10","attributes":{"name":"Alice"}}],"meta":{"count":1}}',
            $json
        );
    }

    public function testSerializeCollectionFromLaravelCollection(): void
    {
        $serializer = new JsonApiSerializer();
        $items = new Collection([
            ['id' => 20, 'name' => 'Taylor'],
        ]);

        $json = $serializer->serializeCollection($items, 'users');

        $this->assertSame(
            '{"data":[{"type":"users","id":"20","attributes":{"name":"Taylor"}}],"meta":{"count":1}}',
            $json
        );
    }

    public function testSerializeCollectionWithCustomMeta(): void
    {
        $serializer = new JsonApiSerializer();

        $json = $serializer->serializeCollection(
            [
                ['id' => 1, 'name' => 'Ada'],
            ],
            'users',
            ['source' => 'tests', 'trace_id' => 'abc-123']
        );

        $this->assertSame(
            '{"data":[{"type":"users","id":"1","attributes":{"name":"Ada"}}],"meta":{"source":"tests","trace_id":"abc-123","count":1}}',
            $json
        );
    }

    public function testSerializeItemUsesItemTypeWhenProvided(): void
    {
        $serializer = new JsonApiSerializer();
        $json = $serializer->serializeItem(
            ['id' => 5, 'type' => 'admins', 'name' => 'Root'],
            'users'
        );

        $this->assertSame(
            '{"data":{"type":"admins","id":"5","attributes":{"name":"Root"}},"meta":{"count":1}}',
            $json
        );
    }

    public function testSerializeObjectItem(): void
    {
        $serializer = new JsonApiSerializer();
        $json = $serializer->serializeItem(new PublicObjectItem(99, 'Server'), 'nodes');

        $this->assertSame(
            '{"data":{"type":"nodes","id":"99","attributes":{"name":"Server"}},"meta":{"count":1}}',
            $json
        );
    }

    public function testSerializeJsonSerializableItem(): void
    {
        $serializer = new JsonApiSerializer();
        $json = $serializer->serializeItem(new SerializableItem(), 'users');

        $this->assertSame(
            '{"data":{"type":"users","id":"7","attributes":{"name":"Lin"}},"meta":{"count":1}}',
            $json
        );
    }

    public function testSerializeItemWithCustomMeta(): void
    {
        $serializer = new JsonApiSerializer();
        $json = $serializer->serializeItem(
            ['id' => 7, 'name' => 'Lin'],
            'users',
            ['requested_by' => 'qa']
        );

        $this->assertSame(
            '{"data":{"type":"users","id":"7","attributes":{"name":"Lin"}},"meta":{"requested_by":"qa","count":1}}',
            $json
        );
    }

    public function testSerializeItemWithoutIdThrowsException(): void
    {
        $serializer = new JsonApiSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each item must contain a non-empty "id".');
        $serializer->serializeItem(['name' => 'Missing id'], 'users');
    }

    public function testSerializeItemWithInvalidTypeThrowsException(): void
    {
        $serializer = new JsonApiSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Each item must be an array or object.');
        $serializer->serializeItem('invalid', 'users');
    }

    public function testJsonSerializableReturningNonArrayThrowsException(): void
    {
        $serializer = new JsonApiSerializer();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('JsonSerializable items must return an array.');
        $serializer->serializeItem(new InvalidSerializableItem(), 'users');
    }
}

final class PublicObjectItem
{
    public function __construct(public int $id, public string $name)
    {
    }
}

final class SerializableItem implements \JsonSerializable
{
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => 7,
            'name' => 'Lin',
        ];
    }
}

final class InvalidSerializableItem implements \JsonSerializable
{
    public function jsonSerialize(): string
    {
        return 'invalid';
    }
}
