<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;

/**
 * @var \panix\mod\yandexmarket\models\SettingsForm $model
 */
$form = ActiveForm::begin([]);
?>
    <div class="card">
        <div class="card-header">
            <h5><?= $this->context->pageName ?></h5>
        </div>
        <div class="card-body">
            <?= $form->field($model, 'name')->hint($model::t('HINT_NAME')); ?>
            <?= $form->field($model, 'company')->hint($model::t('HINT_COMPANY')); ?>
            <?= $form->field($model, 'url')->hint($model::t('HINT_URL')); ?>
            <?= $form->field($model, 'currency_id')->dropDownList($model->getCurrencies()); ?>


        </div>
        <div class="card-footer text-center">
            <?= $model->submitButton(); ?>
            <?= Html::a('XML',['/yandex-market.xml'],['class'=>'btn btn-outline-primary','target'=>'_blank']); ?>
        </div>
    </div>
<?php ActiveForm::end(); ?>