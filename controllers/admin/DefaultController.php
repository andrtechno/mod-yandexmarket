<?php

namespace panix\mod\yandexmarket\controllers\admin;

use Yii;
use panix\engine\controllers\AdminController;
use panix\mod\yandexmarket\models\SettingsForm;

class DefaultController extends AdminController {

    public function actionIndex() {

        $this->pageName = Yii::t('yandexmarket/default', 'MODULE_NAME');

        $this->breadcrumbs[] = [
            'label' => Yii::t('shop/default', 'MODULE_NAME'),
            'url' => ['/admin/shop']
        ];
        $this->breadcrumbs[] = $this->pageName;
        $model = new SettingsForm;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $model->save();
            $this->refresh();
        }
        return $this->render('index', ['model' => $model]);
    }

}
