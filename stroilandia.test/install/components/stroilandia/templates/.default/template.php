<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */

/** @var CBitrixComponent $component */

use Bitrix\Main\Localization\Loc;

$this->setFrameMode(true);
?>
<form method="post" class="form_ip" action="">
    <div class="form-group">
        <label for="ipAdress"><?= Loc::getMessage('IP_ADRESS') ?></label>
        <input type="text" name="IP" class="form-control" id="ipAdress" placeholder="192.168.1.1">
    </div>
    <div id="ansver"></div>

    <button type="submit" class="btn btn-primary"><?= Loc::getMessage('SEND') ?></button>
</form>



