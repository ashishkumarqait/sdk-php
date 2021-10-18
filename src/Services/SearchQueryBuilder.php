<?php

namespace LiveIntent\Services;

use Illuminate\Support\Arr;

class SearchQueryBuilder
{
    /**
     * The resource service we are building a query for.
     *
     * @var \LiveIntent\Services\AbstractResourceService
     */
    private $service;

    /**
     * The search conditions.
     *
     * @var array
     */
    private $conditions = [];

    /**
     * The page to fetch.
     *
     * @var int
     */
    private $page = null;

    /**
     * The number of items to fetch.
     *
     * @var int
     */
    private $limit = null;

    /**
     * The return mode.
     *
     * @var string
     */
    private $returnMode = 'appended';

    /**
     * The fields to return.
     *
     * @var array
     */
    private $returnFields = [];

    /**
     * The search conditions.
     *
     * @var array
     */
    private static $operatorMap = [
        '=' => 'eq',
        '!=' => 'neq',
        '>' => 'gt',
        '>=' => 'gteq',
        '<' => 'lt',
        '<=' => 'lteq',
    ];

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(AbstractResourceService $service)
    {
        $this->service = $service;
    }

    /**
     * Add a where condition to the query.
     *
     * @param string $field
     * @param mixed $operator
     * @param mixed $value
     * @return static
     */
    public function where($field, $operator, $value = null): static
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->addCondition($field, $operator, $value);

        return $this;
    }

    /**
     * Limit the results to `n` entities.
     *
     * @return static
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * The page to fetch.
     *
     * @return static
     */
    public function page(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Only return the specified fields.
     *
     * @return static
     */
    public function only(string $fields): static
    {
        $this->returnMode = 'only';

        $this->returnFields = Arr::wrap($fields);

        return $this;
    }

    /**
     * Get the results.
     *
     * @return \Illuminate\Support\Collection
     */
    public function get()
    {
        return $this->service->search($this->buildPayload());
    }

    /**
     * Get the first result.
     *
     * @return \LiveIntent\Resource
     */
    public function first()
    {
        // the LSD minimum is currently 5, otherwise we'd just use 1
        return $this->limit(5)->get()->first();
    }

    /**
     * Get the raw search results.
     *
     * @return \stdClass
     */
    public function search()
    {
        return $this->service->searchRaw($this->buildPayload());
    }

    /**
     * Get the count of records matching the criteria.
     *
     * @return int
     */
    public function count()
    {
        return data_get($this->only('id')->search(), 'total');
    }

    /**
     * Check whether an entity exists with the given conditions.
     *
     * @return bool
     */
    public function exists()
    {
        return ! ! $this->only('id')->first();
    }

    /**
     * Check whether an entity exists with the given conditions.
     *
     * @return bool
     */
    public function doesntExist()
    {
        return ! $this->exists();
    }

    /**
     * Add a condtiion to the query.
     *
     * @return void
     */
    protected function addCondition(string $field, string $operator, mixed $value): void
    {
        $this->conditions[] = [
            'field' => $field,
            'operator' => static::$operatorMap[$operator] ?? $operator,
            'value' => $value,
        ];
    }

    /**
     * Build the payload of the request.
     *
     * @return (array|int|string)[]
     *
     * @psalm-return array{conditions?: array, page?: int, n?: int, returnMode?: string, return?: array}
     */
    protected function buildPayload(): array
    {
        return array_filter([
            'conditions' => $this->conditions,
            'page' => $this->page,
            'n' => $this->limit,
            'returnMode' => $this->returnMode,
            'return' => $this->returnFields,
        ]);
    }

    /**
     * Dump debug information.
     *
     * @return static
     */
    public function dump(): static
    {
        $this->service->pendingRequest()->dump();

        return $this;
    }
}
