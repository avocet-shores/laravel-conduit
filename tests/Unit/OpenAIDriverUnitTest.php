<?php

use AvocetShores\Conduit\Contexts\AIRequestContext;
use AvocetShores\Conduit\Drivers\OpenAIDriver;
use AvocetShores\Conduit\Exceptions\ConduitException;
use AvocetShores\Conduit\Exceptions\ConduitProviderNotAvailableException;
use AvocetShores\Conduit\Exceptions\ConduitProviderRateLimitExceededException;

it('throws provider exception when the response is a server error', function () {
    $response = Http::response('', 500);

    Http::fake([
        '*' => $response,
    ]);

    $context = AIRequestContext::create();
    $context->setModel('test');

    $driver = new OpenAIDriver;

    $driver->run($context);
})->throws(ConduitProviderNotAvailableException::class);

it('throws rate limit exceeded when server responds with 429', function () {
    $response = Http::response('', 429);

    Http::fake([
        '*' => $response,
    ]);

    $context = AIRequestContext::create();
    $context->setModel('test');

    $driver = new OpenAIDriver;

    $driver->run($context);
})->throws(ConduitProviderRateLimitExceededException::class);

it('throws conduit exception when the response is a 401 error', function () {
    $response = Http::response('', 401);

    Http::fake([
        '*' => $response,
    ]);

    $context = AIRequestContext::create();
    $context->setModel('test');

    $driver = new OpenAIDriver;

    $driver->run($context);
})->throws(ConduitException::class);

it('rethrows generic exceptions as conduit exceptions', function () {
    $response = Http::response('', 200);

    Http::fake([
        '*' => $response,
    ]);

    $context = AIRequestContext::create();
    $context->setModel('test');

    // Mock the generateRequest method to throw an exception
    $driver = Mockery::mock(OpenAIDriver::class)
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();
    $driver->shouldReceive('generateRequest')->andThrow(new Exception('Test exception'));

    $driver->run($context);
})->throws(ConduitException::class);
