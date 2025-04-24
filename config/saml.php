<?php

return [
    'sp' => [
        'entityId' => env('APP_URL') . '/saml/metadata',
        'assertionConsumerService' => [
            'url' => env('APP_URL') . '/saml/acs'
        ],
        'singleLogoutService' => [
            'url' => env('APP_URL') . '/saml/sls'
        ],
        'NameIDFormat' => 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified'
    ],
    'idp' => [
        'entityId' => env('PLATFORM_SAML_IDP_ENTITYID'),
        'singleSignOnService' => [
            'url' => env('PLATFORM_SAML_IDP_SSS')
        ],
        'singleLogoutService' => [
            'url' => env('PLATFORM_SAML_IDP_SLS')
        ],
        'x509cert' => env('PLATFORM_SAML_IDP_X509CERT'),
    ],
    'debug' => env('PLATFORM_SAML_DEBUG', false)
];
