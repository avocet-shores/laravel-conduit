<?php

use AvocetShores\Conduit\Facades\Conduit;

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
    expect($property->getValue($conduit))->toBeInstanceOf(config('conduit.drivers.' . config('conduit.default_driver')));
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
