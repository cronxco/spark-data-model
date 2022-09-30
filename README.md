# Spark Data Model

Simple DataModel implementation package for Laravel using MySQL.

## Installation

To start using this package, install it with composer:

```php
composer require CronxCo/data-model
```

Publishing config and migrations:

```
php artisan vendor:publish --provider=CronxCo\DataModel\DataModelServiceProvider
```

This package uses Laravel's package auto-discovery feature, so there is no need to modify your `config/app.php` file.

## Configuration

Event Store logs are saved to your main database by default, but it is recommended to use a dedicated MySQL database for it. Once you create the database, make sure to set Event Store to use it:

First, add a dedicated connection to your `config/database.php` file:

```php
'connections' => [

        /*
        ...
        */

        'DataModel' => [
            'driver' => 'mysql',
            'host' => env('DATA_MODEL_HOST', 'localhost'),
            'port' => env('DATA_MODEL_PORT', '3306'),
            'database' => env('DATA_MODEL_DATABASE', 'data_model'),
            'username' => env('DATA_MODEL_USERNAME', 'root'),
            'password' => env('DATA_MODEL_PASSWORD', 'root'),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],

        /*
        ...
        */
]
```

Next, add required environment variables to your `.env` file:

```env
DATA_MODEL_CONNECTION="DataModel"
DATA_MODEL_DATABASE="your_data_model_database_name"
DATA_MODEL_TABLE="your_data_model_table_name"
DATA_MODEL_USERNAME="your_data_model_user_name"
DATA_MODEL_PASSWORD="your_data_model_user_password"
```

It is recommended to create a separate user for event store database, and remove UPDATE, DELETE, DROP permissions, to make sure your event store is append-only.

Next, run the migration to create default event store table:

```
php artisan migrate
```

## Usage

To start logging your events, append this line to your code where you wish the event to be logged:

```php
DataModel::add('event_name', $data);
```

Or using the DataModel helper function, which is just a wrapper for the facade:

```php
DataModel()->add('event_name', $data);
```

the `add()` method accepts four arguments:

- `$event_action`: name of your event, e.g. `user_created`, `email_sent`, `order_shipped`, etc.
- `$event_payload`: array of values to record. e.g. for `user_created` event, you can pass the array of attributes that this user was created with.
- `$target_id`: _(optional)_ ID of target model in your database. E.g., for `email_sent` event, you can pass `user_id` as `$target_id`. This helps in the future when you wish to fetch all events related to a particular user.
- `$before`: _(optional)_ array of values that were changed. E.g. for `user_updated` event, you may pass `$user->toArray()` to record attributes that were changed and their values before the change. _Note:_ the `add()` method automatically filters out only those keys that exist in `$event_payload` parameter to avoid unnecessary overhead.

Sometimes, certain events occur much more frequently than others, e.g. `user_created` and `user_logged_in`. To help with query performance, you can separate certain events to their dedicated tables by changing the `streams` array in `config/DataModel.php` file:

```php
'streams' => [
    'user_login_stream' => [
        'user_logged_in',
    ]
]
```

This will automatically create a dedicated `user_login_stream` table in your event store database when you try to add `user_logged_in` event. All events that are not defined in this array will be saved in the default event store table.

## Extra methods

### query()

Returns `Illuminate\Database\Eloquent\Builder` instance so you can perform any query on event store tables.

### get()

Gets all events from the default event store table. Returns a collection.

### get($event_name)

Gets all events of specific type from event store table. Automatically determines which table to search in. Returns a collection.

### stream($stream_name)

Sets dedicated table and returns `Illuminate\Database\Eloquent\Builder` instance so you can perform any query on event store tables.

## Exception handling

By default, DataModel suppresses any exceptions that occur during `add()` method call. You can disable this by changing `throw_exceptions` setting in `config/DataModel.php`:

```php
'throw_exceptions' => true,
```

## Testing

Run the tests with

```
vendor/bin/phpunit
```
