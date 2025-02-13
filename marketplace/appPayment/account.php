<?php
header('Content-Type: text/html; charset=utf-8');

$db = new mysqli('localhost', 'bitrix0', 'ILcJtZ?M6W@uOVgj7zlX', 'sitemanager');
$db->set_charset('utf-8');

$arUser = array();
$arAcc  = array();
$arComp = array();
$arAccComp = array();
$arContent = array();
$sql = $db->query("SELECT `ID`, CONCAT(`LAST_NAME`, ' ', `NAME`) AS `user` FROM `b_user`");
while($result = $sql->fetch_assoc())
{
    $arUser[$result['ID']] = $result['user'];
}

$sql = $db->query("SELECT `b`.`ID`, `c`.`TITLE`, `r`.`ENTITY_ID`,  `b`.`RQ_BANK_NAME`, `b`.`RQ_ACC_NUM`
                   FROM `b_crm_bank_detail` AS `b` 
                   LEFT JOIN `b_crm_requisite` AS `r` ON `r`.`ID` = `b`.`ENTITY_ID`
                   LEFT JOIN `b_crm_company` AS `c` ON `c`.`ID` = `r`.`ENTITY_ID`
                   WHERE `c`.`IS_MY_COMPANY` = 'Y'
                   ORDER BY `r`.`ENTITY_ID` ASC");
while($result = $sql->fetch_assoc())
{
    $arComp[$result['ENTITY_ID']] = $result['TITLE'];
    $arAccComp[$result['ID']] = $result['ENTITY_ID'];
    $arAcc[$result['ID']] = $result['RQ_ACC_NUM'];
}


$arFiles = scandir('./access');
foreach($arFiles as $file)
{
    if($file != '.' && $file != '..')
    {
        $f = unserialize(file_get_contents('./access/'.$file));
        if(!empty($f['acc']))
        {
            foreach($f['acc'] as $acc)
            {
                $arContent[$arComp[$arAccComp[$acc]]][$arAcc[$acc]][$file] = $arUser[$file];
            }
        }
    }
}
echo '<pre>';
print_r($arContent);