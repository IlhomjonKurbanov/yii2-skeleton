<?php

namespace backend\controllers;

use common\Core;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use ZipArchive;


/**
 * I18nController
 */
class I18nController extends BackendController
{
    /**
     * add new language
     * @return \yii\web\Response
     */
    public function actionAdd()
    {
        $curLang = Yii::$app->language;
        Yii::$app->language = Yii::$app->request->post('language');
        Yii::t('app', 'Home');
        Yii::$app->language = $curLang;
        Yii::$app->session->setFlash('success', Yii::t('app', 'New language has been successfully added.'));
        return $this->redirect(['index']);
    }

    /**
     * @inheritdoc
     * @return array
     */
    public function behaviors()
    {
        $adminRules = parent::behaviors()['access']['rules'];

        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'add' => ['post'],
                    'delete' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => $adminRules,
            ],
        ];
    }

    /**
     * Index
     * @return string
     */
    public function actionIndex()
    {
        $languages = Core::getLocaleList();
        if (Yii::$app->params['mongodb']['i18n']) {
            $message = $this->mongodbMessage();
        } else {
            $message = $this->mysqlMessage();
        }

        foreach ($message['currentLanguages'] as $key => $name) {
            unset($languages[$key]);
        }

        /* export tab */
        $availableExport = [
        ];

        $availableLanguages = $message['currentLanguages'];
        foreach ($availableLanguages as $key => $availableLanguage) {
            $availableExport[]=[
                'lang' => $key,
                'title' => $availableLanguage,
                'cats' => $this->getLanguageCat($key)
            ];
        }

        return $this->render('index', [
            'searchModel' => $message['searchModel'],
            'dataProvider' => $message['dataProvider'],
            'languages' => $languages,
            'mongodb' => Yii::$app->params['mongodb']['i18n'],
            'availableExport' => $availableExport
        ]);
    }

    /**
     * @param $lang
     * @return array
     */
    protected function getLanguageCat($lang)
    {
        $cats=[];
        if (Yii::$app->params['mongodb']['i18n']) {
            $cats = \common\models\mongodb\Message::find()
                ->where(['language' => $lang])
                ->asArray()
                ->distinct('category');

        }
        return $cats;
    }

    /**
     * MySQL Message
     * @return array
     */
    protected function mysqlMessage()
    {
        $searchModel = new \common\models\MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $currentLanguages = \common\models\Message::getLocaleList([Yii::$app->sourceLanguage]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'currentLanguages' => $currentLanguages
        ];
    }

    /**
     * MongoDB Message
     * @return array
     */
    protected function mongodbMessage()
    {
        $searchModel = new \common\models\mongodb\MessageSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        $currentLanguages = \common\models\mongodb\Message::getLocaleList([Yii::$app->sourceLanguage]);
        return [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'currentLanguages' => $currentLanguages
        ];
    }

    /**
     * ajax save translation
     * @throws \yii\db\Exception
     */
    public function actionSaveTranslation()
    {
        // POST: id, value
        if (Yii::$app->request->isPost) {
            $parts = explode('_', Yii::$app->request->post('id'));
            $id = $parts[1];
            $language = $parts[2];
            $value = Yii::$app->request->post('value');
            if (!empty($id)) {
                if (Yii::$app->params['mongodb']['i18n']) {
                    $message = \common\models\mongodb\Message::find()->where(['_id' => $id])->one();
                    $message->translation = $value;
                    $message->save();
                } else {
                    Yii::$app->db->createCommand()->update('{{%core_message}}', ['translation' => $value, 'is_translated' => 1], ['id' => $id, 'language' => $language])->execute();
                }

                echo $value;
            }

        }
    }

    /**
     * delete translate
     * @param $id
     * @param $language
     * @return \yii\web\Response
     * @throws \Exception
     * @throws \yii\db\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id, $language = null)
    {
        if (Yii::$app->params['mongodb']['i18n']) {
            \common\models\mongodb\Message::find()->where(['_id' => $id])->one()->delete();
        } else {
            Yii::$app->db->createCommand()->delete('{{%core_message}}', ['id' => $id, 'language' => $language])->execute();
        }

        return $this->redirect(['index']);
    }

    /**
     * @param $lang
     * @param $cat
     * @throws \Exception
     */
    public function actionExport($lang, $cat)
    {
        if (Yii::$app->params['mongodb']['i18n']) {
            $messages = \common\models\mongodb\Message::find()
                ->select(['language', 'category', 'message', 'is_translated'])
                ->where(['language' => $lang, 'category' => $cat])
                ->asArray()
                ->all();

            /* zip file */
            $zipFile = tempnam(sys_get_temp_dir(), 'zip');
            $zip = new ZipArchive();
            if ($zip->open($zipFile, ZipArchive::CREATE) !== TRUE) {
                throw new \Exception(Yii::t('app', 'Cannot create a zip file.'));
            }
            $zip->addFromString($lang . '_' . $cat . '.json', json_encode($messages));
            $zip->close();

            /* download */
            Yii::$app->response->sendFile($zipFile, $lang . '_' . $cat . '.zip');
        }
    }
}
