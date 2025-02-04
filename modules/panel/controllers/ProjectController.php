<?php

namespace app\modules\panel\controllers;

use app\components\Helpers;
use app\models\Domain;
use app\models\DomainForm;
use app\models\Logs;
use app\models\Project;
use yii\data\ActiveDataProvider;
use yii\debug\models\search\Log;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * ProjectController implements the CRUD actions for Project model.
 */
class ProjectController extends Controller
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
                        //'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Project models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Project::find(),
            'pagination' => [
                'pageSize' => 50
            ],
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Project model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Project();

        if ($this->request->isPost) {
            $model->status = Project::STATUS_ACTIVE;
            if ($model->load($this->request->post())) {
                //check redirect type, url, bots
                if ($model->redirect_type !== '' && $model->redirect_type !== null)
					$model->redirect_type = implode(',', $model->redirect_type);
                if ($model->redirect_urls !== '' && $model->redirect_urls !== null)
					$model->redirect_urls = str_replace("\r\n", ",", $model->redirect_urls);
				if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
					$model->allowed_bots = implode(',', array_values((array)$model->allowed_bots));

                if ($model->save()) {
                    \Yii::$app->session->setFlash('success', 'Успешно');
                    return $this->redirect(['index']);
                } else {
                    $errors = $model->getErrorSummary(true);
                    \Yii::$app->session->setFlash('error', implode("\n", $errors));
                    return $this->redirect(['index']);
                }
            }
        } else {
            $model->loadDefaultValues();
            //check redirect type, url, bots
            if ($model->redirect_type !== '' && $model->redirect_type !== null)
				$model->redirect_type = explode(',', $model->redirect_type);
            if ($model->redirect_urls !== '' && $model->redirect_urls !== null)
				$model->redirect_urls = str_replace(",", "\r\n", $model->redirect_urls);
			if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
				$model->allowed_bots = explode(',', $model->allowed_bots);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    public function actionHide($id)
    {
        $model = Project::findOne($id);
        if ($model) {
            $model->status = Project::STATUS_INACTIVE;
            $model->save();
        }

        return $this->redirect(['index']);
    }

    public function actionShow($id)
    {
        $model = Project::findOne($id);
        if ($model) {
            $model->status = Project::STATUS_ACTIVE;
            $model->save();
        }

        return $this->redirect(['index']);
    }

    /**
     * Displays a single IpList model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $domain_model = new DomainForm();

/*        if ($model->type == Project::TYPE_DROP || $model->type === Project::) {
            $query = Domain::find()->where(['project_id' => $id, 'farm' => 0]);
            $queryFarm = Domain::find()->where(['project_id' => $id, 'farm' => 1]);
        } else {
            $query = Logs::find()->where(['project_id' => $id, 'status' => Logs::STATUS_VALID_UPLOAD]);
            $queryFarm = Logs::find()->where(['project_id' => $id, 'status' => Logs::STATUS_VALID_UPLOAD]); // TODO допилить для логов вывод
        }*/

        $query = Domain::find()->where(['project_id' => $id, 'farm' => 0]);
        $queryFarm = Domain::find()->where(['project_id' => $id, 'farm' => 1]);

        if ($model->redirect_type !== '' && $model->redirect_type !== null)
            $model->redirect_type = explode(',', $model->redirect_type);
        if ($model->redirect_urls !== '' && $model->redirect_urls !== null)
            $model->redirect_urls = str_replace(",", "\r\n", $model->redirect_urls);
        if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
            $model->allowed_bots = explode(',', $model->allowed_bots);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $dataProviderFarm = new ActiveDataProvider([
            'query' => $queryFarm,
/*            'pagination' => [
                'pageSize' => 50
            ],*/
            'pagination' => false,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ]
            ],
        ]);

        $total = Helpers::getTotalStatistics($dataProvider);
        $totalFarm = Helpers::getTotalStatistics($dataProviderFarm);

        return $this->render('view', [
            'model' => $model,
            'domain_model' => $domain_model,
            'dataProvider' => $dataProvider,
            'dataProviderFarm' => $dataProviderFarm,
            'total' => $total,
            'totalFarm' => $totalFarm
        ]);
    }


    /**
     * Updates an existing Project model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            //check redirect type and url
            if ($model->redirect_type !== '' && $model->redirect_type !== null)
				$model->redirect_type = implode(',', array_values((array)$model->redirect_type));
            if ($model->redirect_urls !== '' && $model->redirect_urls !== null)
				$model->redirect_urls = str_replace("\r\n", ",", $model->redirect_urls);
			if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
				$model->allowed_bots = implode(',', array_values((array)$model->allowed_bots));

            if ($model->save()) {
                \Yii::$app->session->setFlash('success', 'Сохранено');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                \Yii::$app->session->setFlash('error', 'Ошибка');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        throw new BadRequestHttpException();
    }

    /**
     * Deletes an existing Project model.
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
     * Finds the Project model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Project the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Project::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
