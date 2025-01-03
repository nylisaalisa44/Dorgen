<!DOCTYPE html>

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Result</title>
    <style>
        .btn-back {
            display: inline-block;
            font-weight: 400;
            color: #ffffff;
            text-align: center;
            border: 1px solid transparent;
            padding: .375rem .75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: .25rem;
            background-color: #007bff;
        }
        .btn-back:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>

<div class="form-group">
	<?= \yii\helpers\Html::a('Back', ['index'], ['class' => 'btn-back']) ?>
</div>
<br>

<div id="messages"></div>

<script>
  function fetchMessage() {
    fetch('/panel/cf-accounts/get-message')
      .then(response => response.json())
      .then(data => {
        if (data.message) {
          document.getElementById('messages').innerHTML += `<p>${data.message}</p>`;
        }
        // Повторяем запрос
        fetchMessage();
      })
      .catch(error => {
        console.error('Ошибка:', error);
        setTimeout(fetchMessage, 1000);
      });
  }

  fetchMessage(); // Запускаем получение сообщений при загрузке страницы
</script>

</body>
</html>