# Installation

Install the package via composer:

```shell
composer require ljsharp/laravel-subby
```

Publish the configuration:

```shell
php artisan vendor:publish --tag=subby.config
```

Publish migrations:

```shell
php artisan vendor:publish --tag=subby.migrations
```

Migrate:

```shell
php artisan migrate
```

## Define main subscription

Since usually projects work with only one subscription or one primary, you have to set the tag for it in the
config `main_subscription_tag`. By default is `main`.

# Upgrade from v3.x to v4.x

This package need to be upgraded version by version to apply database changes.

Require in composer version 4 or greater. Publish and migrate:

```shell
php artisan vendor:publish --tag=subby.migrations.v4.0.0
php artisan migrate
```

### Breaking changes

Find breaking changes in [changelog](../CHANGELOG.md).

# Attach Subscriptions to model

**Laravel Subby** has been specially made for Eloquent. To add Subscription functionality to your User model just use
the `\Ljsharp\Subby\Traits\HasSubscriptions` trait like this:

```php
namespace App\Models;

use Ljsharp\Subby\Traits\HasSubscriptions;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSubscriptions;
}
```

That's it, we only have to use that trait in our User model or any other model! Now your users may subscribe to plans.
Then you can import package's models wherever you need them or extend them in your own models.
