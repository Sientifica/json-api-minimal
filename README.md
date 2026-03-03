# json-api-minimal

Paquete PHP mínimo para serializar arreglos o colecciones de objetos a strings JSON:API.

## Instalación (Composer)

```bash
composer require mauricio/json-api-minimal
```

## Requisitos

- PHP 8.1+

## Especificación mínima implementada

- Documento JSON:API con clave raíz `data`.
- Incluye `meta` a nivel raíz con el campo mínimo `count`.
- Cada recurso incluye:
  - `type` (usa el parámetro `$type` o el `type` del item si existe).
  - `id` (obligatorio en cada item).
  - `attributes` (resto de campos del item, excluyendo `id` y `type`).
- Soporta:
  - `array` de items.
  - `Traversable` (ejemplo: `Collection`, `ArrayIterator`).
  - cada item puede ser `array`, objeto con propiedades públicas, o `JsonSerializable`.

## Uso

```php
<?php

use JsonApiMinimal\JsonApiSerializer;

$serializer = new JsonApiSerializer();

$items = [
    ['id' => 1, 'name' => 'Ada', 'email' => 'ada@example.com'],
    ['id' => 2, 'name' => 'Grace', 'email' => 'grace@example.com'],
];

$json = $serializer->serializeCollection($items, 'users', [
    'source' => 'import-job',
    'request_id' => 'req-1001',
]);

echo $json;
// {"data":[{"type":"users","id":"1","attributes":{"name":"Ada","email":"ada@example.com"}},{"type":"users","id":"2","attributes":{"name":"Grace","email":"grace@example.com"}}],"meta":{"source":"import-job","request_id":"req-1001","count":2}}
```

## API pública

- `serializeCollection(array|Traversable $items, string $type, array $meta = []): string`
- `serializeItem(mixed $item, string $type, array $meta = []): string`

## Tests

```bash
composer install
composer test
```

## Publicar en Packagist

1. Publica este repositorio en GitHub (o GitLab/Bitbucket soportado por Packagist).
2. Crea una cuenta en [packagist.org](https://packagist.org) y presiona "Submit".
3. Usa la URL del repositorio (ejemplo: `https://github.com/mauricio/json-api-minimal`).
4. Crea un tag semántico en git (ejemplo: `v1.0.0`) y súbelo:
   `git tag v1.0.0 && git push origin v1.0.0`
5. (Opcional recomendado) Configura el webhook de Packagist en tu repo para auto-actualización de versiones.

Nota: En Packagist, la versión se toma de tags git, no del archivo `composer.json`.

## Limitaciones intencionales (mínimo viable)

- No incluye `relationships`, `included`, `links`, `meta`, ni manejo de errores JSON:API.
- No transforma nombres de atributos.
- Requiere `id` por cada recurso.
