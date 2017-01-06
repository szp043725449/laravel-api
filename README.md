# Laravel HaolyyService

## Documentation

## Installation

Require this package  

```php
php composer.phar require "haolyy/api:dev-master" -vvv
```

After adding the package, add the ServiceProvider to the providers array in `config/app.php`

```php
Haolyy\Api\HaolyyServiceProvider::class,,
```

Optionally you can register the HaolyyService facade:

```php
'haolyyServer'      => Haolyy\Api\Facades\HaolyyService::class,
```

To publish the config use:

```php
php artisan vendor:publish --tag="config"
```

sign Middleware:

```php
/**
 * @Middleware("hsign")
 */
```

