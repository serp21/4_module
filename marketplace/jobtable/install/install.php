<?php
require_once(__DIR__ . '/../crest/crest.php');
require_once(__DIR__ . '/../app/config/config.php');

$result = CRest::installApp();
if ($result['rest_only'] === false): ?>

	<head>
		<script src="//api.bitrix24.com/api/v1/"></script>
		<?php if ($result['install'] == true): ?>
			<script>
				BX24.init(function() {
					BX24.installFinish();
				});
			</script>
		<?php endif; ?>
	</head>

	<body>
		<?php if ($result['install'] == true) {
			// CRest::call("event.bind", array(
			//     "event" => "ONAPPTEST",
			//     "handler" => $DOMAIN . $MARKET_PATH . $APP_NAME . 'handlers/useradd.php'
			// ));

			// При установке также нужно будет проверить 
			// если ли инфоблок Штатное распиание, если нет, то создать.

			echo "Установка завершена успешно!";
		} else {
			echo "При установке возникли ошибки, пожалуйста, попробуйте снова или обратитесь к разработчикам!";
		} ?>
	</body>
<?php endif;
