<?php

use AvocetShores\Conduit\ConduitService;
use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\DriverInterface;
use AvocetShores\Conduit\Dto\ConversationResponse;
use AvocetShores\Conduit\Enums\ResponseFormat;
use AvocetShores\Conduit\Enums\Role;
use AvocetShores\Conduit\Exceptions\AiModelNotSetException;
use AvocetShores\Conduit\Facades\Conduit;
use AvocetShores\Conduit\Middleware\MiddlewareInterface;

it('throws exception when a driver does not exist in the config', function () {

    Conduit::make('non_existent_driver');

})->throws(\InvalidArgumentException::class);

it('throws when a driver in the config does not implement the DriverInterface', function () {

    config(['conduit.drivers.fake' => \AvocetShores\Conduit\Tests\Fixtures\NoInterfaceDriver::class]);

    Conduit::make('fake');

})->throws(\InvalidArgumentException::class, 'Driver fake must implement DriverInterface');

it('throws when a driver in the config does not exist', function () {

    config(['conduit.drivers.fake' => 'NonExistentClass']);

    Conduit::make('fake');

})->throws(\InvalidArgumentException::class, 'Driver fake does not exist.');

it('makes the default driver when no driver is specified', function () {

    $conduit = Conduit::make();

    // Use reflection to get the driver property
    $reflection = new ReflectionClass($conduit);
    $property = $reflection->getProperty('driver');

    // Assert that the driver is an instance of the default driver
    expect($property->getValue($conduit))->toBeInstanceOf(config('conduit.drivers.'.config('conduit.default_driver')));
});

it('makes the specified driver when a driver is specified', function () {

    $conduit = Conduit::make('amazon_bedrock');

    // Use reflection to get the driver property
    $reflection = new ReflectionClass($conduit);
    $property = $reflection->getProperty('driver');

    // Assert that the driver is an instance of the specified driver
    expect($property->getValue($conduit))->toBeInstanceOf(config('conduit.drivers.amazon_bedrock'));
});

it('sets the model when it is optionally passed into the make function', function () {

    $conduit = Conduit::make('amazon_bedrock', 'claude-3-5-sonnet-v2');

    // Use reflection to get the driver property
    $reflection = new ReflectionClass($conduit);
    $property = $reflection->getProperty('driver');

    // Assert that the driver is an instance of the specified driver
    expect($property->getValue($conduit))->toBeInstanceOf(config('conduit.drivers.amazon_bedrock'));

    //
});

it('it can run when a valid model is set', function () {
    $driverMock = Mockery::mock(DriverInterface::class);
    $driverMock->shouldReceive('run')
        ->once()
        ->andReturn((new ConversationResponse('Hello from AI')));

    $service = new ConduitService($driverMock);

    $service->usingModel('gpt-4')
        ->withInstructions('You are a helpful assistant.')
        ->addMessage('Hello there!', Role::USER);

    $response = $service->run();

    expect($response)->toBeInstanceOf(ConversationResponse::class)
        ->and($response->output)->toBe('Hello from AI');
});

it('it throws an AiModelNotSetException if model is not set', function () {
    $driverMock = Mockery::mock(DriverInterface::class);
    $service = new ConduitService($driverMock);

    $service->run(); // Should throw
})->throws(AiModelNotSetException::class);

it('middleware pipeline executes in order and can mutate the context', function () {
    $driverMock = Mockery::mock(DriverInterface::class);

    // The driver should see modified instructions from the middleware
    $driverMock->shouldReceive('run')
        ->once()
        ->with(Mockery::on(function (AIRequestContext $context) {
            return $context->getInstructions() === 'Modified instructions';
        }))
        ->andReturn(new ConversationResponse('middleware-affected response'));

    $service = new ConduitService($driverMock);
    $service->usingModel('some-model')
        ->withInstructions('Original instructions');

    // Closure-based middleware:
    $closureMiddleware = function (AIRequestContext $context, Closure $next) {
        $context->setInstructions('Modified instructions');
        return $next($context);
    };

    // Push the middleware and run
    $service->pushMiddleware($closureMiddleware);
    $response = $service->run();

    expect($response)->toBeInstanceOf(ConversationResponse::class)
        ->and($response->output)->toBe('middleware-affected response');
});

it('class-based middleware is resolved from the container and invoked', function () {
    $driverMock = Mockery::mock(DriverInterface::class)
        ->shouldReceive('run')
        ->once()
        ->andReturn(new ConversationResponse('All good'))
        ->getMock();

    $middlewareMock = Mockery::mock(MiddlewareInterface::class);
    $middlewareMock->shouldReceive('handle')
        ->once()
        ->andReturnUsing(function (AIRequestContext $context, $next) {
            $context->addMessage('Injected by class middleware', Role::USER);

            return $next($context);
        });

    // Bind the mock to the container so "app('FakeMiddleware')" returns $middlewareMock
    $this->app->instance('FakeMiddleware', $middlewareMock);

    $service = new ConduitService($driverMock);
    $service->usingModel('some-model');

    // Push the string name, which is how ConduitService expects a class-based middleware
    $service->pushMiddleware('FakeMiddleware');

    $response = $service->run();

    expect($response->output)->toBe('All good');
});

it('enableJsonOutput sets the response format to JSON', function () {
    $driverMock = Mockery::mock(DriverInterface::class);
    $driverMock->shouldReceive('run')
        ->once()
        ->with(Mockery::on(function (AIRequestContext $context) {
            return $context->getResponseFormat() === ResponseFormat::JSON;
        }))
        ->andReturn(new ConversationResponse( 'Returned in JSON'));

    $service = new ConduitService($driverMock);
    $service->usingModel('json-model')
        ->enableJsonOutput();

    $response = $service->run();
    expect($response->output)->toBe('Returned in JSON');
});

it('enableStructuredOutput sets the format to STRUCTURED_SCHEMA and adds a schema to the context', function () {
    $driverMock = Mockery::mock(DriverInterface::class);

    $driverMock->shouldReceive('run')
        ->once()
        ->with(Mockery::on(function (AIRequestContext $context) {
            return $context->getResponseFormat() === ResponseFormat::STRUCTURED_SCHEMA
                && $context->getSchema()
                && $context->getSchema()->getName() === 'TestSchema';
        }))
        ->andReturn(new ConversationResponse('Structured response'));

    $schemaMock = new \AvocetShores\Conduit\Features\StructuredOutputs\Schema(
        name: 'TestSchema',
        description: 'Test schema',
        properties: []
    );

    $service = new ConduitService($driverMock);
    $service->usingModel('structured-model')
        ->enableStructuredOutput($schemaMock);

    $response = $service->run();
    expect($response->output)->toBe('Structured response');
});

it('using the Conduit facade works and calls ConduitService under the hood', function () {

    // Mock the response that the HTTP client would return
    \Illuminate\Support\Facades\Http::fake([
        '*' => \Illuminate\Support\Facades\Http::response([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Hello from facade'
                    ]
                ]
            ],
            'usage' => [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30
            ],
            'model' => 'gpt-4'
        ])
    ]);

    // If Conduit::make() internally resolves ConduitService, test the chain
    $response = Conduit::make('openai')
        ->usingModel('gpt-4')
        ->withInstructions('You are the best!')
        ->addMessage('Hello from facade test!', Role::USER)
        ->run();

    expect($response)->toBeInstanceOf(ConversationResponse::class)
        ->and($response->output)->toBe('Hello from facade')
        ->and($response->modelUsed)->toBe('gpt-4')
        ->and($response->usage->inputTokens)->toBe(10)
        ->and($response->usage->outputTokens)->toBe(20)
        ->and($response->usage->totalTokens)->toBe(30);
});
