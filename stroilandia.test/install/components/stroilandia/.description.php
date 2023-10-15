<?php

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$arComponentDescription = [
    "NAME" => "stroilandia geoIP",
    "DESCRIPTION" => "stroilandia geoIP",
    "ICON" => "",
    "CACHE_PATH" => "Y",
    "SORT" => 10,
    "PATH" => [
        "ID" => "stroilandia_geoIP",
        "CHILD" => [
            "ID" => "stroilandia_component",
            "NAME" => "stroilandia geoIP"
        ]
    ]
];