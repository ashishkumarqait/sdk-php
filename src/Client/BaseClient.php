<?php

namespace LiveIntent\Client;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Request;
use LiveIntent\Services\TokenService;
use LiveIntent\Exceptions\FileNotFoundException;
use LiveIntent\Exceptions\StubNotFoundException;
use LiveIntent\Exceptions\InvalidOptionException;
use Illuminate\Http\Client\Factory as IlluminateClient;

class BaseClient extends IlluminateClient implements ClientInterface
{
    /**
     * The base url for all api requests issued by this client.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The default number of times a request should be retried.
     *
     * This may be overridden on a per request basis.
     *
     * @var int
     */
    private $tries = 1;

    /**
     * The default number of milliseconds to delay before retrying.
     *
     * This may be overridden on a per request basis.
     *
     * @var int
     */
    private $retryDelay = 100;

    /**
     * The default number of seconds to wait on a request before giving up.
     *
     * This may be overridden on a per request basis.
     *
     * @var int
     */
    private $timeout = 10;

    /**
     * Extra optional guzzle override options.
     *
     * @var array
     */
    private $guzzleOptions = [];

    /**
     * Whether the request/response pairs should be stored for later use.
     *
     * @var bool
     */
    private $shouldSaveRecordings = false;

    /**
     * The filepath to use for reading and storing responses.
     *
     * @var string
     */
    private $recordingsFilepath = 'tests/__snapshots__/snapshot';

    /**
     * The token service.
     *
     * @var \LiveIntent\Services\TokenService
     */
    private $tokenService;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(array $options = [])
    {
        $this->tries = $options['tries'] ?? $this->tries;
        $this->timeout = $options['timeout'] ?? $this->timeout;
        $this->baseUrl = $options['base_url'] ?? $this->baseUrl;
        $this->retryDelay = $options['retryDelay'] ?? $this->retryDelay;
        $this->guzzleOptions = $options['guzzleOptions'] ?? $this->guzzleOptions;
        $this->recordingsFilepath = $options['recordingsFilepath'] ?? $this->recordingsFilepath;
        $this->stubCallbacks = collect();

        $this->tokenService = new TokenService([
            'client_id' => $options['client_id'] ?? null,
            'client_secret' => $options['client_secret'] ?? null,
            'base_url' => $this->baseUrl,
        ], $this);
    }

    /**
     * Issue a request to the api.
     *
     * @param string $method
     * @param string $path
     * @param null|array $data
     * @param array $opts
     * @return \Illuminate\Http\Client\Response
     */
    public function request($method, $path, $data = null, $opts = [])
    {
        return $this
            ->baseUrl($this->baseUrl)
            ->withToken($this->tokenService->token(), $this->tokenService->tokenType())
            ->withBody(json_encode($data), 'application/json')
            ->acceptJson()
            ->timeout($this->timeout)
            ->retry($opts['tries'] ?? $this->tries, $opts['retryDelay'] ?? $this->retryDelay)
            ->withOptions($this->guzzleOptions)
            ->send($method, $path, $opts);
    }

    /**
     * Instruct the client to use fake responses.
     *
     * @param  callable|array  $callback
     * @return $this
     */
    public function fake($callback = null)
    {
        if ($callback !== null) {
            return parent::fake($callback);
        }

        return parent::fake(function (Request $request) {
            if ($this->shouldSaveRecordings) {
                throw new InvalidOptionException('Cannot use the `fake` option together with the `saveRecordings` option.');
            }

            $response = $this->findMockedResponse($request);

            if (! $response) {
                throw StubNotFoundException::factory($request);
            }

            return $this->response($response['body'], $response['status'], $response['headers']);
        });
    }

    /**
     * Find a response stub that matches the request.
     *
     * @return null|\Illuminate\Http\Client\Response
     */
    public function findMockedResponse(Request $request)
    {
        $filepath = $this->getFilepath();

        if (! file_exists($filepath)) {
            throw new FileNotFoundException("Recordings file not found. Path tried: `{$filepath}`");
        }

        $recorded = unserialize(file_get_contents($this->getFilepath()));

        $match = collect($recorded)->first(fn ($pair) => $this->isSameRequest($pair[0], $request));

        return $match[1] ?? null;
    }

    /**
     * Record a request response pair.
     *
     * @param  \Illuminate\Http\Client\Request  $request
     * @param  \Illuminate\Http\Client\Response  $response
     * @return void
     */
    public function recordRequestResponsePair($request, $response)
    {
        parent::recordRequestResponsePair($request, $response);

        if ($this->shouldSaveRecordings) {
            $recorded = collect($this->recorded)->map(function ($pair) {
                return [$pair[0], [
                    'body' => $pair[1]->body(),
                    'headers' => $pair[1]->headers(),
                    'status' => $pair[1]->status(),
                ]];
            });

            $this->saveRequestResponsePairs($recorded);
        }
    }

    /**
     * Save request/response pairs for later mocking.
     *
     * @return $this
     */
    public function saveRecordings()
    {
        $this->record();

        $this->shouldSaveRecordings = true;

        return $this;
    }

    /**
     * Get the filepath that test data should be stored at.
     *
     * @return string
     */
    public function getFilepath()
    {
        return $this->recordingsFilepath;
    }

    /**
     * Save recorded request response pairs to storage.
     *
     * @param array $recordings
     * @return void
     */
    protected function saveRequestResponsePairs(Collection $recording)
    {
        if ($this->stubCallbacks->isNotEmpty()) {
            throw new InvalidOptionException('Cannot use the `fake` option together with the `saveRecordings` option.');
        }

        $filepath = tap($this->getFilepath(), fn ($path) => touch($path));

        $previouslyRecorded = unserialize(file_get_contents($filepath)) ?: collect();

        $snapshots = collect($previouslyRecorded)
            ->concat($recording)
            ->keyBy(fn ($item) => $this->getRequestChecksum($item[0]));

        file_put_contents($filepath, serialize($snapshots));
    }

    /**
     * Determine if two requests should be considered the same.
     *
     * @return bool
     */
    private function isSameRequest(Request $a, Request $b)
    {
        return $this->getRequestChecksum($a) === $this->getRequestChecksum($b);
    }

    /**
     * Get a checksum of a request so we can compare if requests are the same.
     *
     * @return string
     */
    private function getRequestChecksum(Request $request)
    {
        // We need to some normalizing of the request data since the
        // incoming request and saved request look a bit different
        $data = $request->isJson()
              ? json_decode(collect($request->data())->flip()->first(), true)
              : $request->data();

        // ignore these keys when preforming the comparison
        $excludedKeys = ['version', 'client_id', 'client_secret'];

        $parts = [
            $request->method(),
            $request->url(),
            collect($data)->except($excludedKeys)->toArray(),
        ];

        return hash('crc32b', collect($parts)->map('json_encode')->join(''));
    }
}
