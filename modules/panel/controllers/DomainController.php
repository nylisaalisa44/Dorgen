<?php

namespace app\modules\panel\controllers;

use app\commands\WordPressJob;
use app\components\CpanelApi;
use app\components\Helpers;
use app\components\RabbitMQ;
use app\components\Redis;
use app\components\WordPressClient;
use app\models\Articles;
use app\models\Domain;
use app\models\DomainForm;
use app\models\Project;
use app\models\VisitorsBots;
use Yii;
use yii\base\DynamicModel;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * DomainController implements the CRUD actions for Domain model.
 */
class DomainController extends Controller
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
                    ],
                ],
            ]
        );
    }

    public function actionGetDomainList()
    {
        if (!$this->request->isPost) {
            throw new BadRequestHttpException();
        }

        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $template = Yii::$app->request->post('template');
        $keys = Yii::$app->request->post('keys');

        foreach ($keys as $key) {
            $domain = Domain::findOne($key);

            if ($domain) {
                // Формируем URL на основе шаблона и домена
                $url = str_replace('{domain}', $domain->domain, $template);

                $formattedDomains[] = $url;
            }
        }

        // Возвращаем данные для textarea
        return implode("\n", $formattedDomains);
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionMoveProject()
    {
        if (!$this->request->isPost) {
            throw new BadRequestHttpException();
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $keys = \Yii::$app->request->post('keys');
        $project = \Yii::$app->request->post('project');

        if (Domain::updateAll(['project_id' => $project], ['in', 'id', $keys])) {
            return [
                'status' => 'success'
            ];
        }

        return [
            'status' => 'error'
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionMoveFarm()
    {
        if (!$this->request->isPost) {
            throw new BadRequestHttpException();
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $keys = \Yii::$app->request->post('keys');
        $revert = \Yii::$app->request->post('revert');

		if ($revert) {
			$updatedRows = Domain::updateAll(['farm' => 0], ['in', 'id', $keys]);
		} else {
			$updatedRows = Domain::updateAll(['farm' => 1], ['in', 'id', $keys]);
		}

        if ($updatedRows > 0) {
            return [
                'status' => 'success',
                'updatedRows' => $updatedRows
            ];
        }

        return [
            'status' => 'error',
            'message' => 'No rows were updated'
        ];
    }

    /**
     * @throws BadRequestHttpException
     */
    public function actionRemove()
    {
        if (!$this->request->isPost) {
            throw new BadRequestHttpException();
        }

        \Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        $keys = \Yii::$app->request->post('keys');

        foreach ($keys as $key) {
            Redis::deleteDomainSlugs($key);
        }

        if (Domain::deleteAll(['in', 'id', $keys])) {
            return [
                'status' => 'success'
            ];
        }

        return [
            'status' => 'error'
        ];
    }

    public function actionGetDomains()
    {
        $model = Domain::find()->all();
        foreach ($model as $m) {
            echo "https://{d5}.$m->domain <br>";
        }
    }

    public function actionChecker()
    {
        return $this->render('checker');
    }

    public function actionRedirect($id)
    {
        $model = $this->findModel($id);
        if ($model->load(Yii::$app->request->post())) {
            if ($model->redirect_type !== '' && $model->redirect_type !== null) $model->redirect_type = implode(',', array_values((array)$model->redirect_type));
            if ($model->redirect_urls !== '' && $model->redirect_urls !== null) $model->redirect_urls = str_replace("\r\n", ",", $model->redirect_urls);
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Редирект успешно установлен');
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка');
            }
            return $this->redirect(['index']);
        }

        //check redirect type and url
        if ($model->redirect_type !== '' && $model->redirect_type !== null) $model->redirect_type = explode(',', $model->redirect_type);
        if ($model->redirect_urls !== '' && $model->redirect_urls !== null) $model->redirect_urls = str_replace(",", "\r\n", $model->redirect_urls);

        return $this->renderAjax('_redirect', [
            'model' => $model
        ]);
    }

    /**
     * Creates a new Domain model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new DomainForm();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                \Yii::$app->session->setFlash('success', 'Добавлены');
                return $this->redirect(['project/view', 'id' => $model->project_id]);
            }
        }

        return $this->redirect(['project/view', 'id' => $model->project_id]);
    }

    /**
     * Deletes an existing Domain model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
		$result = $this->findModel($id)->delete();
//        return $this->redirect(['index']);

		if ($result) {
			Yii::$app->session->setFlash('success', 'Успешно удален');
		} else {
			Yii::$app->session->setFlash('error', 'Что-то пошло не так');
		}
		Redis::deleteDomainSlugs($id);

		return $this->redirect(Yii::$app->request->referrer);
    }

	/**
	 * Переносит домены в новый проект
	 */
	public function actionChangeProject () {
		$post = $this->request->post();
		$domainsIds = $post['domains'] ?? '';
		$projectId =  $post['project'] ?? '';

		if ($domainsIds == '' || $projectId == '') {
			Yii::$app->session->setFlash('error', 'Что-то пошло не так');
		} else {
			$errorMessages = '';
			$domainIdArray = explode(',',$domainsIds);
			foreach ($domainIdArray as $id){
				$domain = Domain::find()->where(['id' => $id])->one();
				$domain->project_id = $projectId;
				$domain->save();

				$errors = $domain->errors;
				if (!empty($errors)) {
					$errorMessages .= "$domain->domain: ";
					foreach ($errors as $error) {
						$errorMessages .= implode(', ', $error) . " ";
					}
					$errorMessages .= " | ";
				}
			}

			if ($errorMessages == '') {
				Yii::$app->session->setFlash('success', 'Домены успешно перенесены');
			} else {
				Yii::$app->session->setFlash('error', "Произошла ошибка: $errorMessages");
			}
		}

		return $this->redirect(Yii::$app->request->referrer);
	}


	public function actionDdl()
	{
		$query = Domain::find()->where(['project_id' => null, 'type' => Domain::TYPE_DDL]);
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'pagination' => false,
			'sort' => [
				'defaultOrder' => [
					'id' => SORT_DESC,
				]
			],
		]);

		$total = Helpers::getTotalStatistics($dataProvider);

		return $this->render('ddl', [
			'dataProvider' => $dataProvider,
			'total' => $total,
		]);
	}

	public function actionUploadCpanel()
	{
		if ($this->request->isPost) {
			$model = new DynamicModel(['file']);
			$model->addRule(['file'], 'required');
			$model->addRule(['file'], 'file', ['extensions' => 'txt']);

			if ($model->load(Yii::$app->request->post())) {
				$model->file = UploadedFile::getInstance($model, 'file');
				$redis = Yii::$app->redis;
				$redis->set('current_cpanel', 0);

				list($uniq, $uniq_lines) = CpanelApi::pushPluginsToQueue($model);
				return $this->asJson([
					'success' => true,
					'jobId' => $uniq,
					'uniq_lines' => $uniq_lines
				]);
			} else {
				Yii::$app->session->setFlash('error', "Файл не прошел валидацию");
			}
		}
		return $this->redirect(['ddl']);
	}

	public function actionUploadClients()
	{
		if ($this->request->isPost) {
			$model = new DynamicModel(['file','metka']);
			$model->addRule(['file','metka'], 'required');
			$model->addRule(['file'], 'file', ['extensions' => 'txt']);
			$model->addRule(['metka'], 'string');

			if ($model->load(Yii::$app->request->post())) {
				$model->file = UploadedFile::getInstance($model, 'file');
				$redis = Yii::$app->redis;
				$redis->set('current_plugin', 0);

				list($uniq, $uniq_lines) = WordPressClient::pushPluginsToQueue($model);
				return $this->asJson([
					'success' => true,
					'jobId' => $uniq,
					'uniq_lines' => $uniq_lines
				]);
			} else {
				Yii::$app->session->setFlash('error', "Файл не прошел валидацию");
			}
		}
		return $this->redirect(['ddl']);
	}

	public function actionGetJobLog($jobId, $type)
	{
		if ($type == 'wp') {
			$logName = 'wordpress.log';
		} else {
			$logName = 'cpanel.log';
		}

		$logFile = Yii::getAlias('@runtime/logs/'. $logName);
		$logs = file($logFile);

		$filteredLogs = array_filter($logs, function($log) use ($jobId) {
			return str_contains($log, "ID: {$jobId}");
		});

		$parsedLogs = array_map(function($log) {
			$parts = explode('|', $log);
			return trim($parts[1] ?? '');
		}, $filteredLogs);

		return implode('<br>', $parsedLogs);
	}

	public function actionGetCurrent($key)
	{
		$redis = Yii::$app->redis;
		return $redis->get($key);
	}

	public function actionSendReport ($file_name) {
		$file_path = Yii::getAlias('@runtime/logs/'. $file_name);
		if (file_exists($file_path)) {
			Yii::$app->response->sendFile($file_path)->on(\yii\web\Response::EVENT_AFTER_SEND, function() use ($file_path) {
				@unlink($file_path);
			});
		} else {
			return $this->redirect(Yii::$app->request->referrer);
		}


	}

    /**
     * Finds the Domain model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Domain the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Domain::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
