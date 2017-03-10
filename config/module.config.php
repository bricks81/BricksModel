<?php

use Zend\ServiceManager\Factory\InvokableFactory;

return [
    'service_manager' => [
        'factories' => [
            'Bricks\Model\Model' => InvokableFactory::class
        ],
        'initializers' => [
            'Bricks\Model\ModelInitializer',
        ]
    ],
    'controllers' => [
        'initializers' => [
            'Bricks\Model\ModelInitializer',
        ]
    ]
];