# Laravel Arpoone Documentation
## Introduction
**Laravel Arpoone** is a package that integrates with the Arpoone SMS gateway, allowing you to send SMS messages from your Laravel application using the notification system. This package supports multi-tenant applications, webhook support, and optional logging of SMS messages in the database.

## Requirements
 - PHP 8.0+
 - Laravel 8.0+
 - Guzzle HTTP Client
 - libphonenumber library for phone number validation

## Installation
1. **Install the package via Composer:**  
   ```bash
   composer require controlink/laravel-arpoone
   ```
2. **Publish the configuration file:**  
   After installation, publish the configuration file to customize the package behavior.
   ```bash
   php artisan vendor:publish --tag=arpoone-config
   ```

3. **Publish the migrations (optional):**
    If you are using multi-tenant or want to log SMS messages, publish the migrations:
    ```bash
    php artisan vendor:publish --tag=arpoone-migrations
    ```
    Then, run the migrations:
    ```bash
    php artisan migrate
    ```
4. **Set environment variables:**
    If you are not using multi-tenant mode, In your `.env` file, add the necessary variables:
    ```env
    ARPOONE_API_KEY=your-api-key-here
    ARPOONE_ORGANIZATION_ID=your-organization-id-here
    ARPOONE_SENDER=your-sender-id-here
    ```
    If you are using multi-tenant mode, you can set the tenant ID dynamically when sending notifications.
## Configuration
After publishing the configuration file, you can modify the `config/arpoone.php` file to customize the behavior of the package.
```php
return [

    // The base URL of the Arpoone API. This should point to the appropriate version of the API you're using.
    // You can override it via the .env file with the variable ARPOONE_URL.
    'url' => env('ARPOONE_URL', "https://api.arpoone.com/v1.1/"),

    // The API key for authenticating requests to the Arpoone API.
    // This must be set in your .env file using the ARPOONE_API_KEY variable.
    'api_key' => env('ARPOONE_API_KEY', null),

    // The ID of the organization in Arpoone that is sending the SMS messages.
    // It should be set in your .env file using the ARPOONE_ORGANIZATION_ID variable.
    'organization_id' => env('ARPOONE_ORGANIZATION_ID', null),

    // The default sender name or number for the SMS messages.
    // You can configure this via the ARPOONE_SENDER variable in your .env file.
    'sender' => env('ARPOONE_SENDER', null),

    // If true, SSL certificates will be verified when sending requests to the API.
    // This is a security feature to ensure that the API connection is secure.
    'verify_ssl' => true,

    // If true, the package will migrate a database table to store the configuration settings.
    // This is useful if you want to store the configuration settings in the database to make them dynamic, or multi-tenant.
    'multi_tenant' => false,

    // The name of the database table to store the configuration settings.
    // This is only used if the multi_tenant option is set to true.
    'table_name' => 'arpoone_configuration',

    // The tenant model to use for multi-tenant applications. (e.g., App\Models\User)
    // This is only used if the multi_tenant option is set to true.
    'tenant_model' => '',

    // If true, the table will be created with a tenant column to store some type of identifier for the tenant.
    // This is only used if the multi_tenant option is set to true.
    'use_tenant_column' => false,

    // The name of the column to store the tenant identifier.
    // This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'tenant_column_name' => 'tenant_id',

    //If true, the sms sent will be logged in the database
    //This is useful if you want to keep track of the sms sent
    'log_sms' => false,

    //The name of the table to store the sms logs
    //This is only used if the log_sms option is set to true
    'sms_log_table' => 'arpoone_sms_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'sms_log_tenant_column_name' => 'tenant_id',

    //If true, the package will apply webhooks routes to handle the sms status
    //This is useful if you want to keep track of the sms status, and update your database accordingly
    'webhooks' => false,
];
```
## Key Configuration Options:
 - url: Base URL for the Arpoone API.
 - api_key: API key for authenticating with Arpoone.
 - organization_id: Your Arpoone organization ID.
 - sender: Default sender for SMS messages.
 - verify_ssl: Option to verify SSL certificates (set to false for development).
 - multi_tenant: Set to true if your application is multi-tenant.
 - log_sms: Set to true to log sent SMS messages in the database.
 - webhooks: Set to true if you want to handle SMS status via webhooks.

## Usage
### Sending SMS Notifications
To send SMS using Laravel’s notification system, you need to define a notification class that uses the Arpoone channel. Here's an example:

1. **Create a Notification Class:**  
   ```bash
   php artisan make:notification SendSmsNotification
   ```
2. **Modify the Notification Class:**

    Inside the `SendSmsNotification` class, define the `toArpoone` method to send SMS:
    ```php
    use Illuminate\Notifications\Notification;
    
    class SendSmsNotification extends Notification
    {
         public function via($notifiable)
         {
              return [\Controlink\LaravelArpoone\Channels\Arpoone::class];
         }
    
         public function toArpoone($notifiable)
         {
              return [
                'content' => 'Your SMS message content goes here!',
              ];
         }
    }
    ```

3. **Send the Notification:**

In your Notifiable model (such as User), ensure that you have a method `routeNotificationForArpoone` which returns the phone number:
```php
public function routeNotificationForArpoone()
{
    return $this->phone_number;
}
```
Then, to send the SMS:
```php
$user = User::find(1);
$user->notify(new SendSmsNotification());
```
**Note:** If you do not create a `routeNotificationForArpoone` method in your Notifiable model, the package will try to use a column named `phone_number` if it exists, or it will throw an exception.

## Multi-Tenant Support
If you are using multi-tenant functionality, ensure the following:

 - Set multi_tenant to true in the configuration.
 - Define the tenant_model and ensure the tenant identifier is available.
 - When sending a notification in a multi-tenant application, pass the tenant ID when initializing the Arpoone channel:

```php
$tenantId = $user->tenant_id;
$notification = new \Controlink\LaravelArpoone\Channels\Arpoone($tenantId);
$notification->send($user, new SendSmsNotification());
```

## Logging SMS
If SMS logging is enabled, set log_sms to true in the configuration. The package will log sent SMS in the database table defined in sms_log_table.

## Webhook Support
To track SMS statuses using webhooks, set webhooks to true in the configuration.
The package will automatically create a route to handle the webhook and will also add the webhooks necessary to the body of the SMS sent.


## Error Handling
The package throws exceptions in the following cases:

 - Missing or invalid API key.
 - Missing sender or organization ID.
 - Invalid or missing phone numbers.
 - SMS sending failures.
 - Ensure you handle exceptions appropriately in your application.