<?php

namespace LiveIntent\Services;

use Carbon\Carbon;

class TokenService extends AbstractService
{
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
    public function __construct(array $options = [])
    {
        $this->options = $options;

        $this->clientId = $options['client_id'];
        $this->clientSecret = $options['client_secret'];

        $this->stubCallbacks = collect();
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
    public function refreshTokens()
    {
        $response = $this->asForm()->post('oauth/token', [
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
