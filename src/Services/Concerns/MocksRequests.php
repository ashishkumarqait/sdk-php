<?php

namespace LiveIntent\Services\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use LiveIntent\Exceptions\FileNotFoundException;
use LiveIntent\Exceptions\StubNotFoundException;
use LiveIntent\Exceptions\InvalidOptionException;

trait MocksRequests
{
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
     * Instruct the client to use fake responses.
     *
     * @param  callable|array  $callback
     * @return $this
     */
    public function fake($callback = null)
    {
        if ($this->tokenService) {
            $this->tokenService->fake();
        }

        if ($callback !== null) {
            return parent::fake($callback);
        }

        parent::fake(function (Request $request) {
            if ($this->shouldSaveRecordings) {
                throw new InvalidOptionException('Cannot use the `fake` option together with the `saveRecordings` option.');
            }

            $response = $this->findMockedResponse($request);

            if (! $response) {
                throw StubNotFoundException::factory($request);
            }

            return $this->response($response['body'], $response['status'], $response['headers']);
        });

        /** @psalm-suppress InvalidArgument */
        $this->pendingRequest()->stub($this->stubCallbacks);

        return $this;
    }

    /**
     * Save request/response pairs for later mocking.
     *
     * @return $this
     */
    public function saveSnapshots()
    {
        if ($this->tokenService) {
            $this->tokenService->saveSnapshots();
        }

        $this->record();

        $this->shouldSaveRecordings = true;

        return $this;
    }

    /**
     * Find a response stub that matches the request.
     *
     * @return null|\Illuminate\Http\Client\Response
     */
    public function findMockedResponse(Request $request)
    {
        if (! file_exists($this->recordingsFilepath)) {
            throw new FileNotFoundException("Recordings file not found. Path tried: `{$this->recordingsFilepath}`");
        }

        $recorded = unserialize(file_get_contents($this->recordingsFilepath));

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
                return [
                    $this->getRequestParts($pair[0]),
                    $this->getResponseParts($pair[1]),
                ];
            });

            $this->saveRequestResponsePairs($recorded);
        }
    }

    /**
     * Get the request parts that should be considered for comparison.
     *
     * @return array
     */
    public function getRequestParts(Request $request)
    {
        return [
            'method' => $request->method(),
            'url' => $request->url(),
            'body' => $this->getNormalizedRequestData($request),
        ];
    }

    /**
     * Get the response parts that should be considered for comparison.
     *
     * @return array
     */
    public function getResponseParts(Response $response)
    {
        return [
            'body' => $response->body(),
            'headers' => $response->headers(),
            'status' => $response->status(),
        ];
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

        $filepath = tap($this->recordingsFilepath, fn ($path) => touch($path));

        $previouslyRecorded = unserialize(file_get_contents($filepath)) ?: collect();

        $snapshots = collect($previouslyRecorded)
            ->concat($recording)
            ->keyBy(fn ($item) => $this->getChecksum($item[0]));

        file_put_contents($filepath, serialize($snapshots));
    }

    /**
     * Determine if two requests should be considered the same.
     *
     * @return bool
     */
    private function isSameRequest(array $a, Request $b)
    {
        return $this->getChecksum($a) === $this->getChecksum($this->getRequestParts($b));
    }

    /**
     * Get the request's data in a normalized way for comparison.
     *
     * @return array
     */
    private function getNormalizedRequestData(Request $request)
    {
        // Initialize data when not json
        $data = $request->data();

        if ($request->isJson()) {
            // Turn request data into an array
            $temp = json_decode((string) collect($request->data()), true);

            $keys = array_keys($temp);

            // Grab the first array key and json_decode it
            $data = json_decode(reset($keys), true);
        }

        $excludedKeys = ['version', 'client_id', 'client_secret'];

        return collect($data)->except($excludedKeys)->toArray();
    }

    /**
     * Get a checksum of an array so we can use it as a comparison key.
     *
     * @return string
     */
    private function getChecksum(array $parts)
    {
        return hash('crc32b', json_encode($parts));
    }
}
