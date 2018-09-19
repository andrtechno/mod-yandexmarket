<?php

use panix\engine\Html;
use panix\engine\bootstrap\ActiveForm;
?>
<?php
$form = ActiveForm::begin([
            //  'id' => 'form',
            'options' => ['class' => 'form-horizontal'],

        ]);
?>
<div class="card bg-light">
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

        <?= Html::submitButton(Html::icon('check') . ' ' . Yii::t('app', 'SAVE'), ['class' => 'btn btn-success']) ?>
        <?= Html::a('das',[''],['class'=>'btn btn-default']); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>