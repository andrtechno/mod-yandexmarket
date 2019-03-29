<?php

namespace panix\mod\yandexmarket;

use Yii;
use panix\engine\WebModule;

class Module extends WebModule
{

    public $icon = 'yandex';
    public $routes = [
        'yandex-market.xml' => 'yandexmarket/default/index',
    ];

    public function getAdminMenu()
    {
        return [
            'shop' => [
                'items' => [
                    'integration' => [
                        'items' => [
                            [
                                'label' => Yii::t('yandexmarket/default', 'MODULE_NAME'),
                                'url' => ['/yandexmarket'],
                                'icon' => $this->icon,
                            ],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function getAdminSidebar()
    {
        return (new \panix\engine\bootstrap\BackendNav)->findMenu('shop')['items'];
    }

    public function getInfo()
    {
        return [
            'label' => Yii::t('yandexmarket/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('yandexmarket/default', 'MODULE_DESC'),
            'url' => ['/yandexmarket'],
        ];
    }
}
