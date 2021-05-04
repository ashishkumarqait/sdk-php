<?php

namespace LiveIntent\Services;

use LiveIntent\Resource;
use LiveIntent\Exceptions;
use Illuminate\Http\Client\Response;
use LiveIntent\Client\ClientInterface;

abstract class AbstractService
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
     * The client to use for issueing requests.
     */
    private ClientInterface $client;

    /**
     * Create a new service instance.
     *
     * @return void
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Find a resource by its id.
     *
     * @param string|int $id
     * @param array $opts
     * @return \LiveIntent\Resource
     */
    public function find($id, $opts = [])
    {
        return $this->request('get', $this->resourceUrl($id), null, $opts);
    }

    /**
     * Create a new resource.
     *
     * @param array|\stdClass|\LiveIntent\Resource $attributes
     * @param array $opts
     * @return \LiveIntent\Resource
     */
    public function create($attributes, $opts = [])
    {
        $payload = (array) $attributes;

        if ($attributes instanceof Resource) {
            $payload = $attributes->getAttributes();
        }

        return $this->request('post', $this->baseUrl, $payload, $opts);
    }

    /**
     * Update an existing resource.
     *
     * @param array|\stdClass|\LiveIntent\Resource $attributes
     * @param array $opts
     * @return \LiveIntent\Resource
     */
    public function update($attributes, $opts = [])
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

        return $this->request('post', $this->resourceUrl($id), $payload, $opts);
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
     * Get the client used by the service to make requests.
     *
     * @return \LiveIntent\Client\ClientInterface
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Make a request to the api.
     *
     * @param null|array $params
     * @param array $opts
     * @return \LiveIntent\Resource
     */
    protected function request(string $method, string $path, $params = null, $opts = [])
    {
        $response = $this->getClient()->request($method, $path, $params, $opts);

        $this->handleErrors($response);

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

    /**
     * Check for api errors and handle them accordingly.
     *
     * @throws \LiveIntent\Exceptions\AbstractRequestException
     *
     * @return void
     */
    private function handleErrors(Response $response)
    {
        if ($response->successful()) {
            return;
        }

        throw $this->newApiError($response);
    }

    /**
     * Create the proper exception based on an error response.
     *
     * @return \LiveIntent\Exceptions\AbstractRequestException
     */
    private function newApiError(Response $response)
    {
        switch ($response->status()) {
            case 400:
            case 422:
                return Exceptions\InvalidRequestException::factory($response);
            case 401:
                return Exceptions\AuthenticationException::factory($response);
            case 403:
                return Exceptions\AuthorizationException::factory($response);
            case 404:
            case 410:
                return Exceptions\NotFoundException::factory($response);
            case 409:
                return Exceptions\ConflictException::factory($response);
            case 429:
                return Exceptions\NotFoundException::factory($response);
            case 500:
            case 502:
            case 503:
            case 504:
                return Exceptions\ServerErrorException::factory($response);
            default:
                return Exceptions\UnknownApiException::factory($response);
        }
    }
}
