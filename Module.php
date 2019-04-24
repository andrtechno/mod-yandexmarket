<?php

namespace panix\mod\yandexmarket;

use panix\mod\admin\widgets\sidebar\BackendNav;
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
                                'url' => ['/admin/yandexmarket'],
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
        return (new BackendNav())->findMenu('shop')['items'];
    }

    public function getInfo()
    {
        return [
            'label' => Yii::t('yandexmarket/default', 'MODULE_NAME'),
            'author' => 'andrew.panix@gmail.com',
            'version' => '1.0',
            'icon' => $this->icon,
            'description' => Yii::t('yandexmarket/default', 'MODULE_DESC'),
            'url' => ['/admin/yandexmarket'],
        ];
    }
}
