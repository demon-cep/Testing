<?

use Bitrix\Main;
use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Localization\Loc;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loc::loadMessages(__FILE__);

class Stroilandia extends \CBitrixComponent
{
    private string $ip;
    public function onPrepareComponentParams($arParams)
    {
        $arParams = parent::onPrepareComponentParams($arParams);
        return $arParams;
    }

    /**
     * Возврашает HB
     * @param string $HB_NAME
     * @return DataManager|void
     * @throws Main\ArgumentException
     * @throws Main\SystemException
     */
    private function getHB(string $HB_NAME)
    {
        if(empty($HB_NAME)){
            throw new Exception(Loc::getMessage('ERROR_HB'));
        }
        $result = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            "filter" => ["=NAME" => "GeoIP"],
            "select" => ["NAME", "ID"],
        ]);
        if ($hldata = $result->fetch()) {

            $hlbl = $hldata['ID']; // Указываем ID нашего highloadblock блока к которому будет делать запросы.
            $hlblock = \Bitrix\Highloadblock\HighloadBlockTable::getById($hlbl)->fetch();

            $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            return $entity_data_class;
        }
    }
    private function HBData()
    {
        if(empty($this->ip)){
            throw new \Exception(Loc::getMessage('ERROR_IP'));
        }
        $entity_data_class= $this->getHB($this->arParams['HB_NAME']);
        $data = $entity_data_class::getList(array(
            "select" => array("*"),
            "order" => array("ID" => "DESC"),
            "filter" => array("=UF_IP" => $this->ip) //Фильтрация выборки
        ));

        if ($arData = $data->Fetch()) {
               $this->arResult["GEO"]=$arData;
        }else{
            $this->geo();
        }
    }

    /**
     * Добовление записи в HB
     * @param array $arr [
     "UF_IP" => $geo['ip'],

    "UF_COUNTRY_ID" => $geo['country']['id'],
    "UF_COUNTRY_NAME" => $geo['country']['name_ru'],

    "UF_REGION_ID" => $geo['region']['id'],
    "UF_REGION_NAME" => $geo['region']['name_ru'],

    "UF_CITY_ID" => $geo['city']['id'],
    "UF_CITY_NAME" => $geo['city']['name_ru'],

    "UF_TIMESTAMP" => $geo['timestamp'],]
     * @return void
     * @throws Main\ArgumentException
     * @throws Main\SystemException
     */
    private function addHB(array $arr){
        $entity_data_class= $this->getHB($this->arParams['HB_NAME']);
        $result = $entity_data_class::add($arr);
        if (!$result->isSuccess())
        {
            throw new Exception(Loc::getMessage('ERROR_ADD_HB').$this->arParams['HB_NAME'].Loc::getMessage('ERROR_TEXT').implode('<br>',$result->getErrorMessages()));
        }
    }
    private function geo(){
        if(empty($this->ip)){
            throw new \Exception(Loc::getMessage('ERROR_IP'));
        }
        $geo = json_decode(file_get_contents('http://api.sypexgeo.net/json/' . $this->ip), true);
        $this->arResult["TEST"]=$geo;
        if(empty($geo['city']['id'])){
            throw new Exception("http://api.sypexgeo.net/json/" . $this->ip.Loc::getMessage('ERROR_EMPTY_ARRAY'));
        }
        $arr = [
            "UF_IP" => $geo['ip'],

            "UF_COUNTRY_ID" => $geo['country']['id'],
            "UF_COUNTRY_NAME" => $geo['country']['name_ru'],

            "UF_REGION_ID" => $geo['region']['id'],
            "UF_REGION_NAME" => $geo['region']['name_ru'],

            "UF_CITY_ID" => $geo['city']['id'],
            "UF_CITY_NAME" => $geo['city']['name_ru'],

            "UF_TIMESTAMP" => $geo['timestamp'],
        ];

        $this->addHB($arr);
        $this->arResult["GEO"]=$arr;
    }

    private function getGeo(string $ip = "")
    {
        if (empty($ip)) {
            throw new \Exception(Loc::getMessage('ERROR_IP'));
        }
        $this->ip=$ip;
        $this->HBData();

    }

    private function errorGeo(Throwable $e)
    {
        AddEventHandler('main', 'OnEventLogGetAuditTypes', 'ASD_OnEventLogGetAuditTypes');
        function ASD_OnEventLogGetAuditTypes()
        {
            return array('GEO_ERROR_TYPE' => Loc::getMessage('ERROR_DATA'));
        }

        CEventLog::Add(array(
            "SEVERITY" => "SECURITY",
            "AUDIT_TYPE_ID" => "GEO_ERROR_TYPE",
            "MODULE_ID" => "stroilandia.test",
            "DESCRIPTION" => Loc::getMessage('ERROR') . $e->getMessage(),
        ));
    }

    /**
     * Отоброжение JSON
     * @return void
     */
    private function returnJson()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
        header('Access-Control-Allow-Headers', 'Content-Type');
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($this->arResult);
        exit();
    }

    public function executeComponent()
    {
        CModule::IncludeModule("highloadblock");
//        проверяем от кого пришол запрос если ajax отдаем json
        if ($_GET["AJAX_REQUEST"] == "Y") {

            try {
                $this->getGeo($_POST['IP']);

            } catch (Throwable $e) {
                $this->errorGeo($e);
                $this->arResult["ERROR"]="Y";
                $this->arResult["ERROR_TEXT"]=$e->getMessage();
            }
            $this->returnJson();
        } else {

            \Bitrix\Main\UI\Extension::load("ui.bootstrap4");
            $this->showTemplate();
        }

    }

    public function showTemplate()
    {
        $this->includeComponentTemplate();
    }


}