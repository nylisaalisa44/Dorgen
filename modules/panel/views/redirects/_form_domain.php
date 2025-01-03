<?php

use app\components\BotDetector;
use app\models\Redirects;

?>
<form id="redirects-form" action="/panel/redirects/create-domain-redirects" method="post">
    <input type="hidden" name="<?= Yii::$app->request->csrfParam ?>" value="<?= Yii::$app->request->csrfToken ?>">

    <div class="form-group">
        <label for="redirect_url">Ссылки</label>
        <textarea id="redirect_url" name="redirect_url" rows="6" class="form-control" placeholder="Введите ссылки..." required></textarea>
        <p class="help-block">Формат шаблона:
            <br>
            <code>https://{c5}.domain.com</code> <br>
            <code>https://{w4}.domain2.com</code>
        </p>
        <p class="help-block">
            <code>{w4}</code> - 4 случайных символа <br>
            <code>{c5}</code> - 5 случайных букв <br>
            <code>{d3}</code> - 3 случайные цифры <br>
            <code>{rand1-567}</code> - случайное число от 1 до 567
        </p>
    </div>

    <label>Разрешённые боты</label>
    <div class="form-group">
		<?php foreach (BotDetector::getBotArray() as $botValue => $botName): ?>
            <div class="form-check form-check-inline">
                <input class="form-check-input" type="checkbox" name="allowed_bots[]" value="<?= $botValue ?>" id="allowed_bot_<?= $botValue ?>">
                <label class="form-check-label" for="allowed_bot_<?= $botValue ?>"><?= $botName ?></label>
            </div>
		<?php endforeach; ?>
    </div>

    <label>Тип редиректа</label>
    <div class="form-group">
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="redirect_type[]" value="<?=Redirects::REDIRECT_301?>" id="redirect_type_301">
            <label class="form-check-label" for="redirect_type_301">301 Redirect</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="redirect_type[]" value="<?=Redirects::REDIRECT_302?>" id="redirect_type_302">
            <label class="form-check-label" for="redirect_type_302">302 Redirect</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="redirect_type[]" value="<?=Redirects::REDIRECT_JAVASCRIPT?>" id="redirect_type_js">
            <label class="form-check-label" for="redirect_type_js">JavaScript</label>
        </div>
        <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="redirect_type[]" value="<?=Redirects::REDIRECT_META?>" id="redirect_type_meta">
            <label class="form-check-label" for="redirect_type_meta">Meta Redirect</label>
        </div>
    </div>

    <div class="form-group">
        <label for="bot_limit">Лимит ботов</label>
        <input type="text" id="bot_limit" name="bot_limit" class="form-control" value="<?= $_SERVER['BOT_LIMIT'] ?>" placeholder="Введите лимит ботов">
    </div>

    <div class="form-group">
        <div class="checkbox">
            <label>
                <input type="checkbox" name="run_bots_immediately">
                Запустить ботов сразу
            </label>
        </div>
    </div>

    <input type="hidden" id="redirect_keys" name="redirect_keys">

    <div class="form-group">
        <button type="submit" class="btn btn-success">Save</button>
    </div>
</form>
