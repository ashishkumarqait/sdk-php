<?php

namespace LiveIntent\Services\Concerns;

use LiveIntent\Exceptions;
use Illuminate\Http\Client\Response;

trait HandlesApiErrors
{
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
