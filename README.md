# Laravelextends/requestapi

## Documentation

## Installation

Require this package  

```php
php composer.phpar require "laravelextends/requestapi:dev-master"
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
Integration\Api\IntegrationServiceProvider::class,
```


To publish the config use:

```php
php artisan vendor:publish --tag="config"
```

```php
    /**
     * @Integration(configure="user.login", power="sdfd", cache={"caching_time":0.5, "cache_name"="ssdf"})
     */
```