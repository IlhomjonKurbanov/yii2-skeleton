<?php
use \yii\helpers\Html;

/* @var $user \common\models\Account */

?>
<table class="body-wrap">
    <tr>
        <td></td>
        <td class="container" width="600">
            <div class="content">
                <table class="main" width="100%" cellpadding="0" cellspacing="0" itemprop="action" itemscope="" itemtype="http://schema.org/ConfirmAction">
                    <tr>
                        <td class="content-wrap">
                            <meta itemprop="name" content="Welcome Email">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td class="content-block">
                                        <?= Yii::t('app', 'Hello {FULLNAME},', ['FULLNAME' => Html::encode($user->fullname)]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        <?= Yii::t('app', 'Thank you for registering with {APPNAME}. Please note details of your account:', ['APPNAME' => Html::encode(Html::encode(\Yii::$app->name))]) ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block">
                                        <?= Yii::t('app', 'Email: {EMAIL}', ['EMAIL' => Html::encode($user->email)]) ?>
                                        <br>
                                        <?= Yii::t('app', 'Password: {PASSWORD}', ['PASSWORD' => Html::encode($user->passwordText)]) ?>
                                        <br>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content-block" itemprop="handler" itemscope="" itemtype="http://schema.org/HttpActionHandler">
                                        <a href="<?= Yii::$app->urlManager->createAbsoluteUrl(Yii::$app->user->loginUrl) ?>" class="btn-primary" itemprop="url"><?= Yii::t('app', 'Login Now') ?></a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </div>
        </td>
        <td></td>
    </tr>
</table>
<link href="src/css/mailgun.css" media="all" rel="stylesheet" type="text/css" />