<?php
/**
 * @var string[] $formData
 * @var \app\models\Order $order
 * @var \app\models\OrderTransaction $transaction
 */
?>
<form method="POST" action="https://www.liqpay.com/api/pay" accept-charset="utf-8" id="liqpay-form">
    <?php foreach ($formData as $key => $value): ?>
        <?= \yii\helpers\Html::hiddenInput($key, $value) ?>
    <?php endforeach; ?>
    <input type="submit" value="Pay" class="btn btn-primary" />
</form>
<script>
jQuery('#liqpay-form').submit();
</script>