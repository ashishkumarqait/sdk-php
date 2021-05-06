<?php

namespace LiveIntent\Services\Concerns;

use Illuminate\Support\Collection;
use Illuminate\Http\Client\Request;
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
     * Get the request's data in a normalized way for comparison.
     *
     * @return array
     */
    private function getNormalizedRequestData(Request $request)
    {
        $data = $request->isJson()
            ? json_decode(collect($request->data())->flip()->first(), true)
            : $request->data();

        $excludedKeys = ['version', 'client_id', 'client_secret'];

        return collect($data)->except($excludedKeys)->toArray();
    }

    /**
     * Get a checksum of a request so we can compare if requests are the same.
     *
     * @return string
     */
    private function getRequestChecksum(Request $request)
    {
        $parts = [
            $request->method(),
            $request->url(),
            $this->getNormalizedRequestData($request),
        ];

        return hash('crc32b', collect($parts)->map('json_encode')->join(''));
    }
}
