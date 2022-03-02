# LiveIntent SDK PHP

[![Test](https://github.com/LiveIntent/sdk-php/actions/workflows/run-tests.yml/badge.svg)](https://github.com/LiveIntent/sdk-php/actions/workflows/run-tests.yml)
[![Latest Stable Version](https://poser.pugx.org/liveintent/sdk-php/v/stable.svg)](https://packagist.org/packages/liveintent/sdk-php)
[![License](https://poser.pugx.org/liveintent/sdk-php/license)](//packagist.org/packages/liveintent/sdk-php)

The LiveIntent PHP SDK provides a convenient way to interact with the LiveIntent API in PHP applications.

## Installation

```
composer require liveintent/sdk-php
```

## Getting Started

Simple usage looks like this:

```php
$liveIntent = new \LiveIntent\LiveIntentClient([
    'client_id' => '<your-client-id>',
    'client_secret' => '<your-client-secret>',
]);

$lineItem = $liveIntent->lineItems->find(123);

echo $lineItem->id;
```

## Documentation

Should be written, and will include what services are available.

### Services

Currently available services can be found by looking [here](/src/Services/ServiceFactory.php#L27).

### Override example (todo)

```php
$response = $liveIntent->request()->get('/auth/user/4090');
$response->status();
```

## Request Options

When creating a client, you pay pass an array of options to further configure the client. If you require a per-request configuration, the individual service methods will also accept this optional array.

#### Global configuration example
```php
$liveIntent = new \LiveIntent\LiveIntentClient([
    'client_id' => '<your-client-id>',             // your client id
    'client_secret' => '<your-client-secret>',     // your client secret
    'tries' => 3,                                  // number of retries per request
    'timeout' => 10,                               // number of seconds to wait on a response before hangup
    'retryDelay' => 10,                            // number of seconds to wait between retries
    'base_url' => 'localhost:1234',                 // base url of the api
    'recordingsFilepath' => '/tmp/snapshotfile',   // filepath where test snapshots should be saved (see Testing)
    'guzzleOptions' => [],                         // additional guzzle options see (https://docs.guzzlephp.org/en/stable/request-options.html)
]);
```

#### Per-request override example
```php
$liveIntent->lineItems
    ->retry(3, 10)
    ->baseUrl('localhost:1234')
    ->withOptions([])
    ->find(123);
]);
```

## Logging

Will be handled soon.

## Testing

When testing you often want to mock external api calls, but you also want to be confident that those api calls will actually work.

To solve this, you may instruct the client to record the request/response pairs it makes. This allows you to run your tests against a live version of the api when necessary, and to reuse those same responses when mocking is acceptable.

#### To begin recording

```php
<?php

namespace Tests;

use LiveIntent\LiveIntentClient;
use PHPUnit\Framework\TestCase as PHPUnit;

class TestCase extends PHPUnit
{
    protected $liveIntentClient;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->liveIntentClient = new LiveIntentClient();

        $this->liveIntentClient->record();
    }
}
```

By default recordings will be saved in the file `tests/__snapshots__/snapshot`. You may reconfigure this via the options described [above](#request-options), but we do recommend including the snapshot in source control.

#### To use mocked responses

```php
<?php

namespace Tests;

use LiveIntent\LiveIntentClient;
use PHPUnit\Framework\TestCase as PHPUnit;

class TestCase extends PHPUnit
{
    protected $liveIntentClient;

    /**
     * This method is called before each test.
     */
    protected function setUp(): void
    {
        $this->liveIntentClient = new LiveIntentClient();

        // $this->liveIntentClient->record();

        $this->liveIntentClient->fake();
    }
}
```

**Note:** The `saveRecordings` and `fake` methods are mutually exclusive and may not be used together in the same run. It will probably be useful to introduce a flag  so you can switch between these options without modifying the code. We use an [environment variable](https://github.com/LiveIntent/sdk-php/blob/main/composer.json#L43).

### Alternative Methods of Mocking

The LiveIntent client inherits from Laravel's Http Client. Therefore, all the methods available to that client, will also be available here.

For detailed documentation see [here](https://laravel.com/docs/8.x/http-client#testing).

## Development

For information about how to contribute to this project please see
[here](/docs/development.md).
