# Laravelextends/requestapi

## Documentation

## Installation

Require this package  

```php
composer require laravelextends/requestapi
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
Integration\Api\IntegrationServiceProvider::class,
```


To publish the config use:

```php
php artisan vendor:publish --tag="config"
```

