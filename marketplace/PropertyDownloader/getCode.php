<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$GLOBALS['APPLICATION']->RestartBuffer();   

function translit($s) {
    $s = (string) $s; 
    $s = trim($s); 
    $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); 
    $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
    return $s; 
  }

$values = $_POST["values"];
$flag = $_POST["flag"];

$db = new mysqli('localhost', 'bitrix0', 'ILcJtZ?M6W@uOVgj7zlX', 'sitemanager');
$db->set_charset('utf8');

$code = "";

if($flag == 0) {

    if($values[0] > 0) {

        $code = "Инфоблоки и их свойства: \n";

        $sql = $db->query("SELECT * FROM b_iblock WHERE ID = ".$values[0]);

        if($sql->num_rows > 0)
        {
            if($result = $sql->fetch_assoc())
            {

            $result["NAME"] = str_replace(')', '', str_replace('(', '', $result["NAME"]));

            $ib = str_replace(":", "", preg_replace('/\s*\(.*?\)/', '', str_replace(' ', '_', mb_strtoupper(translit($result["NAME"])))));

            $code.="define('IB_".$ib."', ".$result["ID"].");\n\n";

                $sql01 = $db->query("SELECT * FROM b_iblock_property WHERE IBLOCK_ID = ".$values[0]);

                if($sql01->num_rows > 0)
                {
                    while($result01 = $sql01->fetch_assoc())
                    {
                        $prop = $ib."_".str_replace(":","",preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result01["NAME"])))));

                        $code.="define('IB_".$prop."', ".$result01["ID"].");\n";

                        $sql02 = $db->query("SELECT * FROM b_iblock_property_enum WHERE PROPERTY_ID = ".$result01["ID"]);

                        if($sql02->num_rows > 0)
                        {
                            $code.="\n";

                            while($result02 = $sql02->fetch_assoc())
                            {
                                $propVal = str_replace(":","", $prop."_".preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result02["VALUE"])))));

                                $code.="define('IB_".$propVal."', ".$result02["ID"].");\n";
                            }

                            $code.="\n";
                        }
                    }
                }
            }
        }

        $code.="\n";
    }

    if(explode("---", $values[1])[0] > 0) {

        $code.= "Пользовательские поля: \n";

        $table = explode("---", $values[1])[0];

    $essence = explode("---", $values[1])[1];

        $essence = preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($essence))));

        $sql = $db->query("SELECT * FROM ".$table." LIMIT 1");

        if($sql->num_rows > 0)
        {
            if($result = $sql->fetch_assoc())
            {
                foreach ($result as $key => $value) {

                    $pos = mb_stripos($key, "UF_");

                    if($pos === 0) {
                        $sql01 = $db->query("SELECT * FROM b_user_field INNER JOIN b_user_field_lang ON b_user_field.ID = b_user_field_lang.USER_FIELD_ID
                            WHERE FIELD_NAME ='".$key."' AND b_user_field_lang.LANGUAGE_ID = 'ru'");

                        if($sql01->num_rows > 0)
                        {
                            if($result01 = $sql01->fetch_assoc())
                            {
                                if($result01["EDIT_FORM_LABEL"] != "") {

                                    $prop = str_replace(":","",
                                        $essence."_".preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result01["EDIT_FORM_LABEL"])))));

                                    $code.="define('".$prop."', '".$key."');"."\n";

                                } else {

                                    $prop = str_replace(":","", $essence."_".explode("UF_", $key."_WITHOUT_NAME")[1]);
                                    
                                    $code.="define('".$prop."', '".$key."');"."\n";
                                }
                            }
                        } else {

                            $prop = str_replace(":","", $essence."_".explode("UF_", $key."_WITHOUT_NAME")[1]);

                            $code.="define('".$prop."', '".$key."');"."\n";
                        }

                        $sql02 = $db->query("SELECT * FROM b_user_field INNER JOIN b_user_field_enum ON b_user_field.ID = b_user_field_enum.USER_FIELD_ID
                            WHERE b_user_field.FIELD_NAME ='".$key."'");

                        if($sql02->num_rows > 0)
                        {
                            $code.="\n";

                            while($result02 = $sql02->fetch_assoc())
                            {
                                $propVal = str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result02["VALUE"])))));

                                $code.="define('".$prop."_".$propVal."', ".$result02["ID"]."); \n";
                            }

                            $code.="\n";
                        }
                    } 
                }
            }
        }


    }

    if(explode("---", $values[2])[0] > 0) {

        $crm = explode("---", $values[2])[0];
        $name = explode("---", $values[2])[1];

        $code.="Статусы CRM:\n";

        if($crm == "LEAD") {
            $crm = "STATUS";
        }

        $sql = $db->query("SELECT * FROM b_crm_status WHERE ENTITY_ID LIKE '%".$crm."%'");

        if($sql->num_rows > 0)
        {
            while($result = $sql->fetch_assoc())
            {
                if($crm !== "DEAL") {
                    $sql01 = $db->query("SELECT * FROM b_crm_item_category WHERE ID=".$result["CATEGORY_ID"]);

                    if($sql01->num_rows > 0) {
                        if($result01 = $sql01->fetch_assoc()) {
                            $category = $result01["NAME"];
                        }
                    }
                } else {

                    $sql01 = $db->query("SELECT * FROM b_crm_deal_category WHERE ID=".$result["CATEGORY_ID"]);

                    if($sql01->num_rows > 0)
                    {
                        if($result01 = $sql01->fetch_assoc()) {
                            $category = $result01["NAME"];
                        }
                    } else {

                        if($result["CATEGORY_ID"] == 0) {
                            $category =  "ОБЪЕКТ";
                        }
                    }

                    $category = str_replace(')', '', str_replace('(', '', $category));

                }


                $code.= "define('".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($name)))))."_".
                    str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($category))))).
                    "_".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result["NAME"])))))."', '".$result["STATUS_ID"]."'); \n";
            }
        }

    }

    if($values[3] > 0) {
        $code.="Смарт-процесс:\n";

        $sql = $db->query("SELECT * FROM b_crm_dynamic_type WHERE ID =".$values[3]);

        if($sql->num_rows > 0)
        {
            if($result = $sql->fetch_assoc())
            {
                $code.= "define('CRM_".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($result["TITLE"])))))."', '".$result["TABLE_NAME"]."'); \n";
            }
        }

    }

    if(explode("---", $values[4])[0] > 0) {

        $crm = explode("---", $values[4])[0];
        $name = explode("---", $values[4])[1];

        $code.="Категории CRM:\n";

        if($crm != 2) {

            $sql = $db->query("SELECT * FROM b_crm_item_category WHERE ENTITY_TYPE_ID=".$crm);

            if($sql->num_rows > 0)
            {
                $i=0;

                while($result = $sql->fetch_assoc()) {
                    $category = $result["NAME"];

                    if(!$result["NAME"]) {
                        $category = $i;
                        $i++;
                    }

                    $code.= "define('".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($name)))))."_".
                        str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($category)))))."', ".$result["ID"]."); \n";
                }
            }
        } else {

            $sql = $db->query("SELECT * FROM b_crm_deal_category");

            if($sql->num_rows > 0)
            {
                while($result = $sql->fetch_assoc()) {
                    $category = $result["NAME"];

                    $category = str_replace(')', '', str_replace('(', '', $category));

                    $code.= "define('".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($name)))))."_".
                        str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($category)))))."', ".$result["ID"]."); \n";
                }

                $category= "ОБЪЕКТ";

                $code.= "define('".str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($name)))))."_".
                    str_replace(":","", preg_replace('/\s*\(.*?\)/', '',str_replace(' ', '_', mb_strtoupper(translit($category)))))."', ".$result["ID"]."); \n";
            }

        }
    }
}

if($flag == 1) {

    $code = "";

    $codeWarnings = "";

    if($values[0] > 0) {

        CModule::IncludeModule("iblock");

        $res = CIBlock::GetByID($values[0]);

        if($ar_res = $res->GetNext()) {

          $code.='/////////// Инфоблок "'.$ar_res["NAME"].'" ///////////////

$ib = new CIBlock;

$arFields = Array(
    "ACTIVE" => "Y",
    "NAME" => "'.$ar_res["NAME"].'",
    "CODE" =>  "'.$ar_res["CODE"].'",
    "IBLOCK_TYPE_ID" => '.$ar_res["IBLOCK_TYPE_ID"].',
    "SITE_ID" => Array("en", "de"),
    "DESCRIPTION" => "'.$ar_res["DESCRIPTION"].'",
    "DESCRIPTION_TYPE" => "'.$ar_res["DESCRIPTION_TYPE"].'",
    "GROUP_ID" => Array("2"=>"X")
);

$ID = $ib->Add($arFields);

if($ID > 0) {
';

        $res = CIBlock::GetProperties($values[0], Array(), Array());

        while($res_arr = $res->Fetch()) {
            //echo print_r($res_arr);

            if(is_array($res_arr["USER_TYPE_SETTINGS"]) && is_countable($res_arr["USER_TYPE_SETTINGS"]) && count($res_arr["USER_TYPE_SETTINGS"]) > 0) {

                 $str = http_build_query($res_arr["USER_TYPE_SETTINGS"], ' ', ' ');

                if (str_contains($str, "DYNAMIC_") !== FALSE) {

                    preg_match_all('/DYNAMIC_(\d+)/', $str, $matches);
                    $dynamicNumbers = $matches[1];

                    $strSmProcs = "";

                    foreach($dynamicNumbers as $number) {
                        $sql03 = $db->query("SELECT * FROM b_crm_dynamic_type WHERE ENTITY_TYPE_ID =".$number);

                        if($sql03->num_rows > 0)
                        {
                           if($result03 = $sql03->fetch_assoc()) {
                            $strSmProcs.=$result03["TITLE"].", ";
                           }
                        }
                    }

                    $strSmProcs = substr($strSmProcs, 0, -2);

                    $codeWarnings.= "
".$res_arr["NAME"]." (привязка к смарт-процессам: ".$strSmProcs.")";

                    //foreach($res_arr["USER_TYPE_SETTINGS"] as $key => $value) {}
                }

                $res_arr["USER_TYPE_SETTINGS"] = print_r($res_arr["USER_TYPE_SETTINGS"], true);

            } 
            else {
                $res_arr["USER_TYPE_SETTINGS"] = "'".$res_arr["USER_TYPE_SETTINGS"]."'";
            }

            if($res_arr["LINK_IBLOCK_ID"] > 0) {

                $sql03 = $db->query("SELECT * FROM b_iblock WHERE ID =".$res_arr["LINK_IBLOCK_ID"] );

                if($sql03->num_rows > 0)
                {
                   if($result03 = $sql03->fetch_assoc()) {
                        $strIblock=$result03["NAME"];
                   }
                }

                $codeWarnings.= "
".$res_arr["NAME"]." (привязка к инфоблоку: ".$strIblock.")";
                                
            } else {
                $res_arr["LINK_IBLOCK_ID"] = 0;
            }

            $code.='
//////////// Свойство "'.$res_arr["NAME"].'" //////////////////    

$ibp = new CIBlockProperty;

$arFields = Array(
    "NAME" => "'.$res_arr["NAME"].'",
    "ACTIVE" => "'.$res_arr["ACTIVE"].'",
    "CODE" => "'.$res_arr["CODE"].'",
    "PROPERTY_TYPE" => "'.$res_arr["PROPERTY_TYPE"].'",
    "IBLOCK_ID" => $ID,
    "DEFAULT_VALUE" => "'.$res_arr["DEFAULT_VALUE"].'",
    "ROW_COUNT" => "'.$res_arr["ROW_COUNT"].'",
    "COL_COUNT" => "'.$res_arr["COL_COUNT"].'",
    "LIST_TYPE" => "'.$res_arr["LIST_TYPE"].'",
    "MULTIPLE" => "'.$res_arr["MULTIPLE"].'",
    "FILE_TYPE" => "'.$res_arr["FILE_TYPE"].'",
    "MULTIPLE_CNT" => "'.$res_arr["MULTIPLE_CNT"].'",
    "USER_TYPE" => "'.$res_arr["USER_TYPE"].'",
    "USER_TYPE_SETTINGS" => '.trim($res_arr["USER_TYPE_SETTINGS"]).',
    "LINK_IBLOCK_ID" => '.$res_arr["LINK_IBLOCK_ID"].',
);

$ibp->Add($arFields);

if(!($ibp > 0)) {
    echo "Ошибка создания свойства '.$res_arr["NAME"].'";
}

////////////////////////////////////////////////////////////
';
        }

        $code.='} else {

echo "Ошибка создания инфоблока '.$ar_res["NAME"].'";

}';
            
        }

    }

    if($codeWarnigs !== "") {
        $codeWarnings="В коде следующих свойств инфоблока имееются привязки, которые необходимо предварительно сверить с целевым битриксом:
".$codeWarnings;
    }

    $code = $codeWarnings."

".$code;
}

echo $code;


?>