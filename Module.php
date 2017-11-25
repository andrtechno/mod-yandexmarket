<?php
namespace panix\mod\yandexmarket;
use Yii;
class Module extends \panix\engine\WebModule {

    public $icon = 'yandex';
    public $routes = [
        'yandex-market.xml' => 'yandexmarket/default/index',
    ];

    public function getAdminMenu() {
        return array(
            'shop' => array(
                'items' => array(
                    array(
                        'label' => Yii::t('yandexmarket/default', 'MODULE_NAME'),
                        'url' => ['/admin/yandexmarket'],
                        'icon' => $this->icon,
                    ),
                ),
            ),
        );
    }

    public function getAdminSidebar() {
        $mod = new \panix\engine\widgets\nav\Nav;
        $items = $mod->findMenu('shop');
        return $items['items'];
    }
    public function getInfo() {
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
