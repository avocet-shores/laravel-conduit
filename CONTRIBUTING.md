# Contributing to Laravel Conduit

Thank you for your interest in contributing to Laravel Conduit! We appreciate your time and effort in helping us improve and expand this package.

Below are some guidelines we ask you to follow when contributing.

## Table of Contents
1. [Getting Started](#getting-started)
2. [Code Style](#code-style)
3. [Testing](#testing)
4. [Contributing a New Driver](#contributing-a-new-driver)
5. [Pull Request Guidelines](#pull-request-guidelines)
6. [Security Vulnerabilities](#security-vulnerabilities)
7. [Resources](#resources)

---

## Getting Started

1. Fork this repository.
2. Clone your fork and create a new branch for your feature or bugfix:
   ```bash
   git checkout -b feature/my-new-feature
   ```
3. Make your changes in the new branch.

---

## Code Style

Laravel Conduit follows PHP's best practices and practices consistent with the Laravel ecosystem:
- Use PSR-12 coding standards.
- We also run automated style checks (see the GitHub Actions tab). If you prefer local validation, you can use [Laravel Pint](https://github.com/laravel/pint) or the same style tooling configured in this repo to ensure your code meets the style guidelines.

---

## Testing

Please add or update tests for any changes you make:
```bash
composer test
```
Make sure all tests pass before submitting your Pull Request.

---

## Contributing a New Driver

We welcome custom drivers for new AI services! However, before you start writing a new driver, please do one of the following to ensure we aren’t already working on the same thing:
- Open a new issue on GitHub outlining your proposed driver.
- Post a message in the relevant GitHub Discussion (if available).
- Reach out to the maintainer directly.

This helps avoid duplicating work and makes it easier for us to give feedback early. When you're ready, implement the `DriverInterface` and wire up your driver in the config. See the "Adding Your Own Driver" section of the [README](./README.md#adding-your-own-driver) for more details.

---

## Pull Request Guidelines

1. Keep your changes in a dedicated branch (e.g., `feature/new-ai-driver` or `fix/typo-in-logging`).
2. Ensure your Pull Request is against the `main` branch (unless otherwise specified).
3. Update or add tests as needed.
4. Provide a clear, concise description of your changes in the Pull Request.
5. If you are contributing a new driver or a large feature, include usage instructions and any relevant documentation updates.

---

## Security Vulnerabilities

If you discover a security vulnerability, please review our [Security Policy](../../security/policy) for instructions on how to report it.

---

## Resources

- [README.md](README.md) – for installation, usage, configuration, and advanced features.
- [CHANGELOG.md](CHANGELOG.md) – to see what has changed recently.

Thank you again for contributing to Laravel Conduit!
