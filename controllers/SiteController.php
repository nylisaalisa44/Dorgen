<?php

namespace app\controllers;

use app\components\BotDetector;
use app\components\Helpers;
use app\components\Middleware;
use app\models\Domain;
use app\models\Project;
use Yii;
use yii\db\Exception;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    /**
     * @throws BadRequestHttpException
     * @throws \Exception
     */
    public function actionIndex()
    {
        $domain = Domain::find()->where(['domain' => Helpers::getDomain()])->one();

        $middleware = new Middleware($domain);

        $isBot = Helpers::isBot();
        $remoteIp = Helpers::getRemoteIp();
        $project = $middleware->domain->project;
        $isDebugEnabled = $project->enable_debug && $remoteIp == $project->debug;

        $redirect = $middleware->getRedirect();
        if ($isBot && $redirect) {
            if ($isDebugEnabled) {
                print_r("Сработал редирект <br>");
                print_r($redirect);
                die();
            }
            return $redirect->execute($this);
        }

        $result = $middleware->getQueryResult();
        $isAllowedBot = BotDetector::isAllowedBot($project->allowed_bots);

        if ($isBot || $isDebugEnabled) {
            if (!$isAllowedBot && !$isDebugEnabled) {
                die();
            }

            if ($middleware->domain->farm) {
                return $this->renderFarmTemplate($middleware, $result);
            } else {
                return $this->renderDoorTemplate($middleware, $result, $project);
            }

        } else {
            list($view, $url) = Helpers::checkAndGetUrl($project);
            return $this->renderPartial('/template/' . $view, [
                'key' => $result['key_name'],
                'snippets' => $result['snippets'],
                'url' => $url,
                'maxId' => $result['max_id'],
                'middleware' => $middleware,
            ]);
        }
    }

    private function renderFarmTemplate($middleware, $result): string
    {
        $project = $middleware->domain->project;
        $domains = Domain::find()->select('domain')->where(['farm' => 1])->column();
        return $this->renderPartial('/template/files/farm', [
            'key' => $result['key_name'],
            'max_snip' => $project->max_snippets,
            'min_snip' => $project->min_snippets,
            'min_links' => $project->min_perelinks,
            'max_links' => $project->max_perelinks,
            'subs' => $project->use_subs,
            'snippets' => $result['snippets'],
            'redirect_rate' => $project->farm_redirect_inner_procent,
            'domains' => $domains,
            'init_domain' => $middleware->domain->domain
        ]);
    }

    private function renderDoorTemplate($middleware, $result, $project): string
    {
        $domain = $middleware->domain;
        $domain_without_zone = $domain->getDomainWithoutZone();
        $num_links = rand($project->min_links_white, $project->max_links_white);
        $num_html_links = 0;
        $domains = $this->getWhiteDomains($project);
        $anchors = $this->getWhiteAnchors($result, $project, $num_links);

        $domains_dor_hidden_link = null;
        $anchors_hidden = null;
        $anchors_map = null;
        $domains_map = null;

        $domain_format = $project->use_subs_white
            ? "https://{w6}.{domain}/{w6}"
            : "https://{domain}/{w6}";

        $farm_domains = Domain::find()->select('domain')->where(['farm' => 1])->column();

        return $this->renderPartial('/template/' . $project->white, [
            'domains' => $domains,
            'key' => $result['key_name'],
            'name_domen_bez_zona' => $domain_without_zone,
            'href_lang' => $project->lang_white,
            'snippets' => $result['snippets'],
            'anchors' => ArrayHelper::getColumn($anchors, 'key_name'),
            'use_subs_white' => $project->use_subs_white,
            'max_snip' => $project->max_snippets_white,
            'min_snip' => $project->min_snippets_white,
            'num_links' => $num_links,
            'num_html_links' => $num_html_links,
            'min_snippets_links_white' => $project->min_snippets_links_white,
            'max_snippets_links_white' => $project->max_snippets_links_white,
            'project_type' => $project->type,
            'domain_format' => $domain_format,
            'domains_dor_hidden_link' => $domains_dor_hidden_link,
            'anchors_hidden' => $anchors_hidden,
            'anchors_map' => $anchors_map,
            'domains_map' => $domains_map,
            'farm_domains' => $farm_domains
        ]);
    }

    private function getWhiteDomains($project): array
    {
        if ($project->use_project_links_white) {
            return Domain::find()->select('domain')->where(['farm' => 0, 'project_id' => $project->id])->column();
        }
        return [Helpers::getDomain()];
    }

    private function getWhiteAnchors($result, $project, $num)
    {
        $queryCommand = Yii::$app->db->createCommand($this->buildAnchorQuery($project->rand_anchor_white, $project->table));
        $queryCommand->bindValue(':limit', $num);

        if (!$project->rand_anchor_white) {
            $queryCommand->bindValue(':currentId', $result['id']);
        }

        return $queryCommand->queryAll();
    }

    private function buildAnchorQuery($randAnchorWhite, $table): string
    {
        if ($randAnchorWhite) {
            return "SELECT key_name FROM `$table` JOIN ( SELECT rand() * (SELECT max(id) from `$table`) AS max_id ) AS m WHERE id >= m.max_id ORDER BY id ASC LIMIT :limit;";
        } else {
            return "
            SELECT * FROM (
                (SELECT key_name FROM `$table` WHERE id > :currentId ORDER BY id ASC LIMIT :limit)
                UNION ALL
                (SELECT key_name FROM `$table` WHERE id < :currentId ORDER BY id DESC LIMIT :limit)
            ) AS combined_results
            LIMIT :limit;
        ";
        }
    }
}
