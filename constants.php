<?php
//////////////////////////// Подключение к бд ///////////////////////////////
define("DB_HOST", 'localhost');
define("DB_USER_BD", 'bitrix0');
define("DB_PASSWORD", 'ILcJtZ?M6W@uOVgj7zlX');
define("DB_NAME", 'sitemanager');

//////////////////////////// Подключение к бд ///////////////////////////////

//////////////////////////// Домен битрикса ///////////////////////////////

define("BITRIX_DOMAIN", 'https://dev.vestrus.ru/');

//////////////////////////// Домен битрикса///////////////////////////////

define('USER_BELOGLAZOV', 26);
define('USER_ROVNOV_A', 3);
define('USER_ROVNOV_E', 7);
define('USER_DOLGOVA', 722);
define('USER_KURAMOV', 93);
define('USER_SLUNIAEV', 100);
define('USER_NIKITIN', 121);
define('USER_NIKITINA', 128);
define('USER_MITIN', 810);

////////////////////////////////////////////////////////////////////////////

define('TIMEMAN_SCHEDULE_WIFI', 1);
define('TIMEMAN_SCHEDULE_REMOTE', 2);
define('TIMEMAN_SCHEDULE_BROWSER', 4);
define('TIMEMAN_SCHEDULE_FACE', 5);

///////////////////////////////////////////////////////////////////////

define('TASK_STATUS_CLOSE', 5);
define('TASK_STATUS_RUNNING', 3);
define('TASK_STATUS_WAITING_CONTROL', 4);
define('TASK_STATUS_POSTPONED', 6);
define('TASK_STATUS_WAITING_RUNNING', 2);

/////////////////////////////////////////////////////////////////////

define('GROUPS_WORKERS', 12); 

//////////////////////////////////////////////////////////////////////

define('IB_SECTION_UNIVEST', 1);
define('IB_SECTION_OKPO', 32);

///////////////////////////////////////////////////////////////////////

define('IB_PODRAZDELENIYA', 3);

///////////////////////////////////////////////////////////////////////

define('IB_DOSKA_POCHETA', 2);

define('IB_DOSKA_POCHETA_POLZOVATEL', 5);
define('IB_DOSKA_POCHETA_BLAGODARNOST', 6);

define('IB_DOSKA_POCHETA_BLAGODARNOST_PIVO', 20);
define('IB_DOSKA_POCHETA_BLAGODARNOST_TORT', 15);
define('IB_DOSKA_POCHETA_BLAGODARNOST_KORONA', 13);
define('IB_DOSKA_POCHETA_BLAGODARNOST_KUBOK', 11);
define('IB_DOSKA_POCHETA_BLAGODARNOST_KOKTEYL', 14);
define('IB_DOSKA_POCHETA_BLAGODARNOST_FLAG', 17);
define('IB_DOSKA_POCHETA_BLAGODARNOST_CVETY', 21);
define('IB_DOSKA_POCHETA_BLAGODARNOST_PODAROK', 10);
define('IB_DOSKA_POCHETA_BLAGODARNOST_SERDCE', 19);
define('IB_DOSKA_POCHETA_BLAGODARNOST_DENGI', 12);
define('IB_DOSKA_POCHETA_BLAGODARNOST_ZAMECHANIE', 81);
define('IB_DOSKA_POCHETA_BLAGODARNOST_VYGOVOR', 82);
define('IB_DOSKA_POCHETA_BLAGODARNOST_ULYBKA', 83);
define('IB_DOSKA_POCHETA_BLAGODARNOST_ZVEZDA', 18);
define('IB_DOSKA_POCHETA_BLAGODARNOST_1_MESTO', 16);
define('IB_DOSKA_POCHETA_BLAGODARNOST_USPEH', 9);
define('IB_DOSKA_POCHETA_BLAGODARNOST_PREDUPREJDENIE', 80);

define('IB_DOSKA_POCHETA_POLZOVATELI', 7);
define('IB_DOSKA_POCHETA_I', 440);
define('IB_DOSKA_POCHETA_TRIP', 441);
define('IB_DOSKA_POCHETA_CHECKED', 476);


////////////////////////////////////////////////////

define('IB_GRAFIK_OTSUTSTVIY', 1);

define('IB_GRAFIK_OTSUTSTVIY_POLZOVATEL', 1);
define('IB_GRAFIK_OTSUTSTVIY_SOSTOYANIE_ZAVERSHENIYA', 2);
define('IB_GRAFIK_OTSUTSTVIY_SOSTOYANIE', 3);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA', 4);

define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_KOMANDIROVKA', 2);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTPUSK_DEKRETNYY', 4);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_BOLNICHNYY', 3);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTGUL_ZA_SVOY_SCHET', 5);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_DRUGOE', 7);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_PERSONALNYE_KALENDARI', 8);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTPUSK_UCHEBNYY', 86);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_PROGUL', 6);
define('IB_GRAFIK_OTSUTSTVIY_TIP_OTSUTSTVIYA_OTPUSK_EJEGODNYY', 1);

//////////////////////////////////////////////////////////////////////////

define('IB_PLATEJI', 28);

define('IB_PLATEJI_DATA', 146);
define('IB_PLATEJI_SUMMA', 147);
define('IB_PLATEJI_PLATEJNOE_PORUCHENIE', 149);
define('IB_PLATEJI_INN_KONTRAGENTA', 150);
define('IB_PLATEJI_NAZVANIE_KONTRAGENTA', 151);
define('IB_PLATEJI_NASHA_KOMPANIYA', 152);
define('IB_PLATEJI_RASCHETNYY_SCHET', 153);
define('IB_PLATEJI_NAZNACHENIE_PLATEJA', 154);
define('IB_PLATEJI_CRM', 155);
define('IB_PLATEJI_ID_SCHETA', 156);
define('IB_PLATEJI_ID_ZADACHI', 157);
define('IB_PLATEJI_SSYLKA_NA_SCHET', 158);
define('IB_PLATEJI_SSYLKA_NA_ZADACHU', 159);
define('IB_PLATEJI_NALICHNYY_PLATEJ', 160);
define('IB_PLATEJI_NACHISLENIE_ZP', 161);
define('IB_PLATEJI_DATA_OBRABOTKI_PLATEJA', 162);
define('IB_PLATEJI_OPERATOR', 163);
define('IB_PLATEJI_OTVETSTVENNYY_SOTRUDNIK', 164);
define('IB_PLATEJI_KOMMENTARIY', 165);
define('IB_PLATEJI_OTVETSTVENNYY_ZA_PLATEJ', 166);
define('IB_PLATEJI_KOMANDIROVKA', 254);
define('IB_PLATEJI_KONTRAGENT', 260);
define('IB_PLATEJI_SSYLKA_NA_FAYL', 270);
define('IB_PLATEJI_FAYL', 271);
define('IB_PLATEJI_NDS', 420);
define('IB_PLATEJI_BALANS_OPERATORA', 421);
define('IB_PLATEJI_BALANS_VSEH_PLATEJEY', 422);
define('IB_PLATEJI_ARENDA_TS', 436);
define('IB_PLATEJI_SOZDAT_SCHET_NA_OPLATU', 474);

////////////////////////////////////////////////////////

define('IB_ZARABOTNAYA_PLATA', 27);

define('IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA', 137);
define('IB_ZARABOTNAYA_PLATA_SUMMA', 138);
define('IB_ZARABOTNAYA_PLATA_SOTRUDNIK', 139);
define('IB_ZARABOTNAYA_PLATA_OTVETSTVENNYY', 140);
define('IB_ZARABOTNAYA_PLATA_BALANS_NA_MOMENT_NACHISLENIYA', 141);
define('IB_ZARABOTNAYA_PLATA_ID_SOTRUDNIKA', 142);
define('IB_ZARABOTNAYA_PLATA_STRUKTURNOE_PODRAZDELENIE', 143);
define('IB_ZARABOTNAYA_PLATA_MINIMALNAYA_VYPLATA', 144);
define('IB_ZARABOTNAYA_PLATA_MAKSIMALNAYA_VYPLATA', 145);
define('IB_ZARABOTNAYA_PLATA_PREMIYA', 258);
define('IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA', 262);

define('IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER', 84);
define('IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR', 85);
define('IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA', 90);
define('IB_ZARABOTNAYA_PLATA_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR', 89);

define('IB_ZARABOTNAYA_PLATA_PREDVARITELNAYA_PREMIYA', 361);
define('IB_ZARABOTNAYA_PLATA_PREMIYA_PODTVERJDENA', 368);
define('IB_ZARABOTNAYA_PLATA_SDELNO', 412);
define('IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_OKLADA', 445);
define('IB_ZARABOTNAYA_PLATA_DATA_NACHISLENIYA_PREMII', 446);
define('IB_ZARABOTNAYA_PLATA_OKLAD', 454);

/////////////////////////////////////////////////

define('ZADACHI_ELEMENTY_CRM', 'UF_CRM_TASK');
define('ZADACHI_LOAD_FILES', 'UF_TASK_WEBDAV_FILES');
define('ZADACHI_MAIL_MESSAGE_WITHOUT_NAME', 'UF_MAIL_MESSAGE');
define('ZADACHI_STARYY_ID', 'UF_OLD_ID');
define('ZADACHI_FOT', 'UF_FOT');
define('ZADACHI_FOT_OTVETSTVENNOGO', 'UF_FOT_RESPONSE');
define('ZADACHI_TIP_OBEKTA', 'UF_TASK_OBJECT');
define('ZADACHI_NAPRAVLENIE', 'UF_TASK_DIRECTION');
define('ZADACHI_DATA_PERVOGO_ZAKRYTIYA', 'UF_CLOSEDATE_FIRST');
define('ZADACHI_ID_PREJNEGO_RODITELYA', 'UF_OLD_PARENT_ID');
define('ZADACHI_ID_PREJNEY_GRUPPY', 'UF_OLD_GROUP_ID');
define('ZADACHI_IS_FOLDER_WITHOUT_NAME', 'UF_IS_FOLDER');
define('ZADACHI_FOLDER_ID_WITHOUT_NAME', 'UF_FOLDER_ID');
define('ZADACHI_CATALOG_HREF_WITHOUT_NAME', 'UF_CATALOG_HREF');
define('ZADACHI_ID_SHABLONA', 'UF_TEMPLATE_ID');
define('ZADACHI_OLD_PT_WITHOUT_NAME', 'UF_OLD_PT');
define('ZADACHI_TASK_DISK_DETACH_WITHOUT_NAME', 'UF_TASK_DISK_DETACH');
define('ZADACHI_TASK_FOLDER_MEMBERS_WITHOUT_NAME', 'UF_TASK_FOLDER_MEMBERS');
define('ZADACHI_OLD_CRM_ATTACH_WITHOUT_NAME', 'UF_OLD_CRM_ATTACH');
define('ZADACHI_PREDYDUSHCHAYA_STADIYA', 'UF_PREV_STAGE');
define('ZADACHI_FOT_RUKOVODITELYA', 'UF_FOT_HEAD');
define('ZADACHI_OLD_DEADLINE_WITHOUT_NAME', 'UF_OLD_DEADLINE');
define('ZADACHI_IS_FOT_APP_WITHOUT_NAME', 'UF_IS_FOT_APP');
define('ZADACHI_PREV_RESPONS_WITHOUT_NAME', 'UF_PREV_RESPONS');
define('ZADACHI_DEPTH_LEVEL_WITHOUT_NAME', 'UF_DEPTH_LEVEL');
define('ZADACHI_FOT_FIRST_WITHOUT_NAME', 'UF_FOT_FIRST');
define('ZADACHI_PREV_PRIORITY_WITHOUT_NAME', 'UF_PREV_PRIORITY');

// ========== 18/07/2024 ===========
/////////////////////////////Инфоблок РН карт (корп) - Топливо ТС Корпоративные//////////////////////////////////

define('IB_TOPLIVO_TS_KORPORATIVNYE', 42);

define('IB_TOPLIVO_TS_KORPORATIVNYE_DATA', 210);
define('IB_TOPLIVO_TS_KORPORATIVNYE_LITRY', 211);
define('IB_TOPLIVO_TS_KORPORATIVNYE_CENA_ZA_LITR', 212);
define('IB_TOPLIVO_TS_KORPORATIVNYE_STOIMOST', 213);
define('IB_TOPLIVO_TS_KORPORATIVNYE_TOPLIVO', 214);
define('IB_TOPLIVO_TS_KORPORATIVNYE_OPISANIE', 215);
define('IB_TOPLIVO_TS_KORPORATIVNYE_KARTA', 216);
define('IB_TOPLIVO_TS_KORPORATIVNYE_DERJATEL_KARTY', 217);
define('IB_TOPLIVO_TS_KORPORATIVNYE_CRM', 218);
define('IB_TOPLIVO_TS_KORPORATIVNYE_ZADACHA', 219);
define('IB_TOPLIVO_TS_KORPORATIVNYE_SSYLKA_NA_ZADACHU', 220);
define('IB_TOPLIVO_TS_KORPORATIVNYE_OPERATOR', 221);
define('IB_TOPLIVO_TS_KORPORATIVNYE_ID_SDELKI', 222);
define('IB_TOPLIVO_TS_KORPORATIVNYE_ZARPLATA', 223);
define('IB_TOPLIVO_TS_KORPORATIVNYE_OPISANIE', 250);
define('IB_TOPLIVO_TS_KORPORATIVNYE_KOMANDIROVKA', 251);
define('IB_TOPLIVO_TS_KORPORATIVNYE_ID_KOMANDIROVKI', 252);
define('IB_TOPLIVO_TS_KORPORATIVNYE_KOMANDIROVKA', 255);
define('IB_TOPLIVO_TS_KORPORATIVNYE_VREMYA', 410);
define('IB_TOPLIVO_TS_KORPORATIVNYE_PROBEG', 411);
define('IB_TOPLIVO_TS_KORPORATIVNYE_MARKA_AVTO', 415);
define('IB_TOPLIVO_TS_KORPORATIVNYE_AVTO', 439);

////////////////Инфоблок Я.Заправки (аренда) - Топливо ТС Арендованные////////////////////////
// define('IB_TOPLIVO_TS_ARENDOVANNYE', 123);

// define('IB_TOPLIVO_TS_ARENDOVANNYE_DATA', 492);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_LITRY', 493);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_STOIMOST', 494);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_TOPLIVO', 495);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_OPISANIE', 496);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_SOTRUDNIK', 497);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_CRM', 498);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_ZADACHA', 499);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_OPERATOR', 500);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_ZARPLATA', 501);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_KOMANDIROVKA', 502);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_PROBEG', 503);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_AVTO', 504);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_MARKA_AVTO', 505);
// define('IB_TOPLIVO_TS_ARENDOVANNYE_PUTEVOY_LIST', 506);

define('IB_YA_ZAPRAVKI_ARENDA', 123);

define('IB_YA_ZAPRAVKI_ARENDA_DATA', 492);
define('IB_YA_ZAPRAVKI_ARENDA_LITRY', 493);
define('IB_YA_ZAPRAVKI_ARENDA_STOIMOST', 494);
define('IB_YA_ZAPRAVKI_ARENDA_TOPLIVO', 495);
define('IB_YA_ZAPRAVKI_ARENDA_OPISANIE', 496);
define('IB_YA_ZAPRAVKI_ARENDA_SOTRUDNIK', 497);
define('IB_YA_ZAPRAVKI_ARENDA_CRM', 498);
define('IB_YA_ZAPRAVKI_ARENDA_ZADACHA', 499);
define('IB_YA_ZAPRAVKI_ARENDA_OPERATOR', 500);
define('IB_YA_ZAPRAVKI_ARENDA_ZARPLATA', 501);
define('IB_YA_ZAPRAVKI_ARENDA_KOMANDIROVKA', 502);
define('IB_YA_ZAPRAVKI_ARENDA_PROBEG', 503);
define('IB_YA_ZAPRAVKI_ARENDA_AVTO', 504);
define('IB_YA_ZAPRAVKI_ARENDA_MARKA_AVTO', 505);
define('IB_YA_ZAPRAVKI_ARENDA_PUTEVOY_LIST', 506);

/////////////////////////////////Инфоблок Я.Заправки (корп)//////////////////////////////////////
define('IB_YA_ZAPRAVKI_KORP', 136);

define('IB_YA_ZAPRAVKI_KORP_DATA', 631);
define('IB_YA_ZAPRAVKI_KORP_LITRY', 632);
define('IB_YA_ZAPRAVKI_KORP_STOIMOST', 633);
define('IB_YA_ZAPRAVKI_KORP_TOPLIVO', 634);
define('IB_YA_ZAPRAVKI_KORP_OPISANIE', 635);
define('IB_YA_ZAPRAVKI_KORP_SOTRUDNIK', 636);
define('IB_YA_ZAPRAVKI_KORP_CRM', 637);
define('IB_YA_ZAPRAVKI_KORP_ZADACHA', 638);
define('IB_YA_ZAPRAVKI_KORP_OPERATOR', 639);
define('IB_YA_ZAPRAVKI_KORP_ZARPLATA', 640);
define('IB_YA_ZAPRAVKI_KORP_KOMANDIROVKA', 641);
define('IB_YA_ZAPRAVKI_KORP_PROBEG', 642);
define('IB_YA_ZAPRAVKI_KORP_AVTO', 643);
define('IB_YA_ZAPRAVKI_KORP_MARKA_AVTO', 644);
define('IB_YA_ZAPRAVKI_KORP_PUTEVOY_LIST', 645);

//////////////////Инфоблок Путевые листы///////////////////////////////
define('IB_PUTEVYE_LISTY', 137);

define('IB_PUTEVYE_LISTY_SDELKA', 646);
define('IB_PUTEVYE_LISTY_NOMER_ZADACHI', 647);
define('IB_PUTEVYE_LISTY_KOMANDIROVKA', 648);
define('IB_PUTEVYE_LISTY_DATA_NACHALA_PROBEGA', 649);
define('IB_PUTEVYE_LISTY_DATA_OKONCHANIYA_PROBEGA', 650);
define('IB_PUTEVYE_LISTY_NACHALNYY_PROBEG', 651);
define('IB_PUTEVYE_LISTY_KONECHNYY_PROBEG', 652);
define('IB_PUTEVYE_LISTY_PROBEG', 653);
define('IB_PUTEVYE_LISTY_TARIF', 654);
define('IB_PUTEVYE_LISTY_SUMMA_ARENDY', 655);
define('IB_PUTEVYE_LISTY_SOTRUDNIK', 656);
define('IB_PUTEVYE_LISTY_AVTOMOBIL', 657);
define('IB_PUTEVYE_LISTY_PUTEVOY_LIST', 658);
define('IB_PUTEVYE_LISTY_DATA_I_VREMYA_NACHALA_PROBEGA', 659);
define('IB_PUTEVYE_LISTY_DATA_I_VREMYA_OKONCHANIYA_PROBEGA', 660);
define('IB_PUTEVYE_LISTY_OSTATOK_TOPLIVA_PRI_VYEZDE', 661);
define('IB_PUTEVYE_LISTY_OSTATOK_TOPLIVA_PRI_VOZVRASHCHENII', 662);
define('IB_PUTEVYE_LISTY_AVTOMOBIL_MTR', 663);
define('IB_PUTEVYE_LISTY_RASSTOYANIE_PO_DANNYM_GEODATCHIKA', 664);

// ========== 18/07/2024 ===========

// ========== 20/09/2024 ===========
//////////////////Инфоблок РН карт (аренда) ///////////////////////////////
define('IB_RN_KART_ARENDA', 142);

define('IB_RN_KART_ARENDA_DATA', 667);
define('IB_RN_KART_ARENDA_LITRY', 668);
define('IB_RN_KART_ARENDA_CENA_ZA_LITR', 669);
define('IB_RN_KART_ARENDA_STOIMOST', 670);
define('IB_RN_KART_ARENDA_TOPLIVO', 671);
define('IB_RN_KART_ARENDA_KARTA', 672);
define('IB_RN_KART_ARENDA_DERJATEL_KARTY', 673);
define('IB_RN_KART_ARENDA_CRM', 674);
define('IB_RN_KART_ARENDA_ZADACHA', 675);
define('IB_RN_KART_ARENDA_SSYLKA_NA_ZADACHU', 676);
define('IB_RN_KART_ARENDA_KOMANDIROVKA', 677);
define('IB_RN_KART_ARENDA_AVTO', 678);
define('IB_RN_KART_ARENDA_PUTEVOY_LIST', 679);
define('IB_RN_KART_ARENDA_ZARPLATA', 680);
define('IB_RN_KART_ARENDA_OPISANIE', 681);
// ========== 20/09/2024 ===========
///////////////////////////////////////////////////////////////////////

define('POLZOVATELI_ELEMENTY_CRM', 'UF_USER_CRM_ENTITY');
define('POLZOVATELI_IM_USERS_CAN_FIND', 'UF_IM_SEARCH');
define('POLZOVATELI_CONNECTOR_MD5_WITHOUT_NAME', 'UF_CONNECTOR_MD5');
define('POLZOVATELI_VNUTRENNIY_TELEFON', 'UF_PHONE_INNER');
define('POLZOVATELI_POLZOVATEL_IZ_1S', 'UF_1C');
define('POLZOVATELI_INN', 'UF_INN');
define('POLZOVATELI_RAYON', 'UF_DISTRICT');
define('POLZOVATELI_LOGIN_SKYPE', 'UF_SKYPE');
define('POLZOVATELI_SSYLKA_NA_CHAT_V_SKYPE', 'UF_SKYPE_LINK');
define('POLZOVATELI_ZOOM', 'UF_ZOOM');
define('POLZOVATELI_TWITTER', 'UF_TWITTER');
define('POLZOVATELI_FACEBOOK', 'UF_FACEBOOK');
define('POLZOVATELI_LINKEDIN', 'UF_LINKEDIN');
define('POLZOVATELI_XING', 'UF_XING');
define('POLZOVATELI_DRUGIE_SAYTY', 'UF_WEB_SITES');
define('POLZOVATELI_NAVYKI', 'UF_SKILLS');
define('POLZOVATELI_INTERESY', 'UF_INTERESTS');
define('POLZOVATELI_PODRAZDELENIYA', 'UF_DEPARTMENT');
define('POLZOVATELI_DATA_RASCHETA_OTPUSKA', 'UF_EMPLOYMENT_DATE');
define('POLZOVATELI_UCHET_RABOCHEGO_VREMENI', 'UF_TIMEMAN');

define('POLZOVATELI_UCHET_RABOCHEGO_VREMENI_VESTI_UCHET', 4); 
define('POLZOVATELI_UCHET_RABOCHEGO_VREMENI_NE_VESTI_UCHET', 5); 
define('POLZOVATELI_UCHET_RABOCHEGO_VREMENI_VESTI_UCHET', 15); 
define('POLZOVATELI_UCHET_RABOCHEGO_VREMENI_NE_VESTI_UCHET', 16); 

define('POLZOVATELI_MAKSIMALNOE_VREMYA_NACHALA_RABOCHEGO_DNYA', 'UF_TM_MAX_START');
define('POLZOVATELI_MINIMALNOE_VREMYA_ZAVERSHENIYA_RABOCHEGO_DNYA', 'UF_TM_MIN_FINISH');
define('POLZOVATELI_MINIMALNAYA_PRODOLJITELNOST_RABOCHEGO_DNYA', 'UF_TM_MIN_DURATION');
define('POLZOVATELI_OTCHET_ZA_DEN', 'UF_TM_REPORT_REQ');

define('POLZOVATELI_OTCHET_ZA_DEN_OBYAZATELEN', 6); 
define('POLZOVATELI_OTCHET_ZA_DEN_NE_OBYAZATELEN', 7); 
define('POLZOVATELI_OTCHET_ZA_DEN_NE_POKAZYVAT_FORMU_OTCHETA', 8); 
define('POLZOVATELI_OTCHET_ZA_DEN_OBYAZATELEN', 17); 
define('POLZOVATELI_OTCHET_ZA_DEN_NE_OBYAZATELEN', 18); 
define('POLZOVATELI_OTCHET_ZA_DEN_NE_POKAZYVAT_FORMU_OTCHETA', 19); 

define('POLZOVATELI_SHABLONY_OTCHETA', 'UF_TM_REPORT_TPL');
define('POLZOVATELI_SVOBODNYY_GRAFIK', 'UF_TM_FREE');

define('POLZOVATELI_SVOBODNYY_GRAFIK_VKLYUCHEN', 9); 
define('POLZOVATELI_SVOBODNYY_GRAFIK_VYKLYUCHEN', 10); 
define('POLZOVATELI_SVOBODNYY_GRAFIK_VKLYUCHEN', 20); 
define('POLZOVATELI_SVOBODNYY_GRAFIK_VYKLYUCHEN', 21); 

define('POLZOVATELI_VREMYA_SDACHI_OTCHETA', 'UF_TM_TIME');
define('POLZOVATELI_DEN', 'UF_TM_DAY');
define('POLZOVATELI_CHISLO_MESYACA', 'UF_TM_REPORT_DATE');
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA', 'UF_REPORT_PERIOD');

define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_DEN', 11); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_NEDELYA', 12); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_MESYAC', 13); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_OTCHET_NE_TREBUETSYA', 14); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_DEN', 22); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_NEDELYA', 23); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_MESYAC', 24); 
define('POLZOVATELI_CHASTOTA_SDACHI_OTCHETA_OTCHET_NE_TREBUETSYA', 25); 

define('POLZOVATELI_OTLOJENNOE_VREMYA_OTCHETA', 'UF_DELAY_TIME');
define('POLZOVATELI_DATA_POSLEDNEGO_OTCHETA', 'UF_LAST_REPORT_DATE');
define('POLZOVATELI_DATA_USTANOVKI_NASTROEK', 'UF_SETTING_DATE');
define('POLZOVATELI_DOPUSTIMYY_PROMEJUTOK_IZMENENIYA_VREMENI', 'UF_TM_ALLOWED_DELTA');
define('POLZOVATELI_PRIVYAZKI_DLYA_UCHETA_VNUTRENNEGO_SOVMESHCHENIYA', 'UF_WORK_BINDING');
define('POLZOVATELI_CALENDAR_SYNC_DATE', 'UF_BXDAVEX_CALSYNC');
define('POLZOVATELI_STARYY_ID', 'UF_OLD_ID');
define('POLZOVATELI_POLUCHAET_ZP', 'UF_GET_PAY');
define('POLZOVATELI_MINIMALNAYA_ZP', 'UF_PAY_MIN');
define('POLZOVATELI_MAKSIMALNAYA_ZP', 'UF_PAY_MAX');
define('POLZOVATELI_VOXIMPLANT_USER_PASSWORD', 'UF_VI_PASSWORD');
define('POLZOVATELI_VOXIMPLANT_USER_BACKPHONE', 'UF_VI_BACKPHONE');
define('POLZOVATELI_VOXIMPLANT_PHONE', 'UF_VI_PHONE');
define('POLZOVATELI_VOXIMPLANT_PHONE_PASSWORD', 'UF_VI_PHONE_PASSWORD');
define('POLZOVATELI_PUBLICHNYY_SOTRUDNIK_V_EKSTRANETE', 'UF_PUBLIC');
define('POLZOVATELI_MAC_ADRES', 'UF_MAC_ADDR');
define('POLZOVATELI_ONLAYN', 'UF_ONLINE');
define('POLZOVATELI_MAC_ADRES_WIFI_S_SSID_GKYW', 'UF_USR_1645793476521');
define('POLZOVATELI_MAC_ADRES_MOBILNOGO_TELEFONA', 'UF_USR_MAC_ADDR');
define('POLZOVATELI_V_SETI', 'UF_USR_1645822612916');
define('POLZOVATELI_WEBSERVICE/VPN_LOGIN', 'UF_USR_1664198885082');
define('POLZOVATELI_WEBSERVICE/VPN_PAROL', 'UF_USR_1664198912761');
define('POLZOVATELI_PODRAZDELENIE_PO_RABOTE_S_OBEKTAMI', 'UF_RO_DEPARTMENT');
define('POLZOVATELI_ID_DOLJNOSTI', 'UF_ID_POSITION');
define('POLZOVATELI_KOMPANIYA', 'UF_COMPANY');
define('POLZOVATELI_SDELNAYA_OPLATA', 'UF_USR_1685643895813');
define('POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA', 'UF_USR_1691651430895');

define('POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_PROIZVODSTVENNYY_SEKTOR', 72); 
define('POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_VSPOMOGATELNYY_SEKTOR', 73); 
define('POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_MENEDJER', 74); 
define('POLZOVATELI_SISTEMA_RABOTY_SOTRUDNIKA_RUKOVODITEL_PODRAZDELENIYA', 75); 

define('POLZOVATELI_DATA_NACHALA_RABOTY', 'UF_START_WORK');
define('POLZOVATELI_RESERVE_DEPART_WITHOUT_NAME', 'UF_RESERVE_DEPART');
define('POLZOVATELI_PRISYLAT_UVEDOMLENIYA_OB_OTKRYTII_FAYLOV', 'UF_USR_1701179698018');
define('POLZOVATELI_USR_1702557074349_WITHOUT_NAME', 'UF_USR_1702557074349');
define('POLZOVATELI_UVEDOMLYAT_OB_OTKRYTII_FAYLOV', 'UF_USR_1702587951179');
define('POLZOVATELI_POKAZYVAT_BALANS_FOTA', 'UF_BALANCE_FOT');
define('POLZOVATELI_OTOBRAJAT_BALANS', 'UF_USR_1706248730631');
define('POLZOVATELI_LAST_STRIKE_DATE_WITHOUT_NAME', 'UF_LAST_STRIKE_DATE');
define('POLZOVATELI_SERIYA_I_NOMER_VODITELSKOGO_UDOSTOVERENIYA', 'UF_DRIVER_LICENSE');
define('POLZOVATELI_KATEGORIYA', 'UF_DRIVER_LICENSE_CATEGORY');

define('POLZOVATELI_SYSTEM_WORK_ENUM', 515);

////////////////////////////////////////////////////////////////

define('IB_PEREVODY', 30);

define('IB_PEREVODY_DATA_PEREVODA', 167);
define('IB_PEREVODY_OTPRAVITEL', 168);
define('IB_PEREVODY_POLUCHATEL', 169);
define('IB_PEREVODY_SUMMA', 170);
define('IB_PEREVODY_KOMMENTARIY', 171);
define('IB_PEREVODY_ID_OTPRAVITELYA', 172);
define('IB_PEREVODY_ID_POLUCHATELYA', 173);
define('IB_PEREVODY_ID_ZADACHI', 230);

//////////////////////////////////////////////////////////////////////

define('MTR_STARYY_ID', 'UF_OLD_ID');
define('MTR_OPISANIE', 'UF_MTR_PROPERTY');
define('MTR_DATA_POSLEDNEY_INVENTARIZACII', 'UF_DATE_INVENT');
define('MTR_STATUS_INVENTARIZACII', 'UF_STATUS_INVENT');
define('MTR_MESTO_RAZMESHCHENIE_MTR', 'UF_LOCATION');

define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._201', 33); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._202', 34); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._203', 35); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._204', 36); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._205', 37); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._206', 38); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._207', 39); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._208', 40); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._209_', 41); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._210', 42); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._211', 43); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._212', 44); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._213', 45); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._214', 46); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._215', 47); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_KAB._216', 48); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_11_KAB._9', 49); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_1B', 50); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_1_ETAJ', 51); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_UDALENNO', 52); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_SUVOROVA_167/2', 53); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_SKLAD_AUSTRINA', 54); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_KULAKOVA_7', 55); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_ZARECHNYY', 56); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_2_ETAJ', 57); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_NOVOKUZNECK', 84); 
define('MTR_MESTO_RAZMESHCHENIE_MTR_MIRA_9A_RESEPSHN', 89); 

define('MTR_INVENTARNYY_NOMER', 'UF_INVENT_NUMBER');
define('MTR_SERIYNYY_NOMER', 'UF_SERIAL_NUMBER');
define('MTR_NOMER_PLOMBY', 'UF_CRM_7_SEAL_NUMBER');
define('MTR_MAC_ADRES', 'UF_CRM_7_MAC_ADDRESS_PC');
define('MTR_IP_ADRES', 'UF_CRM_7_IP_ADDRESS_PC');
define('MTR_TIP_USTROYSTVA', 'UF_CRM_7_DEVICE_TYPE');

define('MTR_TIP_USTROYSTVA_SISTEMNYY_BLOK', 60); 
define('MTR_TIP_USTROYSTVA_MONITOR', 61); 
define('MTR_TIP_USTROYSTVA_MONOBLOK', 62); 
define('MTR_TIP_USTROYSTVA_NOUTBUK', 63); 
define('MTR_TIP_USTROYSTVA_KOMMUTATOR', 64); 
define('MTR_TIP_USTROYSTVA_PERIFERIYA', 65); 
define('MTR_TIP_USTROYSTVA_IP-TELEFONIYA', 66); 
define('MTR_TIP_USTROYSTVA_PRINTER,_MFU', 67); 
define('MTR_TIP_USTROYSTVA_SERVER', 70); 
define('MTR_TIP_USTROYSTVA_MUZYKALNAYA_TEHNIKA', 71); 
define('MTR_TIP_USTROYSTVA_TRANSPORTNOE_SREDSTVO', 85); 
define('MTR_TIP_USTROYSTVA_SPECTEHNIKA', 86); 
define('MTR_TIP_USTROYSTVA_PECHAT', 87); 
define('MTR_TIP_USTROYSTVA_PROGRAMMA', 88); 

define('MTR_NOMER_PLOMBY_', 'UF_CRM_7_ADDITIONAL_SEAL_NUMBER');
define('MTR_MAC_ADRES_', 'UF_CRM_7_ADDITIONAL_MAC_ADDRESS_PC');
define('MTR_DATA_ZAMENY_TERMOPASTY', 'UF_CRM_7_DATE_TERMO');
define('MTR_NOMER_PLOMBY_', 'UF_CRM_7_ADDITIONAL_SEAL_NUMBER2');
define('MTR_AGENT_STAFFCOP', 'UF_CRM_7_AGENT');
define('MTR_PREV_ST_WITHOUT_NAME', 'UF_PREV_ST');
define('MTR_NOMER_DOGOVORA', 'UF_CRM_7_NUMBER_CONTRACT');
define('MTR_KOMPANIYA', 'UF_CRM_7_COMPANY');
define('MTR_NORMA_RASHODA_TOPLIVA', 'UF_CRM_7_FUEL_CONSUMPTION_RATE');
define('MTR_VIN_NOMER', 'UF_CRM_7_VIN');
define('MTR_GOS_NOMER', 'UF_CRM_7_GOSNOMER');
define('MTR_TARIF_', 'UF_CRM_7_TARIFF');
define('MTR_ZADOLJENNOST_PO_ARENDE_', 'UF_CRM_7_RENT_DEBT');
define('MTR_IDENTIFIKATOR_GPS-TREKERA', 'UF_CRM_7_GPSTRACKER_ID');

///////////////////////////////////////////////////////////////

define('MTR_OBSHCHEE_INOE', 'DT133_8:NEW'); 
define('MTR_OBSHCHEE_BANK_I_RN_I_SIM_KARTY', 'DT133_8:PREPARATION'); 
define('MTR_OBSHCHEE_AVTOTRANSPORT', 'DT133_8:CLIENT'); 
define('MTR_OBSHCHEE_REALIZOVANO', 'DT133_8:SUCCESS'); 
define('MTR_OBSHCHEE_SPISANO_ZA_BESPLATNO', 'DT133_8:FAIL'); 
define('MTR_OBSHCHEE_GEODEZICHESKOE_OBORUDOVANIE', 'DT133_8:UC_GQVOLC'); 
define('MTR_OBSHCHEE_PECHATI', 'DT133_8:UC_2YFWXW'); 
define('MTR_OBSHCHEE_POKUPKA', 'DT133_8:UC_GQPF1A'); 
define('MTR_OBSHCHEE_STROIT_INVENTAR', 'DT133_8:UC_1ZUGAH'); 
define('MTR_OBSHCHEE_OFISNOE_OBORUDOVANIE', 'DT133_8:1'); 
define('MTR_OBSHCHEE_UDALENO', 'DT133_8:2'); 
define('MTR_OBSHCHEE_INVENTARIZACIYA', 'DT133_8:3'); 
define('MTR_TRANSPORTNYE_SREDSTVA_POKUPKA', 'DT133_17:NEW'); 
define('MTR_TRANSPORTNYE_SREDSTVA_TS_NA_REMONTE', 'DT133_17:PREPARATION'); 
define('MTR_TRANSPORTNYE_SREDSTVA_TS_V_GARAJE', 'DT133_17:CLIENT'); 
define('MTR_TRANSPORTNYE_SREDSTVA_USPEH', 'DT133_17:SUCCESS'); 
define('MTR_TRANSPORTNYE_SREDSTVA_PROVAL', 'DT133_17:FAIL'); 
define('MTR_PECHATI_POKUPKA', 'DT133_18:NEW'); 
define('MTR_PECHATI_INVENTARIZACIYA', 'DT133_18:PREPARATION'); 
define('MTR_PECHATI_V_RABOTE', 'DT133_18:CLIENT'); 
define('MTR_PECHATI_USPEH', 'DT133_18:SUCCESS'); 
define('MTR_PECHATI_PROVAL', 'DT133_18:FAIL'); 
define('MTR_TRANSPORTNYE_SREDSTVA_TS_V_KOMANDIROVKE', 'DT133_17:UC_0X57B2'); 
define('MTR_TRANSPORTNYE_SREDSTVA_LICHNOE_TS', 'DT133_17:UC_A19SCU'); 
define('MTR_PROGRAMMNOE_OBESPECHENIE_POKUPKA', 'DT133_19:NEW'); 
define('MTR_PROGRAMMNOE_OBESPECHENIE_INVENTARIZACIYA', 'DT133_19:PREPARATION'); 
define('MTR_PROGRAMMNOE_OBESPECHENIE_V_ISPOLZOVANII', 'DT133_19:CLIENT'); 
define('MTR_PROGRAMMNOE_OBESPECHENIE_USPEH', 'DT133_19:SUCCESS'); 
define('MTR_PROGRAMMNOE_OBESPECHENIE_PROVAL', 'DT133_19:FAIL'); 

////////////////////////////CRM - Смарт-процессы////////////////////////////

define('CRM_MTR', 'b_crm_dynamic_items_133'); 
define('CRM_AVR', 'b_crm_dynamic_items_144'); 
define('CRM_AVR_ID', 144); // === 05/08/2024 ===
define('CRM_MTR_ID', 133); // === 09/08/2024 ===

define('CRM_ARENDA_TS_ID', 176); // === 08/10/2024 ===

define('CRM_SCHETA_ID', 159); // === 21/08/2024 ===
define('CRM_SCHET_NA_OPLATU', 'b_crm_dynamic_items_128'); 
define('CRM_SCHETA', 'b_crm_dynamic_items_159'); 

/////////////////////////// CRM ПЛ (корп) //////////////////////////////
define('CRM_PL_CORP', 'b_crm_dynamic_items_1056'); 
define('CRM_PL_CORP_ID', 1056);

#Свойства
define('CRM_PL_CORP_SDELKA', 'UF_CRM_18_DEAL');
define('CRM_PL_CORP_KOMANDIROVKA', 'UF_CRM_18_TRIP');
define('CRM_PL_CORP_DATA_I_VREMYA_NACHALA_PROBEGA', 'UF_CRM_18_START');
define('CRM_PL_CORP_DATA_I_VREMYA_OKONCHANIYA_PROBEGA', 'UF_CRM_18_END');
define('CRM_PL_CORP_NACHALNYY_PROBEG', 'UF_CRM_18_BEGINRUN');
define('CRM_PL_CORP_KONECHNYY_PROBEG', 'UF_CRM_18_ENDRUN');
define('CRM_PL_CORP_PROBEG', 'UF_CRM_18_RUN');
define('CRM_PL_CORP_TARIF', 'UF_CRM_18_TARIFF');
define('CRM_PL_CORP_SUMMA_ARENDY', 'UF_CRM_18_RENT');
define('CRM_PL_CORP_TRANSPORTNOE_SREDSTVO', 'UF_CRM_18_VEHICLE');
define('CRM_PL_CORP_PUTEVOY_LIST', 'UF_CRM_18_PL');
define('CRM_PL_CORP_OSTATOK_TOPLIVA_PRI_VYEZDE', 'UF_CRM_18_BEGINFUEL');
define('CRM_PL_CORP_OSTATOK_TOPLIVA_PRI_VOZVRASHCHENII', 'UF_CRM_18_ENDFUEL');
define('CRM_PL_CORP_RASSTOYANIE_PO_DANNYM_GEODATCHIKA', 'UF_CRM_18_GPSDISTANCE');
define('CRM_PL_CORP_TREK', 'UF_CRM_18_TRACK');
define('CRM_PL_CORP_NOMER_ZADACHI', 'UF_CRM_18_TASK');


///////////////////////// CRM ПЛ (аренда) ////////////////////
define('CRM_PL_ARENDA', 'b_crm_dynamic_items_1052'); 
define('CRM_PL_ARENDA_ID', 1052);

#Свойства
define('CRM_PL_ARENDA_SDELKA', 'UF_CRM_17_DEAL');
define('CRM_PL_ARENDA_KOMANDIROVKA', 'UF_CRM_17_TRIP');
define('CRM_PL_ARENDA_DATA_I_VREMYA_NACHALA_PROBEGA', 'UF_CRM_17_START');
define('CRM_PL_ARENDA_DATA_I_VREMYA_OKONCHANIYA_PROBEGA', 'UF_CRM_17_END');
define('CRM_PL_ARENDA_NACHALNYY_PROBEG', 'UF_CRM_17_BEGINRUN');
define('CRM_PL_ARENDA_KONECHNYY_PROBEG', 'UF_CRM_17_ENDRUN');
define('CRM_PL_ARENDA_PROBEG', 'UF_CRM_17_RUN');
define('CRM_PL_ARENDA_TARIF', 'UF_CRM_17_TARIFF');
define('CRM_PL_ARENDA_SUMMA_ARENDY', 'UF_CRM_17_RENT');
define('CRM_PL_ARENDA_TRANSPORTNOE_SREDSTVO', 'UF_CRM_17_VEHICLE');
define('CRM_PL_ARENDA_PUTEVOY_LIST', 'UF_CRM_17_PL');
define('CRM_PL_ARENDA_OSTATOK_TOPLIVA_PRI_VYEZDE', 'UF_CRM_17_BEGINFUEL');
define('CRM_PL_ARENDA_OSTATOK_TOPLIVA_PRI_VOZVRASHCHENII', 'UF_CRM_17_ENDFUEL');
define('CRM_PL_ARENDA_RASSTOYANIE_PO_DANNYM_GEODATCHIKA', 'UF_CRM_17_GPSDISTANCE');
define('CRM_PL_ARENDA_TREK', 'UF_CRM_17_TRACK');
define('CRM_PL_ARENDA_NOMER_ZADACHI', 'UF_CRM_17_TASK');
/////////////////////////////////////////////////////////


define('SDELKI_STARYY_ID', 'UF_OLD_ID');
define('SDELKI_NMCK', 'UF_CRM_61B21F8B7758C');
define('SDELKI_NACHALNAYA_CENA_', 'UF_CRM_61B21F8BDFF4C');
define('SDELKI_SSYLKA', 'UF_CRM_61B21F8C5A990');
define('SDELKI_NAZVANIE_FILTRA', 'UF_CRM_61B21F8CC8718');
define('SDELKI_KRATKOE_NAIMENOVANIE', 'UF_CRM_61B21F8D3E202');
define('SDELKI_NAIMENOVANIE', 'UF_CRM_61B21F8DA9457');
define('SDELKI_ORGANIZATOR', 'UF_CRM_61B21F8E2763B');
define('SDELKI_REESTROVYY_NOMER', 'UF_CRM_61B21F8E9929B');
define('SDELKI_SOTRUDNIK_OPR', 'UF_CRM_61B21F8F1FF18');
define('SDELKI_SPOSOB_ZAKUPKI', 'UF_CRM_61B21F8F8B909');
define('SDELKI_TIP_ZAKUPKI_S_EIS', 'UF_CRM_61B21F900A03A');
define('SDELKI_ELEKTRONNAYA_PLOSHCHADKA', 'UF_CRM_61B21F9078DDB');
define('SDELKI_OKONCHANIE_PRIEMA_ZAYAVOK', 'UF_CRM_61B21F90D374D');
define('SDELKI_DATA_PROVEDENIYA_AUKCIONA', 'UF_CRM_61B21F9151001');
define('SDELKI_REGION_ZAKAZCHIKA/ORGANIZATORA', 'UF_CRM_61B21F91B0103');
define('SDELKI_MYCOMPANY_ID_WITHOUT_NAME', 'UF_MYCOMPANY_ID');
define('SDELKI_DATA_ZAKLYUCHENIYA_DOGOVORA', 'UF_DATE_CONCLUSION');
define('SDELKI_SROK_PO_DOGOVORU', 'UF_DATE_TERM');
define('SDELKI_NOMER_DOGOVORA', 'UF_TERM_NUMBER');
define('SDELKI_DATA_NACHALA_PRIEMA_ZAYAVOK', 'UF_CRM_61E56A4750906');
define('SDELKI_ORGANIZATOR_', 'UF_CRM_61E56A49562C1');
define('SDELKI_NASHA_CENA', 'UF_CRM_61EA7AEC01E21');
define('SDELKI_.', 'UF_CRM_61EA7AEECAEBF');
define('SDELKI_CENA_POBEDITELYA', 'UF_CRM_61EA7AF16AF15');
define('SDELKI_DATA_PODVEDENIYA_ITOGOV', 'UF_CRM_61EA7AF42E55F');
define('SDELKI_ITOGOVYY_PROTOKOL', 'UF_CRM_61EA7AF702DED');
define('SDELKI_KATALOG_SDELKI', 'UF_CATALOG');
define('SDELKI_UTOCHNENIE', 'UF_UTOCH');
define('SDELKI_DNEY_PROSROCHKI_VYPOLNENIYA_PLANA', 'UF_DEADLINE_DAYS');
define('SDELKI_OLD_DEAL_CATALOG_WITHOUT_NAME', 'UF_OLD_DEAL_CATALOG');
define('SDELKI_PREDYDUSHCHAYA_STADIYA', 'UF_PREV_STAGE');
define('SDELKI_SELDON', 'UF_CRM_1661335633466');
define('SDELKI_KRATKOE_NAIMENOVANIE', 'UF_CRM_6308C9262B181');
define('SDELKI_RAMOCHNYY_DOGOVOR', 'UF_CRM_1662631402');
define('SDELKI_INN_ORGANIZATORA', 'UF_CRM_63318248A288E');
define('SDELKI_DATA_PERVOGO_ZAKRYTIYA', 'UF_OLD_CLOSEDATE');
define('SDELKI_WAS_CLOSED_WITHOUT_NAME', 'UF_WAS_CLOSED');
define('SDELKI_VID_DOGOVORA', 'UF_RAMDOGOVOR');

define('SDELKI_VID_DOGOVORA_RAMOCHNYY', 58); 
define('SDELKI_VID_DOGOVORA_OBYCHNYY', 59); 

define('SDELKI_PREV_STAGE_2_WITHOUT_NAME', 'UF_PREV_STAGE_2');
define('SDELKI_PREV_STAGE_2_USER_WITHOUT_NAME', 'UF_PREV_STAGE_2_USER');
define('SDELKI_KREDIT', 'UF_CREDIT');
define('SDELKI_PREV_CATEGORY_WITHOUT_NAME', 'UF_PREV_CATEGORY');
define('SDELKI_NALOG', 'UF_TAX');
define('SDELKI_OLD_NAME_WITHOUT_NAME', 'UF_OLD_NAME');
define('SDELKI_PROMEJUTOCHNAYA_PRIBYL', 'UF_INTERIM_PROFIT');
define('SDELKI_KATALOG_LIDA_', 'UF_CRM_656705754CE7D');
define('SDELKI_SVYAZANNYE_SDELKI', 'UF_RELATED_CRM');
define('SDELKI_..', 'UF_CRM_65AA1A1064823');
define('SDELKI_WHATSAPP_GROUP_ID', 'UF_CRM_A_W_GROUP_ID');
define('SDELKI_TELEGRAM_GROUP_ID', 'UF_CRM_A_T_GROUP_ID');

////////////////////////////////////////////////////////////

define('SDELKI_STATYA_RASHODA', 1); 
define('SDELKI_OBEKT_ISKLYUCHENIE', 2); 
define('SDELKI_KREDIT', 3); 
define('SDELKI_NE_UCHTENNYY_RASHOD', 4); 
define('SDELKI_NE_NASHI_OBEKTY', 5); 
define('SDELKI_TEHNOINJINIRING', 6); 
define('SDELKI_NASHI_KOMPANII_PODRYAD', 7); 
define('SDELKI_NASHI_KOMPANII_ZAEM', 8); 
define('SDELKI_OBEKT', 0); 

/////////////////////////////////////////////////////

define('AVR_OBSHCHEE_BUHGALTERIYA', 'DT144_2:NEW'); 
define('AVR_OBSHCHEE_AVR_OTPRAVLEN_ZAKAZCHIKU', 'DT144_2:PREPARATION'); 
define('AVR_OBSHCHEE_DOKUMENTY_PREDOSTAVLENY', 'DT144_2:SUCCESS'); 
define('AVR_OBSHCHEE_UDALEN', 'DT144_2:FAIL'); 
define('AVR_OBSHCHEE_DOKUMENTY_NE_PREDOSTAVLENY', 'DT144_2:1'); 
define('AVR_OBSHCHEE_DOKUMENTY_PODGOTOVLENY', 'DT144_2:UC_KQ2ARV'); 
define('AVR_OBSHCHEE_SPISAN_NA_UBYTOK', 'DT144_2:2'); 

/////////////////////////////////////////////////////

define('SCHET_NA_OPLATU_PO_SCHETU_OJIDAET_OPLATY', 'DT128_3:NEW'); 
define('SCHET_NA_OPLATU_PO_SCHETU_OPLATA_SEGODNYA', 'DT128_3:PREPARATION'); 
define('SCHET_NA_OPLATU_PO_SCHETU_OPLACHENO', 'DT128_3:CLIENT'); 
define('SCHET_NA_OPLATU_PO_SCHETU_DOKUMENTY_SOBRANY', 'DT128_3:SUCCESS'); 
define('SCHET_NA_OPLATU_PO_SCHETU_UDALEN', 'DT128_3:FAIL'); 
define('SCHET_NA_OPLATU_PO_SCHETU_SBOR_ZAKRYVAYUSHCHIH_DOKUMENTOV', 'DT128_3:1'); 
define('SCHET_NA_OPLATU_PO_SCHETU_DOKUMENTY_NE_PREDOSTAVLENY', 'DT128_3:2'); 
define('SCHET_NA_OPLATU_NALICHNYE_OJIDAET_OPLATY', 'DT128_4:NEW'); 
define('SCHET_NA_OPLATU_NALICHNYE_OPLATA_SEGODNYA', 'DT128_4:PREPARATION'); 
define('SCHET_NA_OPLATU_NALICHNYE_USPEH', 'DT128_4:SUCCESS'); 
define('SCHET_NA_OPLATU_NALICHNYE_UDALEN', 'DT128_4:FAIL'); 
define('SCHET_NA_OPLATU_PO_SCHETU_PERENESENO_IZ_BUHGALTERII', 'DT128_3:UC_VVV2KV'); 
define('SCHET_NA_OPLATU_PO_SCHETU_DOKUMENTY_NE_TREBUYUTSYA', 'DT128_3:3'); 

/////////////////////////////////////////////////////////////////////////

define('SCHETA_OBSHCHEE_PROSROCHENO', 'DT159_1:NEW'); 
define('SCHETA_OBSHCHEE_OPLATYAT_NA_ETOY_NEDELE', 'DT159_1:PREPARATION'); 
define('SCHETA_OBSHCHEE_OPLATYAT_V_TECH_14_DNEY', 'DT159_1:CLIENT'); 
define('SCHETA_OBSHCHEE_OPLACHENO', 'DT159_1:SUCCESS'); 
define('SCHETA_OBSHCHEE_UDALEN', 'DT159_1:FAIL'); 
define('SCHETA_OBSHCHEE_OPLATYAT_V_TECH_30_DNEY', 'DT159_1:UC_OZ2CEE'); 

/////////////////////////////////////////////////////////////////////////

define('IB_SHTATNOE_RASPISANIE', 70);

define('IB_SHTATNOE_RASPISANIE_OKLAD', 273);
define('IB_SHTATNOE_RASPISANIE_PODRAZDELENIE', 274);

//////////////////////////////////////////////////////////////////////////////

define('IB_BOT', 44);

define('IB_BOT_ID_USER', 226);
define('IB_BOT_ID_COMMAND', 227);
define('IB_BOT_ID_DIALOG', 228);
define('IB_BOT_PARAMS', 257);
define('IB_BOT_CRM', 259);
define('IB_BOT_MESSAGE_ID', 268);
define('IB_BOT_IMG', 483);

//////////////////////////ПИСЬМА//////////////////////////
define('IB_PISMA_TASK_ID', 364);


////////////////////Инфоблока///////////////////////////
define('IB_RN_KART_KORP', 135);
define('IB_RN_KART_KORP_DATA', 608);
define('IB_RN_KART_KORP_LITRY', 609);
define('IB_RN_KART_KORP_CENA_ZA_LITR', 610);
define('IB_RN_KART_KORP_STOIMOST', 611);
define('IB_RN_KART_KORP_TOPLIVO', 612);
define('IB_RN_KART_KORP_OPISANIE', 613);
define('IB_RN_KART_KORP_KARTA', 614);
define('IB_RN_KART_KORP_DERJATEL_KARTY', 615);
define('IB_RN_KART_KORP_CRM', 616);
define('IB_RN_KART_KORP_ZADACHA', 617);
define('IB_RN_KART_KORP_SSYLKA_NA_ZADACHU', 618);
define('IB_RN_KART_KORP_OPERATOR', 619);
define('IB_RN_KART_KORP_ID_SDELKI', 620);
define('IB_RN_KART_KORP_ZARPLATA', 621);
define('IB_RN_KART_KORP_OPISANIE', 622);
define('IB_RN_KART_KORP_KOMANDIROVKA', 623);
define('IB_RN_KART_KORP_ID_KOMANDIROVKI', 624);
define('IB_RN_KART_KORP_KOMANDIROVKA', 625);
define('IB_RN_KART_KORP_VREMYA', 626);
define('IB_RN_KART_KORP_PROBEG', 627);
define('IB_RN_KART_KORP_MARKA_AVTO', 628);
define('IB_RN_KART_KORP_AVTO', 629);
define('IB_RN_KART_KORP_PUTEVOY_LIST', 630);


//////////////////////// ФОТ /////////////////////////
define('IB_FOT', 149);

define('IB_FOT_FOT', 700);
define('IB_FOT_TASK', 701);
define('IB_FOT_WORKER', 702);
define('IB_FOT_NOTE', 703);


//////////////////////// Сокращенные рабочие дни /////////////////////////
define('SHORT_WORK_DAY_TIME', 711);
define('SHORT_WORK_DAY_DATE', 710);

?>