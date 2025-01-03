<?php

namespace app\modules\panel\controllers;

use app\commands\CloudflareJob;
use app\components\Cloudflare;
use app\components\RabbitMQ;
use app\models\CfAccounts;
use app\models\CfAccountsSearch;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * CfAccountsController implements the CRUD actions for CfAccounts model.
 */
class CfAccountsController extends Controller
{
    /**
     * @inheritDoc
     */
	public function behaviors()
	{
		return array_merge(
			parent::behaviors(),
			[
				'access' => [
					'class' => AccessControl::class,
					'rules' => [
						[
							'allow' => true,
							'roles' => ['@'],
						],
					],
				],
				'verbs' => [
					'class' => VerbFilter::className(),
					'actions' => [
						'delete' => ['POST'],
						'proba' => ['GET','POST'],
					],
				],
			]
		);
	}

    /**
     * Lists all CfAccounts models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new CfAccountsSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single CfAccounts model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new CfAccounts model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new CfAccounts();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing CfAccounts model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing CfAccounts model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the CfAccounts model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return CfAccounts the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = CfAccounts::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

	public function actionAddDomains()
	{
		$model = new DynamicModel(['ip', 'domains', 'cf_acc_id']);
		$model->addRule(['ip', 'domains','cf_acc_id'], 'required')
			->addRule(['ip'], 'ip')
			->addRule(['cf_acc_id'], 'integer')
			->addRule(['domains'], 'string');

		if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
			$domainsText = $model->domains;
			$domains = array_map('trim', explode("\n", $domainsText));
			$cfAcc = CfAccounts::find()->where(['id' => $model->cf_acc_id])->one();

			$cfApi = new Cloudflare();
			$cfApi->setHeaders($cfAcc->login, $cfAcc->token);
			$cfApi->setIpForAddDomainsFunction($model->ip);
			Yii::$app->queue->push(new CloudflareJob([
				'cf' => $cfApi,
				'type' => 'domains',
				'domains' => $domains
			]));
			return $this->renderPartial('result');
		}

		return $this->render('add-domains', ['model' => $model]);
	}

	public function actionChangeIp () {
		$model = new DynamicModel(['ip', 'domains']);
		$model->addRule(['new_ip', 'replaced_ip', 'old_ip'], 'required')
			->addRule(['new_ip'], 'ip')
			->addRule(['replaced_ip'], 'ip')
			->addRule(['old_ip'], 'ip');

		if (\Yii::$app->request->isPost && $model->load(\Yii::$app->request->post()) && $model->validate()) {
			$new_ip = $model->new_ip;
			$replaced_ip = $model->replaced_ip;
			$old_ip = $model->old_ip;

			$cfApi = new Cloudflare();
			$cfApi->setIps($replaced_ip,$old_ip,$new_ip);
			$accounts = CfAccounts::find()->asArray()->all();
			$cfApi->setCfAccounts($accounts);

			Yii::$app->queue->push(new CloudflareJob([
				'cf' => $cfApi,
				'type' => 'change_ip'
			]));

			return $this->renderPartial('result');
		}

		return $this->render('change-ip', ['model' => $model]);
	}

	public function actionTest () {
		$cfApi = new Cloudflare();
		Yii::$app->queue->push(new CloudflareJob([
			'cf' => $cfApi,
			'type' => 'test'
		]));
		return $this->renderPartial('result',[

		]);

	}

	public function actionGetMessage() {
		$rabbitMQ = new RabbitMQ('cloudflare.service', 'cloudflare.queue', 'cloudflare');
		$message = $rabbitMQ->getMessage();
		return $this->asJson(['message' => $message]);
	}
}
