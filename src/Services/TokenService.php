<?php

namespace LiveIntent\Services;

use Carbon\Carbon;
use Illuminate\Http\Client\Factory as Http;

class TokenService
{
    /**
     * The base url for all api requests issued by this service.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * The default number of times a request should be retried.
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
     * The http client to use.
     *
     * @var \Illuminate\Http\Client\Factory
     */
    private $http;

    /**
     * The client id.
     *
     * @var string
     */
    private $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    private $clientSecret;

    /**
     * The current access token.
     *
     * @var string
     */
    private $accessToken;

    /**
     * The current token type.
     *
     * @var string
     */
    private $tokenType = 'Bearer';

    /**
     * The expiration timestamp of the current token.
     *
     * @var \Carbon\Carbon
     */
    private $expiresAt;

    /**
     * The number of seconds before expiration to refresh tokens.
     *
     * @var int
     */
    private $bufferSeconds;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(array $options = [], Http $http = null)
    {
        $this->baseUrl = $options['base_url'];
        $this->clientId = $options['client_id'];
        $this->clientSecret = $options['client_secret'];

        $this->http = $http ?: new Http();
    }

    /**
     * Get a valid access token for use with the api, obtaining
     * a new one via the provided credentials if necessary.
     *
     * @return string
     */
    public function token()
    {
        if ($this->needsNewTokens()) {
            $this->refreshTokens();
        }

        return $this->accessToken;
    }

    /**
     * Get the token type of the current token.
     *
     * @return string
     */
    public function tokenType()
    {
        return $this->tokenType;
    }

    /**
     * Obtain a fresh set of tokens.
     *
     * @param array $opts
     * @return void
     */
    public function refreshTokens($opts = [])
    {
        $response = $this->http
            ->baseUrl($this->baseUrl)
            ->retry($opts['tries'] ?? $this->tries, $opts['retryDelay'] ?? $this->retryDelay)
            ->asForm()
            ->post('oauth/token', [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type' => 'client_credentials',
                'scope' => 'openid',
            ]);

        $payload = $response->throw()->json();

        $this->accessToken = $payload['access_token'];
        $this->tokenType = $payload['token_type'];
        $this->expiresAt = Carbon::now()->addSeconds($payload['expires_in'] - $this->bufferSeconds);
    }

    /**
     * Check if new tokens should be generated.
     *
     * @return bool
     */
    private function needsNewTokens()
    {
        if (! $this->accessToken) {
            return true;
        }

        return $this->expiresAt->isPast();
    }
}
