# Contributing

Thanks for helping improve the DevCycle driver for Laravel Pennant! This project follows a typical Laravel package workflow and uses Pest for tests.

## Local setup

```bash
composer install
cp .env.example .env # if you need environment overrides for live DevCycle calls
```

## Running the test suite

```bash
composer test
```

## Static analysis

```bash
composer analyse
```

## Code style

Format using Pint's Laravel preset:

```bash
composer format
```

To check formatting without applying changes:

```bash
./vendor/bin/pint --test
```

## Pull request checklist

- Add or update tests when changing behavior.
- Keep the public API stable unless the change is explicitly breaking.
- Run `composer test`, `composer analyse`, and `./vendor/bin/pint --test` before opening a PR.
