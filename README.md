# Laravel Conduit

> Work in progress. Not ready for use.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/avocet-shores/laravel-conduit/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/avocet-shores/laravel-conduit/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/avocet-shores/laravel-conduit.svg?style=flat-square)](https://packagist.org/packages/avocet-shores/laravel-conduit)

Laravel Conduit provides a clean, unified API for working with multiple AI providers. By abstracting away provider-specific details, 
Conduit makes it easy to swap AI models and providers without rewriting your code—so you can focus on building 
next‑level AI‑powered applications.

## Installation

You can install the package via composer:

```bash
composer require avocet-shores/laravel-conduit
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-conduit-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-conduit-config"
```

This is the contents of the published config file:

```php
return [
];
```

## Usage

```php
$conduit = new AvocetShores\Conduit();
echo $conduit->echoPhrase('Hello, AvocetShores!');
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
