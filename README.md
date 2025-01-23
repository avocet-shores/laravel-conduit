# Laravel Conduit

[![Latest Version on Packagist](https://img.shields.io/packagist/v/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Coverage Status](https://img.shields.io/codecov/c/github/avocet-shores/laravel-conduit?style=flat-square)](https://app.codecov.io/gh/avocet-shores/laravel-conduit/)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)

## Why Conduit?

Most AI packages today are tightly coupled with a single AI provider, locking you into their ecosystem (and their 
outages). Conduit offers a
flexible, provider-agnostic solution that allows you to switch between providers without changing your code. You can 
even create custom drivers for your own AI services, all while maintaining a consistent API. Want to add OpenAI as a 
backup to your on-prem model in case of an outage? Just add a fallback:

```php
$response = Conduit::make('on_prem_ai', 'my-model')
    ->withFallback('openai', 'gpt-4o')
    ->withInstructions('Write an inspirational message.')
    ->run();
```

## Key Features

- Unified API for various AI providers (Supports OpenAI and Amazon Bedrock out of the box, with more on the way)
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
    ->addMessage('Hello from Conduit!', Role::USER)
    ->run();
```

When a server or rate limit error occurs, Conduit will automatically switch to the specified fallback provider and 
model, keeping your AI-driven features running.

## Configuration

Conduit's configuration file allows you to set up your provider-specific authentication variables.

### OpenAI

To use OpenAI, you must provide your API key in the `.env` file:

```dotenv
OPENAI_API_KEY=your-api-key
```

### Amazon Bedrock

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

> Currently only supported by some of OpenAI's models.
> 
> Read more about structured outputs in [OpenAI's 
> documentation](https://platform.openai.com/docs/guides/structured-outputs).

Certain drivers and models support specifying a complete schema that the model is then forced to adhere to. If your 
chosen driver/model supports it, you can enable structured output by passing a `Schema` into `enableStructuredOutput()`:

```php
use AvocetShores\Conduit\Features\StructuredOutputs\Schema;

$response = Conduit::make('openai', 'gpt-4o')
    ->withInstructions('Please return a JSON object following the schema.')
    ->enableStructuredOutput(new Schema(/* ... */))
    ->run();
```

Conduit ensures that the schema request is formatted exactly how OpenAI expects, so your simple Schema definition:
    
```php
new Schema(
    name: 'research_paper_extraction',
    description: 'Extract the title, authors, and abstract from a research paper.',
    properties: [
        Input::string('title', 'The title of the research paper.'),
        Input::array('authors', 'The authors of the research paper.', [Input::string()]),
        Input::string('abstract', 'The abstract of the research paper.')    
    ]
);
```

...is automatically converted into the JSON format OpenAI requires:

```json
{
    "response_format": {
        "type": "json_schema",
        "json_schema": {
            "name": "research_paper_extraction",
            "description": "Extract the title, authors, and abstract from a research paper.",
            "schema": {
                "type": "object",
                "properties": {
                    "title": {
                        "description": "The title of the research paper.",
                        "type": "string"
                    },
                    "authors": {
                        "type": "array",
                        "items": {
                            "description": "The authors of the research paper.",
                            "type": "string"
                        }
                    },
                    "abstract": {
                        "description": "The abstract of the research paper.",
                        "type": "string"
                    }
                },
                "required": [
                    "title",
                    "authors",
                    "abstract"
                ],
                "additionalProperties": false
            },
            "strict": true
        }
    }
}
```

#### What if my model doesn't support structured outputs?

You can still enjoy automatic json decoding by adding `withJsonOutput()` and defining your own schema 
somewhere within your prompt. It's important to note, however, that this will not enforce the schema on the AI 
model. You will need to validate the response yourself in a subsequent step:

```php
$response = Conduit::make('openai', 'gpt-4o')
    ->withInstructions('Please return a JSON object in the format { "haiku": string }.')
    ->pushMiddleware(function (AIRequestContext $context, $next) {
        // Validate the response
        $response = $next($context);
        
        if (!isset($response->outputArray['haiku'])) {
            throw new Exception('The AI response did not match the expected schema.');
        }

        return $response;
    })
    ->withJsonOutput()
    ->run();
```


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

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Jared Cannon](https://github.com/jared-cannon)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
