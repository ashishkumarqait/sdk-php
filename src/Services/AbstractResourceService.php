<?php

namespace LiveIntent\Services;

use LiveIntent\Resource;
use LiveIntent\Exceptions;
use Illuminate\Support\Traits\ForwardsCalls;

abstract class AbstractResourceService extends BaseService
{
    use ForwardsCalls;

    /**
     * The resource's base url. Usually it will just be `/entity`.
     *
     * @var string
     */
    protected $baseUrl;

    /**
     * The resource class for this entity.
     *
     * @var string
     */
    protected $objectClass;

    /**
     * Find a resource by its id.
     *
     * @param string|int $id
     * @return \LiveIntent\Resource
     */
    public function find($id)
    {
        return $this->request('get', $this->resourceUrl($id));
    }

    /**
     * Create a new resource.
     *
     * @param array|\stdClass|\LiveIntent\Resource $attributes
     * @return \LiveIntent\Resource
     */
    public function create($attributes)
    {
        $payload = (array) $attributes;

        if ($attributes instanceof Resource) {
            $payload = $attributes->getAttributes();
        }

        return $this->withJson($payload)->request('post', $this->baseUrl);
    }

    /**
     * Update an existing resource.
     *
     * @param array|\stdClass|\LiveIntent\Resource $attributes
     * @return \LiveIntent\Resource
     */
    public function update($attributes)
    {
        $payload = (array) $attributes;
        $id = $payload['id'] ?? null;

        if ($attributes instanceof Resource) {
            $id = $attributes->id;
            $payload = array_merge($attributes->getDirty(), ['version' => $attributes->version]);
        }

        if ($id === null) {
            throw Exceptions\InvalidArgumentException::factory($payload, 'Unable to find `id` for update operation');
        }

        return $this->withJson($payload)->request('post', $this->resourceUrl($id));
    }

    /**
     * Update an existing resource.
     *
     * @param array|\stdClass|\LiveIntent\Resource $attributes
     * @return \LiveIntent\Resource
     */
    public function createOrUpdate($attributes)
    {
        $payload = (array) $attributes;
        $id = $payload['id'] ?? null;

        if ($attributes instanceof Resource) {
            $id = $attributes->id;
            $payload = array_merge($attributes->getDirty(), ['version' => $attributes->version]);
        }

        if ($id && empty($attributes['version'])) {
            $payload['version'] = $this->find($id)->version;
        }

        return $this->withJson($payload)->request('post', $id ? $this->resourceUrl($id) : $this->baseUrl);
    }

    // /**
    //  */
    // public function createMany($attributeGroups)
    // {
    //     //
    // }

    // /**
    //  */
    // public function updateMany($attributeGroups)
    // {
    //     //
    // }

    /**
     * Delete a resource by its id.
     *
     * @param string|int $arg
     * @return \LiveIntent\Resource
     */
    public function delete($arg)
    {
        return $this->requestRaw('delete', $this->resourceUrl($arg));
    }

    /**
     * Issue a raw request without mapping the respon.se
     *
     * @return \Illuminate\Http\Client\Response
     */
    public function requestRaw(string $method, string $url, array $options = [])
    {
        return parent::request($method, $url, $options);
    }

    /**
     * Search for the resource.
     *
     * @return \stdClass
     */
    public function searchRaw(array $payload, array $options = [])
    {
        $response = $this->withJson($payload)->requestRaw(
            'post',
            $this->searchUrl(),
            $options
        );

        return (object) $response->json();
    }

    /**
     * Search for the resource.
     *
     * @return \Illuminate\Support\Collection
     */
    public function search(array $payload, array $options = [])
    {
        $results = data_get(
            $this->searchRaw($payload, $options),
            'output',
            []
        );

        return collect($results)->map(fn ($d) => $this->newResource($d));
    }

    /**
     * Send the request to the given URL.
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @param string $method
     * @param string $url
     * @param array $options
     *
     * @return \LiveIntent\Resource
     */
    public function request(string $method, string $url, array $options = [])
    {
        $response = $this->requestRaw($method, $url, $options);

        return $this->newResource($response->json()['output']);
    }

    /**
     * Get the search url for the resource.
     *
     * @return string
     */
    protected function searchUrl()
    {
        return $this->searchUrl ?? '/search'.$this->baseUrl;
    }

    /**
     * Get the resource's api url, usually it will be
     * in the form of `entity/{id}`.
     *
     * @param string|int $id
     * @return string
     */
    protected function resourceUrl($id)
    {
        return sprintf("%s/$id", $this->baseUrl);
    }

    /**
     * Create a new resource instance.
     *
     * @param array $body
     * @return \LiveIntent\Resource
     */
    private function newResource($body)
    {
        $class = $this->objectClass;

        return new $class($body);
    }

    /**
     * Create a new search query builder.
     *
     * @return \LiveIntent\Services\SearchQueryBuilder
     */
    private function newSearchQuery()
    {
        return new SearchQueryBuilder($this);
    }

    /**
     * Dynamically forward a call into the resource service or the query builder.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (method_exists($this->newSearchQuery(), $method)) {
            return $this->forwardCallTo($this->newSearchQuery(), $method, $parameters);
        }

        return parent::__call($method, $parameters);
    }
}
