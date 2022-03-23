## Development

To contribute to this package first you need to clone the repo and then copy the `.env` file.

### Clone Repo
```php
git clone git@github.com:LiveIntent/sdk-php.git
```

After you clone the repo, then you need to copy the `.env.example`

### Copy Env
```php
cp .env.example .env
```

After you copy the `.env.example` file then you can install the dependencies.

### Install Dependencies
```php
composer install
```

If you get an error message to install PHP 8.0 then install via homebrew

https://stitcher.io/blog/php-8-upgrade-mac

Once the upgrade is successful, then you can try to install the dependencies.

### Unit Tests

To run the unit tests

```php
composer test
```

The SDK uses Heimdall/Bifrost to authenticate so you need to create a Client and use that Client's Secret and Identifier. 

#### Create a Client in Bifrost

First you need to get a token from Bifrost using the `oauth/token` endpoint.

Grab the token and put it into the Authorize header in the Heimdall API.

You can use the Heimdall API to create your client.

```php
{
  "name": "testClient234",
  "environment": "server",
  "redirectUri": "https://qa-heimdall.liveintenteng.com/sign-in"
}
```

Take the response and copy the secret and identifier and put that into your `.env`

```php
{
  "secret": "<some secret>",
  "id": <some id>,
  "name": "<some name>",
  "identifier": "<some identifier>",
  "redirectUri": "https://qa-heimdall.liveintenteng.com/sign-in",
  "created": "<some datetime>",
  "updated": "<some datetime>"
}
```

#### Client Secret and Identifier

In the `.env` file

```php
CLIENT_ID=<identifier from response>
CLIENT_SECRET=<secret from response>

LI_BASE_URL=http://localhost:3000 // Pointing to Heimdall
```


### Linting
The installed linter will auto-format your code to comply with our agreed php coding standard.

To run the linter
```php
composer lint
```



