# Laravel Arpoone Documentation
## Introduction
**Laravel Arpoone** is a package that integrates with the Arpoone SMS and EMAIL gateway, allowing you to send transactional SMS and EMAIL messages from your Laravel application using the notification system. This package supports multi-tenant applications, webhook support, and logging of SMS and EMAIL messages in the database.

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
2. **Set environment variables:**

   If you are not using multi-tenant mode, In your `.env` file, add the necessary variables:
    ```env
   ARPOONE_API_KEY=your-api-key-here
   ARPOONE_ORGANIZATION_ID=your-organization-id-here
   ARPOONE_SMS_SENDER=your-sms-sender-id-here
   ARPOONE_EMAIL_SENDER=your-email-sender-address-here
   ARPOONE_EMAIL_SENDER_NAME=your-email-sender-name-here
    ```
3. **Publish the configuration file:**  

   After installation, publish the configuration file to customize the package behavior.
   ```bash
   php artisan vendor:publish --tag=arpoone-config
   ```

4. **Publish the migrations (optional):**

    If you are using multi-tenant or want to log SMS messages, publish the migrations:
    ```bash
    php artisan vendor:publish --tag=arpoone-migrations
    ```
    Then, run the migrations:
    ```bash
    php artisan migrate
    ```
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
    // You can configure this via the ARPOONE_SMS_SENDER variable in your .env file.
    'sms_sender' => env('ARPOONE_SMS_SENDER', null),

    // The default sender email for the Email messages.
    // You can configure this via the ARPOONE_EMAIL_SENDER variable in your .env file.
    'email_sender' => env('ARPOONE_EMAIL_SENDER', null),

    // The default sender name for the Email messages.
    // You can configure this via the ARPOONE_EMAIL_SENDER_NAME variable in your .env file.
    'email_sender_name' => env('ARPOONE_EMAIL_SENDER_NAME', null),

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
    'sms_log_table_name' => 'arpoone_sms_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'sms_log_tenant_column_name' => 'tenant_id',

    //If true, the package will apply webhooks routes to handle the sms status
    //This is useful if you want to keep track of the sms status, and update your database accordingly
    'sms_webhooks' => false,

    //If true, the package will apply webhooks routes to handle the email status
    //This is useful if you want to keep track of the email status, and update your database accordingly
    'email_webhooks' => false,

    //If true, the sms sent will be logged in the database
    //This is useful if you want to keep track of the sms sent
    'log_emails' => false,

    //The name of the table to store the sms logs
    //This is only used if the log_sms option is set to true
    'emails_log_table' => 'arpoone_emails_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'emails_log_tenant_column_name' => 'tenant_id',

    //The name of the table to store the webhook logs
    //This is only used if the webhooks option is set to true
    'webhook_table_name' => 'arpoone_webhook_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'webhook_tenant_column_name' => 'tenant_id',
];
```
## Key Configuration Options:
 - url: Base URL for the Arpoone API.
 - api_key: API key for authenticating requests.
 - organization_id: ID of the organization sending the SMS.
 - sms_sender: Default sender name or number for the SMS messages.
 - email_sender: Default sender email for the Email messages.
 - email_sender_name: Default sender name for the Email messages.
 - verify_ssl: Verify SSL certificates when sending requests.
 - multi_tenant: Enable multi-tenant support.
 - table_name: Name of the database table to store configuration settings.
 - tenant_model: Tenant model to use for multi-tenant applications.
 - use_tenant_column: Create a tenant column in the configuration table.
 - tenant_column_name: Name of the column to store the tenant identifier.
 - log_sms: Enable logging of SMS messages.
 - sms_log_table: Name of the table to store SMS logs.
 - sms_log_tenant_column_name: Name of the column to store the tenant identifier for SMS logs.
 - sms_webhooks: Enable webhooks to track SMS status.
 - email_webhooks: Enable webhooks to track email status.
 - log_emails: Enable logging of email messages.
 - emails_log_table: Name of the table to store email logs.
 - emails_log_tenant_column_name: Name of the column to store the tenant identifier for email logs.
 - webhook_table_name: Name of the table to store webhook logs.
 - webhook_tenant_column_name: Name of the column to store the tenant identifier for webhook logs.

### Notes:
 - If you are using multi-tenant mode, set `multi_tenant` to true and define the `tenant_model` and `tenant_column_name`.
 - If you are using logging, set `log_sms` or `log_emails` to true and manually go to the `migrations` table delete all the package migrations and run `php artisan migrate` to create the new tables.
 - If you change any table default name, after running `php artisan migrate`, you need to manually update the tables in the database, deleting them, deleting the rows for the corresponding tables from the `migrations` table and running the migrations again.

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
                   'type' => 'sms',
                   'content' => 'Your SMS message content goes here!',
                 ];
            }
       }
       ```

3. **Send the Notification:**

    In your Notifiable model (such as User), ensure that you have a method `routeNotificationForArpoone` which returns the phone number:
    ```php
    public function routeNotificationForArpooneSMS()
    {
        return $this->phone_number;
    }
    ```
    Then, to send the SMS:
    ```php
    $user = User::find(1);
    $user->notify(new SendSmsNotification());
    ```
    **Note:** If you do not create a `routeNotificationForArpooneSMS` method in your Notifiable model, the package will throw an exception.

### Sending Email Notifications
To send Email using Laravel’s notification system, you need to define a notification class that uses the Arpoone channel. Here's an example:

1. **Create a Notification Class:**  
   ```bash
   php artisan make:notification SendEmailNotification
   ```
2. **Modify the Notification Class:**

    Inside the `SendEmailNotification` class, define the `toArpoone` method to send Email:
    ```php
    use Illuminate\Notifications\Notification;
    
    class SendEmailNotification extends Notification
    {
         public function via($notifiable)
         {
              return [\Controlink\LaravelArpoone\Channels\Arpoone::class];
         }
    
         public function toArpoone($notifiable)
         {
              return [
                'type' => 'email',
                'subject' => 'Your Email subject goes here!',
                'textContent' => 'Your Email message content goes here!', // Optional, if not set, the package will use strip_tags on htmlContent
                'htmlContent' => 'Your <b>Email message</b> content goes here!',
              ];
         }
    }
    ```
3. **Send the Notification:**

    In your Notifiable model (such as User), ensure that you have a method `routeNotificationForArpooneEmail` which returns the email address:
    ```php
    public function routeNotificationForArpooneEmail()
    {
        return $this->email;
    }
    ```
    Then, to send the Email:
    ```php
    $user = User::find(1);
    $user->notify(new SendEmailNotification());
    ```
    **Note:** If you do not create a `routeNotificationForArpooneEmail` method in your Notifiable model, the package will throw an exception.

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
If SMS logging is enabled, set `log_sms` to true in the configuration. The package will log sent SMS in the database table defined in `sms_log_table`.

## Logging Emails
If Email logging is enabled, set `log_emails` to true in the configuration. The package will log sent Emails in the database table defined in `emails_log_table`.

## Webhook Support
### SMS Webhooks
To track SMS statuses using webhooks, set webhooks to true in the configuration.
The package will automatically create a route to handle the webhook and will also add the webhooks necessary to the body of the SMS sent.

The possible statuses are:
 - `pending`: The SMS is pending to be sent.
 - `delivered`: The SMS has been delivered to the recipient.
 - `not_delivered`: The SMS could not be delivered to the recipient.

### Email Webhooks
To track Email statuses using webhooks, set webhooks to true in the configuration.
The package will automatically create a route to handle the webhook and will also add the webhooks necessary to the body of the Email sent.

The possible statuses are:
 - `bounced`: The Email has bounced back to the sender.
 - `clicked`: The Email has been clicked by the recipient.
 - `opened`: The Email has been opened by the recipient.
 - `spam`: The Email has been marked as spam by the recipient.
 - `unsubscribed`: The recipient has unsubscribed from the Email.

## Error Handling
The package throws exceptions in the following cases:

 - Missing or invalid API key.
 - Missing sender or organization ID.
 - Invalid or missing phone number or email address.
 - SMS sending failures.
 - Email sending failures.

When an SMS or Email fails to send, the package throws an `ArpooneRequestException`.
You can obtain the code returned by the API using the `getArpooneErrorCode()` method:

```php
try {
    $user->notify(new SomeNotification());
} catch (\Controlink\LaravelArpoone\Exceptions\ArpooneRequestException $e) {
    $code = $e->getArpooneErrorCode();
    // Handle the code or message as needed
}
```

If you call the channel directly via `send()`, catch the exception in the same way:

```php
$channel = new \Controlink\LaravelArpoone\Channels\Arpoone($tenantId);
try {
    $channel->send($user, new SomeNotification());
} catch (\Controlink\LaravelArpoone\Exceptions\ArpooneRequestException $e) {
    $code = $e->getArpooneErrorCode();
    // Handle the code or message as needed
}
```
Ensure you handle exceptions appropriately in your application.
