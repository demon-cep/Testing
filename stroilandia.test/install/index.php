<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class stroilandia_test extends CModule
{
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->MODULE_ID = 'stroilandia.test';
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("STROILANDIA_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("STROILANDIA_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("STROILANDIA_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("STROILANDIA_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
        $this->MODULE_GROUP_RIGHTS = "Y";

        CModule::IncludeModule("highloadblock");
    }

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }
    function createHB()
    {
        $highBlockName = "GeoIP";
        $tableName = "stroilandia_hb_geo_ip";
        $data = array(
            'NAME' => $highBlockName,
            'TABLE_NAME' => $tableName,
        );
        $result = Bitrix\Highloadblock\HighloadBlockTable::add($data);
        $highBlockID = $result->getId();

        $arFieldsName = array(
            'UF_IP' => array("Y", "string"),

            'UF_COUNTRY_ID' => array("Y", "integer"),
            'UF_COUNTRY_NAME' => array("Y", "string"),

            'UF_REGION_ID' => array("Y", "integer"),
            'UF_REGION_NAME' => array("Y", "string"),

            'UF_CITY_ID' => array("Y", "integer"),
            'UF_CITY_NAME' => array("Y", "string"),

            'UF_TIMESTAMP' => array("Y", "integer"),
        );
        $obUserField = new CUserTypeEntity();
        $sort = 100;
        foreach ($arFieldsName as $fieldName => $fieldValue) {
            $arUserField = array(
                "ENTITY_ID" => "HLBLOCK_" . $highBlockID,
                "FIELD_NAME" => $fieldName,
                "USER_TYPE_ID" => $fieldValue[1],
                "XML_ID" => "",
                "SORT" => $sort,
                "MULTIPLE" => "N",
                "MANDATORY" => $fieldValue[0],
                "SHOW_FILTER" => "N",
                "SHOW_IN_LIST" => "Y",
                "EDIT_IN_LIST" => "Y",
                "IS_SEARCHABLE" => "N",
                "SETTINGS" => array(),
            );
            $obUserField->Add($arUserField);
        }
    }

    function InstallDB()
    {
        $this->createHB();

    }

    function UnInstallDB()
    {
        global $DB;

        $result = \Bitrix\Highloadblock\HighloadBlockTable::getList([
            "filter"=>["=NAME"=>"GeoIP"],
            "select"=>["ID"],
        ]);
        if ($hldata = $result->fetch())
        {
            \Bitrix\Highloadblock\HighloadBlockTable::delete($hldata['ID']);
        }

    }

    function DoInstall()
    {
        global $APPLICATION;

        if ($this->isVersionD7()) {
            $this->InstallDB();
            CopyDirFiles($this->GetPath()."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        } else {
            $APPLICATION->ThrowException(Loc::getMessage("STROILANDIA_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("STROILANDIA_INSTALL_TITLE"), $this->GetPath() . "/install/step.php");
    }

    function DoUninstall()
    {
        global $APPLICATION;
        $context = Application::getInstance()->getContext();
        $request = $context->getRequest();

        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/local/components/stroilandia");
        if ($request["step"] < 2) {
            $APPLICATION->IncludeAdminFile(Loc::getMessage("STROILANDIA_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep1.php");
        } elseif ($request["step"] == 2) {

            if ($request['savedata'] != "Y") {
                $this->UnInstallDB();
            }

            \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage("STROILANDIA_UNINSTALL_TITLE"), $this->GetPath() . "/install/unstep2.php");
        }
    }

    public function GetPath($notDocumentRoot = false)
    {
        if ($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(), '', dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

}