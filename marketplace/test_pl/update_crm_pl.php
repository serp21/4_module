<?php

require_once(__DIR__.'/crest.php');


//$field_id = (int)CRestCurrent::call('app.option.get', ['option' => 'crm_pl_field_crm_id'])['result'];
$field_id = (int)CRest::call('app.option.get', ['option' => 'crm_field_id'])['result'];
if (!$field_id)
	exit;

if (!isset($_POST['selected_crms']))
	exit;


$selected_crms = $_POST['selected_crms'];
//if (strlen($selected_crms))
//{
	$selected_crms_arr = explode('|', $selected_crms);
	//if (count($selected_crms_arr))
	//{
		$settings = [];
		foreach ($selected_crms_arr as $crm_info)
		{
			$crm_info_arr = explode(':', $crm_info);
			if (count($crm_info_arr) === 2)
			{
				$entityCode = $crm_info_arr[0];
				$is_sel = (int)$crm_info_arr[1];
				$settings[$entityCode] = $is_sel ? 'Y' : 'N';
			}
		}

		if (count($settings))
		{
			$result = CRest::call('userfieldconfig.update',
				[	'moduleId'	=> 'crm',
					'id'		=> $field_id,
					'field'		=> [ 'settings' => $settings ]
				]);
		}
	//}
//}

//print_r($result);
echo isset($result['result']);

?>