<?php

namespace LiveIntent\Services;

use LiveIntent\Resource;
use LiveIntent\Exceptions;

abstract class AbstractResourceService extends AbstractService
{
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

    // /**
    //  */
    // public function createOrUpdate($attributes, $key = 'id')
    // {
    //     //
    // }

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

    // /**
    //  */
    // public function where($field, $operator, $value)
    // {
    //     //
    // }

    // /**
    //  */
    // public function delete($id)
    // {
    //     //
    // }

    /**
     * Send the request to the given URL.
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     *
     * @param string $method
     * @param string $url
     * @param array $options
     * @return \LiveIntent\Resource
     */
    public function request(string $method, string $url, array $options = [])
    {
        $response = parent::request($method, $url, $options);

        // TODO - handle multiple, handle other structures
        return $this->newResource($response->json()['output']);
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
}
