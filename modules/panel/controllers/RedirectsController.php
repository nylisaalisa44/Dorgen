<?php

namespace app\modules\panel\controllers;

use app\models\Redirects;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * RedirectsController implements the CRUD actions for Redirects model.
 */
class RedirectsController extends Controller
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
                'access' => [
                    'class' => AccessControl::class,
                    'only' => ['index'],
                    'rules' => [
                        [
                            'actions' => ['index'],
                            'allow' => true,
                            'roles' => ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all Redirects models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $redirects = Redirects::find()
            ->andWhere(['!=', 'type', Redirects::TYPE_DOMAIN])
            ->groupBy('entity_id')
            ->orderBy('entity_id')
            ->all();

        $statusCounts = Redirects::find()
            ->select([
                'entity_id',
                'status',
                'SUM(bots_count) AS bots_count',
                'SUM(bot_limit) AS bot_limit',
                'COUNT(*) AS count'
            ])
            ->andWhere(['!=', 'type', Redirects::TYPE_DOMAIN])
            ->groupBy(['entity_id', 'status'])
            ->asArray()
            ->all();

        $statusCountsByEntity = [];
        foreach ($statusCounts as $statusCount) {
            $entityId = $statusCount['entity_id'];
            $status = $statusCount['status'];

            if (!isset($statusCountsByEntity[$entityId])) {
                $statusCountsByEntity[$entityId] = [
                    'status' => [],
                    'bots_count' => 0,
                    'bot_limit' => 0
                ];
            }

            $statusCountsByEntity[$entityId]['status'][$status] = $statusCount['count'];
            $statusCountsByEntity[$entityId]['bots_count'] += $statusCount['bots_count'];
            $statusCountsByEntity[$entityId]['bot_limit'] += $statusCount['bot_limit'];
        }

        return $this->render('index', [
            'redirects' => $redirects,
            'statusCountsByEntity' => $statusCountsByEntity,
        ]);
    }

    public function actionEntityProgress($id, $title = '')
    {
        $redirects = Redirects::find()
            ->where(['entity_id' => $id])
            ->all();

        return $this->renderPartial('entity_progress', [
            'redirects' => $redirects,
            'title' => $title
        ]);
    }

    /**
     * Displays a single Redirects model.
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
     * Creates a new Redirects model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new Redirects();

        if ($this->request->isPost) {
            $post = Yii::$app->request->post();
            $status = $post['run_bots_immediately'] ?? false;
            if ($status) {
                $status = Redirects::STATUS_IN_WORK;
            } else {
                $status = Redirects::STATUS_WAIT;
            }

            if ($model->load($this->request->post())) {
                if ($model->redirect_type !== '' && $model->redirect_type !== null)
                    $model->redirect_type = implode(',', $model->redirect_type);
                if ($model->allowed_bots !== '' && $model->allowed_bots !== null)
                    $model->allowed_bots = implode(',', array_values((array)$model->allowed_bots));

                if (in_array('0', $model->entity_id)) {
                    $entity_ids = [0];
                    $type = Redirects::TYPE_GLOBAL;
                } else {
                    $entity_ids = $model->entity_id;
                    $type = Redirects::TYPE_PROJECT;
                }

                $errorMessages = '';

                $links = explode("\r\n", $model->redirect_url);
                foreach ($entity_ids as $entity_id) {
                    foreach ($links as $link) {
                        $newModel = new Redirects();
                        $newModel->redirect_url = $link;
                        $newModel->redirect_type = $model->redirect_type;
                        $newModel->bot_limit = $model->bot_limit;
                        $newModel->status = $status;
                        $newModel->entity_id = $entity_id;
                        $newModel->allowed_bots = $model->allowed_bots;
                        $newModel->type = $type;
                        $newModel->save();

                        $errors = $newModel->errors;
                        if (!empty($errors)) {
                            $errorMessages .= "$link: ";
                            foreach ($errors as $error) {
                                $errorMessages .= implode(', ', $error) . " ";
                            }
                            $errorMessages .= " | ";
                        }

                    }
                }

                if ($errorMessages == '') {
                    Yii::$app->session->setFlash('success', 'Редиректы успешно установлены');
                } else {
                    Yii::$app->session->setFlash('error', "Произошла ошибка: $errorMessages");
                }

                return $this->redirect(['index']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    //TODO добавить валидаций и обработки ошибок и каким-то образом вывод через флешки?
    public function actionCreateDomainRedirects()
    {
        $post = Yii::$app->request->post();
        $domains_ids = explode(",", $post['redirect_keys']);

        $status = $post['run_bots_immediately'] ?? false;
        if ($status) {
            $status = Redirects::STATUS_IN_WORK;
        } else {
            $status = Redirects::STATUS_WAIT;
        }

        $allowed_bots = $post['allowed_bots'] ?? null;
        if ($allowed_bots !== null) {
            $allowed_bots = implode(',', array_values((array)$post['allowed_bots']));
        }

        $redirect_type = $post['redirect_type'] ?? null;
        if ($redirect_type !== null) {
            $redirect_type = implode(',', $post['redirect_type']);
        }

        $type = Redirects::TYPE_DOMAIN;
        $bot_limit = $post['bot_limit'];

        $errorMessages = '';

        $links = explode("\r\n", $post['redirect_url']);
        foreach ($domains_ids as $entity_id) {
            foreach ($links as $link) {
                $model = new Redirects();
                $model->redirect_url = $link;
                $model->redirect_type = $redirect_type;
                $model->bot_limit = $bot_limit;
                $model->status = $status;
                $model->entity_id = $entity_id;
                $model->type = $type;
                $model->allowed_bots = $allowed_bots;
                $model->save();

                $errors = $model->errors;
                if (!empty($errors)) {
                    $errorMessages .= "$link: ";
                    foreach ($errors as $error) {
                        $errorMessages .= implode(', ', $error) . " ";
                    }
                    $errorMessages .= " | ";
                }
            }
        }

        if ($errorMessages == '') {
            Yii::$app->session->setFlash('success', 'Редиректы успешно установлены');
        } else {
            Yii::$app->session->setFlash('error', "Произошла ошибка: $errorMessages");
        }

        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * Updates an existing Redirects model.
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
     * Deletes an existing Redirects model.
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

    public function actionClear($id)
    {
        Redirects::deleteAll(['entity_id' => $id]);
        return $this->redirect(['index']);
    }

    /**
     * Finds the Redirects model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Redirects the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Redirects::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionLoadDomainRedirect($id)
    {
        $model = Redirects::find()->where(['entity_id' => $id])->one();

        if ($model === null) {
            throw new \yii\web\NotFoundHttpException('Запись не найдена.');
        }

        $redirectUrls = Redirects::find()
            ->select('redirect_url')
            ->where(['entity_id' => $id])
            ->column();
        $url = implode("\r\n", $redirectUrls);
        $model->redirect_url = $url;

        return $this->renderPartial('_form', ['model' => $model]);
    }

    public function actionChangeStatus()
    {
        $get = Yii::$app->request->get();
        $status = $get['status'] ?? null;
        $entity_id = $get['id'] ?? null;
        if ($status == null || $entity_id == null) {
            Yii::$app->session->setFlash('error', 'Что-то пошло не так');
        } else {
            Redirects::updateAll(
                ['status' => $status],
                [
                    'and',
                    ['entity_id' => $entity_id],
                    ['!=', 'status', Redirects::STATUS_DONE]
                ]
            );
			if ($status == Redirects::STATUS_WAIT) {
				Redirects::clearCache($entity_id);
			}
            Yii::$app->session->setFlash('success', 'Статусы редиректов успешно изменены');
        }
        return $this->redirect(Yii::$app->request->referrer);
    }

	public function actionDeleteFilesRedirects () {
		if ($this->request->isPost) {
			$post = $this->request->post();
			$redirects = $post['DynamicModel']['redirects'] ?? '';
			$links = explode("\r\n", $redirects);
			$deleted_redirect = 0;
			foreach ($links as $link) {
				$deleted_redirect += Redirects::deleteAll(['like', 'redirect_url', $link]);
			}
			if($deleted_redirect == 0){
				Yii::$app->session->setFlash('warning', 'Не найдены редиректы для удаления');
			} else {
				Yii::$app->session->setFlash('success', 'Редиректы успешно удалены в количестве: ' . $deleted_redirect);
			}
		}

		return $this->redirect(Yii::$app->request->referrer);
	}
}
