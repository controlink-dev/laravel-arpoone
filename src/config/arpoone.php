<?php

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
    'sms_log_table_name' => 'arpoone_sms_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'sms_log_tenant_column_name' => 'tenant_id',

    //If true, the package will apply webhooks routes to handle the sms status
    //This is useful if you want to keep track of the sms status, and update your database accordingly
    'webhooks' => false,

    //The name of the table to store the webhook logs
    //This is only used if the webhooks option is set to true
    'webhook_table_name' => 'arpoone_webhook_logs',

    //The name of the column to store the tenant identifier.
    //This is only used if the multi_tenant option is set to true and the use_tenant_column option is set to true.
    'webhook_tenant_column_name' => 'tenant_id',
];