<?php

namespace panix\mod\yandexmarket\controllers;

use panix\mod\yandexmarket\components\YandexMarketXML;

class DefaultController extends \panix\engine\controllers\WebController {

    public function actionIndex() {
        $xml = new YandexMarketXML;
        $xml->processRequest();
    }

}
