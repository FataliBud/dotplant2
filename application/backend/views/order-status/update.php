<?php

/* @var $this yii\web\View */
/* @var $model app\models\OrderStatus */

use kartik\helpers\Html;
use kartik\icons\Icon;
use app\backend\widgets\BackendWidget;
use kartik\widgets\ActiveForm;

$this->title = Yii::t('app', $model->isNewRecord ? 'Create' : 'Update');
$this->params['breadcrumbs'] = [
    ['label' => Yii::t('app', 'Order Statuses'), 'url' => ['index']],
    $this->params['breadcrumbs'][] = $this->title,
];

?>
<div class="col-xs-12 col-sm-6 col-md-6 col-lg-6">
    <?php $form = ActiveForm::begin(); ?>
        <?php
            BackendWidget::begin(
                [
                    'icon' => 'info-circle',
                    'title'=> Yii::t('shop', 'Order Status'),
                    'footer' => Html::submitButton(
                        Icon::show('save') . Yii::t('app', 'Save'),
                        ['class' => 'btn btn-primary']
                    ),
                ]
            );
        ?>
            <?= $form->field($model, 'title')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'short_title')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'label')->textInput(['maxlength' => 255]) ?>
            <?= $form->field($model, 'external_id')->textInput(['maxlength' => 38]) ?>
            <?= $form->field($model, 'internal_comment')->textarea(['rows' => 6]) ?>
        <?php BackendWidget::end(); ?>
    <?php ActiveForm::end(); ?>
</div>
