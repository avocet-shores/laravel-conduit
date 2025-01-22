# Laravel Conduit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Coverage Status](https://img.shields.io/codecov/c/github/avocet-shores/laravel-conduit?style=flat-square)](https://app.codecov.io/gh/avocet-shores/laravel-conduit/)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)

Laravel Conduit offers a fluent, unified API for working with disparate AI providers. It focuses on conversational 
models 
(e.g., completions with OpenAI, converse with Amazon Bedrock) while ensuring a consistent interface across all 
providers. This makes it easy to switch providers or models without changing how your application consumes AI services.

In the event of provider issues, you can even set up fallback providers, ensuring your application automatically 
recovers from external outages.

## Key Features

- Unified API for various AI providers (Currently OpenAI and Amazon Bedrock)
- Automatic fallback to secondary drivers and/or models for improved reliability
- Easy extensibility: implement a simple interface to add custom drivers
- Pipeline-based middleware support
- Structured output and automatic JSON decoding

## Installation

1. You can install the package via composer:

   ```
   composer require avocet-shores/laravel-conduit
   ```

2. Optionally publish the configuration file:

   ```
   php artisan vendor:publish --provider="AvocetShores\Conduit\ConduitServiceProvider"
   ```

## Basic Usage

Below is a typical usage example with the Conduit facade:

```php
$response = Conduit::make('openai', 'gpt-4o')
    ->withInstructions(
     "Write a haiku about the user's input. " .
     'Return your response in the JSON format { "haiku": string }.'
    )
    ->addMessage('Laravel Conduit', Role::USER)
    ->withJsonOutput() // Automatically decodes the JSON into the response object
    ->run();

/**
 * Code flows in bridging  
 * Weave AI in Laravel  
 * Seamless paths converge
 */
echo $response->outputArray['haiku'];
```

1. Call the Conduit facade with `make('driver', 'model')`.
2. Provide optional instructions and messages.
3. Optionally enable jsonOutput to automatically decode the response.
4. Call `run()` to obtain a `ConversationResponse`.

### Setting a Fallback

To ensure your application remains online if your primary provider fails, you can set a fallback driver and model:

```php
use AvocetShores\Conduit\Enums\Role;

$response = Conduit::make('openai', 'gpt-4o')
    ->withFallback('amazon_bedrock', 'claude sonnet 3.5 v2')
    ->withInstructions('You are a helpful assistant.')
    ->addMessage('Hello from Conduit test!', Role::USER)
    ->run();
```

When a server or rate limit error occurs, Conduit will automatically switch to the specified fallback provider and 
model, keeping your AI-driven features running.

## Configuration

Conduit's configuration file allows you to set up your provider-specific authentication methods.

### OpenAI Configuration

To use OpenAI, you must provide your API key in the `.env` file:

```dotenv
OPENAI_API_KEY=your-api-key
```

### Amazon Bedrock Configuration

To use Amazon Bedrock, you'll need to provide your AWS credentials in the `.env` file. By default, Conduit 
references the same `.env` variables as the AWS SDK and other AWS services, but you can always point them to your own
custom variables by updating the `config/conduit.php` file.

```dotenv
AWS_ACCESS_KEY_ID=your-access-key-id
AWS_SECRET_ACCESS_KEY=your-secret-access-key
AWS_DEFAULT_REGION=your-region
```

## Advanced Usage

### Structured Outputs 

> Currently only supported by some of the OpenAI models

Certain drivers support specifying a complete schema that the model is then forced to adhere to. If your chosen driver 
supports 
it, you can 
enable structured output by passing a 
`Schema` into `enableStructuredOutput()`:

```php
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;

$response = Conduit::make('openai', 'gpt-4o')
    ->withInstructions('Please return a JSON object following the schema.')
    ->enableStructuredOutput(new Schema(/* ... */))
    ->run();
```

Note that if the driver does not support structured outputs, the request will still return whatever format the provider offers.

### Middleware and Pipelines

Conduit uses Laravel’s Pipelines to provide you with a powerful extension system via middleware. You can add 
middleware by providing either a class that implements `MiddlewareInterface` or a closure:

```php
$response = Conduit::make('openai', 'gpt-4o')
    ->pushMiddleware(function (AIRequestContext $context, $next) {
        // Example: remove SSN or credit card info from messages
        $messages = $context->getMessages();
        
        foreach ($messages as &$message) {
            $message->content = preg_replace('/\d{3}-\d{2}-\d{4}/', '[REDACTED_SSN]', $message->content);
            $message->content = preg_replace('/\b\d{16}\b/', '[REDACTED_CREDIT_CARD]', $message->content);
        }
        
        $context->setMessages($messages);

        // Continue down the pipeline
        return $next($context);
    })
    ->withInstructions('')
    ->run();
```

This can be useful for inspecting or modifying the AI request/response on its way through the pipeline. Or, for 
example, adding your own logging:

```php
$response = Conduit::make('openai', 'gpt-4o')
    ->pushMiddleware(function (AIRequestContext $context, $next) {
        // Log the request
        Log::info('AI Request Messages: ', $context->getMessages());
        
        // Add some metadata
        $context->setMetadata('request_time', now());

        // Continue down the pipeline
        return $next($context);
    })
    ->pushMiddleware(function (AIRequestContext $context, $next) {
        // Run after the AI request is completed
        $response = $next($context);
        
        // Log the response and use the metadata from the previous middleware
        Log::info('AI Response: ', [
            'response' => $response->outputArray,
            'execution_time' => now() - $context->getMetadata('request_time')
        ]);

        return $response;
    })
    ->withInstructions('')
    ->run();
```

## The `AIRequestContext` Object

The `AIRequestContext` object is passed through the pipeline to the designated driver, and contains all the data 
required for Conduit to handle the request. This includes the model, instructions, messages, responseFormat, and more.
It also includes a blank slate `metadata` array for any additional data you wish to pass through the pipeline. You can access
and modify this data as needed via its getter and setter methods.

If you're planning on creating your own driver, you'll need to familiarize yourself with the `AIRequestContext` 
object, as it's how you'll receive the data you need to make the AI request. Speaking of which...

### Adding Your Own Driver

Laravel Conduit is fully extensible to new or custom drivers. You only need to implement the 
`DriverInterface` and provide:

- A `run()` method to handle the AI request context.
- A return value of type `ConversationResponse`, ensuring a normalized data shape across drivers.

Finally, add your driver to the `drivers` array in the configuration file.

Example: 

```php
class MyCustomDriver implements DriverInterface
{
    public function run(AIRequestContext $context): ConversationResponse
    {
        // Make request to your custom AI service
        // Parse and return in a ConversationResponse
        return new ConversationResponse(
            outputArray: json_decode($response->getBody(), true)
        );
    }
}
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

1. Fork the repository.
2. Make your changes in a dedicated branch.
3. Write tests for your changes.
4. Submit a Pull Request.

We welcome new drivers, bug fixes, and improvements!

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jared Cannon](https://github.com/jared-cannon)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
