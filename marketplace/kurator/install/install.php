<?php
require_once (__DIR__.'/../config/config.php');
require_once (__DIR__.'/../crest/crest.php');

$result = CRest::installApp();

if($result['rest_only'] === false):?>
	<head>
		<script src="//api.bitrix24.com/api/v1/"></script>
		<?php if($result['install'] == true):?>
			<script>
				BX24.init(function(){
					BX24.installFinish();
				});
			</script>
		<?php endif;?>
	</head>
	<body>
		<?php if($result['install'] == true) {
			
			$result01 = CRest::call("event.bind", array(
			    'event' => 'onTaskAdd',
			    "handler" => $DOMAIN . $MARKET_PATH . $APP_NAME . 'handlers/taskAdd.php' 
			));

			$result01 = CRest::call("event.bind", array(
			    'event' => 'onTaskUpdate',
			    "handler" => $DOMAIN . $MARKET_PATH . $APP_NAME . 'handlers/taskUpdate.php' 
			));

			CRest::call("event.bind", array(
			    "event" => "ONAPPTEST",
			    "handler" => $DOMAIN . $MARKET_PATH . $APP_NAME . 'handlers/taskUpdate.php'
			));
			echo "Установка завершена успешно! ";
		} else {
			echo "При установке возникли ошибки, пожалуйста, попробуйте снова или обратитесь к разработчикам!";
		} ?>
	</body>
<?php endif;