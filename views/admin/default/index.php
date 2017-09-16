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
<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title"><?= $this->context->pageName ?></h3>
    </div>
    <div class="panel-body">
        <?= $form->field($model, 'name')->hint($model::t('HINT_NAME')); ?>
        <?= $form->field($model, 'company')->hint($model::t('HINT_COMPANY')); ?>
        <?= $form->field($model, 'url')->hint($model::t('HINT_URL')); ?>
        <?= $form->field($model, 'currency_id')->dropDownList($model->getCurrencies()); ?>


    </div>
    <div class="panel-footer text-center">

        <?= Html::submitButton(Html::icon('check') . ' ' . Yii::t('app', 'SAVE'), ['class' => 'btn btn-success']) ?>
        <?= Html::a('das',[''],['class'=>'btn btn-default']); ?>
    </div>
</div>
<?php ActiveForm::end(); ?>