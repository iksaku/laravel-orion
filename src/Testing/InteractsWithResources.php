<?php

namespace Orion\Testing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

trait InteractsWithResources
{
    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param LengthAwarePaginator $paginator
     * @param array $data
     * @param bool $exact
     */
    protected function assertResourceListed($response, LengthAwarePaginator $paginator, array $data = [], bool $exact = true): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'from', 'last_page', 'path', 'per_page', 'to', 'total']
        ]);

        $expected = [
            'data' => $paginator->forPage($paginator->currentPage(), $paginator->perPage())->values()->map(function ($resource) use ($data) {
                $arrayRepresentation = is_array($resource) ? $resource : $resource->fresh()->toArray();
                $arrayRepresentation = array_merge($arrayRepresentation, $data);

                return $arrayRepresentation;
            })->toArray(),
            'links' => [
                'first' => $this->resolveResourceLink($paginator, 1),
                'last' => $this->resolveResourceLink($paginator, $paginator->lastPage()),
                'prev' => $paginator->currentPage() > 1 ? $this->resolveResourceLink($paginator, $paginator->currentPage() - 1) : null,
                'next' => $paginator->lastPage() > 1 ? $this->resolveResourceLink($paginator, $paginator->currentPage() + 1) : null,
            ],
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'from' => $paginator->firstItem(),
                'last_page' => $paginator->lastPage(),
                'path' => $this->resolvePath($this->resolveBasePath($paginator)),
                'per_page' => $paginator->perPage(),
                'to' => $paginator->perPage() > $paginator->total() ? $paginator->total() : $paginator->currentPage() * $paginator->perPage(),
                'total' => $paginator->total()
            ]
        ];

        if ((float) app()->version() >= 8.0) {
            $expected['meta']['links'] = $this->buildMetaLinks($paginator);
            $meta = $expected['meta'];
            ksort($meta);
            $expected['meta'] = $meta;
        }

        if ($exact) {
            $actual = json_decode($response->getContent(), true);
            $this->assertSame($expected, $actual);
        } else {
            $response->assertJson($expected);
        }
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param Model $resource
     * @param array $data
     */
    protected function assertResourceShown($response, $resource, $data = []): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => array_merge($resource->toArray(), $data)]);
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param string $table
     * @param array $databaseData
     * @param array $responseData
     */
    protected function assertResourceStored($response, string $table, array $databaseData, array $responseData): void
    {
        $response->assertStatus(201);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => $responseData]);
        $this->assertDatabaseHas($table, $databaseData);
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param string $table
     * @param array $originalDatabaseData
     * @param array $updatedDatabaseData
     * @param array $responseData
     */
    protected function assertResourceUpdated($response, string $table, array $originalDatabaseData, array $updatedDatabaseData, array $responseData): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => $responseData]);
        $this->assertDatabaseMissing($table, $originalDatabaseData);
        $this->assertDatabaseHas($table, $updatedDatabaseData);
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param Model $resource
     * @param array $data
     */
    protected function assertResourceDeleted($response, $resource, $data = []): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => array_merge($resource->toArray(), $data)]);
        $this->assertDatabaseMissing($resource->getTable(), [$resource->getKeyName() => $resource->getKey()]);
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param Model $resource
     * @param array $data
     */
    protected function assertResourceTrashed($response, $resource, $data = []): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => array_merge($resource->toArray(), $data)]);
        $this->assertDatabaseHas($resource->getTable(), [$resource->getKeyName() => $resource->getKey()]);
    }

    /**
     * @param \Illuminate\Testing\TestResponse|\Illuminate\Foundation\Testing\TestResponse $response
     * @param Model|\Illuminate\Database\Eloquent\SoftDeletes $resource
     * @param array $data
     */
    protected function assertResourceRestored($response, $resource, $data = []): void
    {
        $response->assertStatus(200);
        $response->assertJsonStructure(['data']);
        $response->assertJson(['data' => array_merge($resource->toArray(), $data)]);
        $this->assertDatabaseHas($resource->getTable(), [$resource->getDeletedAtColumn() => null]);
    }

    protected function resolveResourceLink(LengthAwarePaginator $paginator, int $page): string
    {
        $path = $this->resolvePath($this->resolveBasePath($paginator));
        return "{$path}?page={$page}";
    }

    protected function resolveBasePath(LengthAwarePaginator $paginator): string
    {
        if ((float) app()->version() >= 6.0) {
            return $paginator->path();
        }

        $paginatorDescriptor = $paginator->toArray();
        return $paginatorDescriptor['path'];
    }

    protected function resolvePath(string $basePath): string
    {
        return config('app.url')."/api/$basePath";
    }

    protected function buildMetaLinks(LengthAwarePaginator $paginator): array
    {
        $links = [
            $this->buildMetaLink(
                $paginator->currentPage() > 1 ? $this->resolveResourceLink($paginator, $paginator->currentPage() - 1) : null,
                'Previous',
                false
            )
        ];

        for ($page = 1; $page <= $paginator->lastPage(); $page++) {
            $links[] = $this->buildMetaLink(
                $this->resolveResourceLink($paginator, $page),
                $page,
                $paginator->currentPage() === $page
            );
        }

        $links[] = $this->buildMetaLink(
            $paginator->lastPage() > 1 ? $this->resolveResourceLink($paginator, $paginator->currentPage() + 1) : null,
            'Next',
            false
        );

        return $links;
    }

    protected function buildMetaLink(?string $url, $label, bool $active): array
    {
        return [
            'url' => $url,
            'label' => $label,
            'active' => $active
        ];
    }

    /**
     * @param Collection|array $items
     * @param string $path
     * @param int $currentPage
     * @param int $perPage
     * @param int|null $total
     * @return LengthAwarePaginator
     */
    protected function makePaginator($items, string $path, int $currentPage = 1, int $perPage = 15, int $total = null): LengthAwarePaginator
    {
        if (is_array($items)) {
            $items = collect($items);
        }

        return new LengthAwarePaginator($items, $total ?? $items->count(), $perPage, $currentPage, ['path' => $path]);
    }
}