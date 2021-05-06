<?php

namespace LiveIntent\Services\Concerns;

use LiveIntent\Services\TokenService;
use Illuminate\Http\Client\PendingRequest;

trait AuthenticatesRequests
{
    /**
     * The token service to use for generating new tokens.
     *
     * @var \LiveIntent\Services\TokenService
     */
    protected $tokenService;

    /**
     * Set the token service to use.
     *
     * @param \LiveIntent\Services\TokenService $tokenService
     * @return void
     */
    public function setTokenService(TokenService $tokenService)
    {
        $this->tokenService = $tokenService;
    }

    /**
     * Prepare authentication for the request.
     *
     * @param PendingRequest $request
     * @return void
     */
    protected function authenticateRequest(PendingRequest $request)
    {
        $options = $request->mergeOptions();

        if (data_get($options, 'headers.Authorizaion') || data_get($options, 'cookies')) {
            return;
        }

        $request->withToken($this->tokenService->token(), $this->tokenService->tokenType());
    }
}
