<?
    if ($success = $flash['success']) {
        echo MessageBox::success($success);
    }
    if ($error = $flash['error']) {
        echo MessageBox::error($error);
    }
    if ($flash['question']) {
        echo $flash['question'];
    }


    $infobox_content = array(array(
        'kategorie' => _('Hinweise:'),
        'eintrag'   => array(array(
            'icon' => 'icons/16/black/info.png',
            'text' => _("Hier k�nnen die Anbindung zum Opencast Matterhorn System verwaltet werden. Geben Sie die jeweiligen URLs zu den REST-Sevices, sowie die dementsprechenen Zugangsdaten an.")
        ))
    ));
    $infobox = array('picture' => 'infobox/administration.jpg', 'content' => $infobox_content);
?>

<h3>Globale Opencast Matterhorn Einstellungen</h3>
<span>
  <?=_("Tragen Sie hier die jeweilgen Pfade zu den Matterhorn REST-Endpoints ein.")?>
</span>
<form style="padding-top:25px;" action="<?= PluginEngine::getLink('opencast/admin/update/') ?>" method=post>
    <?= CSRFProtection::tokenTag() ?>
    <fieldset>
        <legend><?=_("Series Service")?></legend>
        <label class="form_label" for="series_url"><?=_("Service-URL")?>:</label>
        <input id="group_name" type="text" name="series_url" value="<?=$series_url?>" size="50">
        <label class="form_label" for="series_user"><?=_("Nutzerkennung")?>:</label>
        <input id="group_name" type="text" name="series_user" value="<?=$series_user?>" size="50">
        <label class="form_label" for="series_password"><?=_("Passwort")?>:</label>
        <input id="group_name" type="password" name="series_password" value="<?=$series_password?>" size="50">
    </fieldset>
    <fieldset>
        <legend><?=_("Search Service")?></legend>
        <label class="form_label" for="search_url"><?=_("Service-URL")?>:</label>
        <input id="group_name" type="text" name="search_url" value="<?=$search_url?>" size="50">
        <label class="form_label" for="search_user"><?=_("Nutzerkennung")?>:</label>
        <input id="group_name" type="text" name="search_user" value="<?=$search_user?>" size="50">
        <label class="form_label" for="search_password"><?=_("Passwort")?>:</label>
        <input id="group_name" type="password" name="search_password" value="<?=$search_password?>" size="50">
    </fieldset>
    <fieldset>
        <legend><?=_("Scheduling Service")?></legend>
        <label class="form_label" for="scheduling_url"><?=_("Service-URL")?>:</label>
        <input id="group_name" type="text" name="scheduling_url" value="<?=$scheduling_url?>" size="50">
        <label class="form_label" for="scheduling_user"><?=_("Nutzerkennung")?>:</label>
        <input id="group_name" type="text" name="scheduling_user" value="<?=$scheduling_user?>" size="50">
        <label class="form_label" for="scheduling_password"><?=_("Passwort")?>:</label>
        <input id="group_name" type="password" name="scheduling_password" value="<?=$scheduling_password?>" size="50">
    </fieldset>
    <fieldset>
        <legend><?=_("Capture Admin Service")?></legend>
        <label class="form_label" for="captureadmin_url"><?=_("Service-URL")?>:</label>
        <input id="group_name" type="text" name="captureadmin_url" value="<?=$captureadmin_url?>" size="50">
        <label class="form_label" for="captureadmin_user"><?=_("Nutzerkennung")?>:</label>
        <input id="group_name" type="text" name="captureadmin_user" value="<?=$captureadmin_user?>" size="50">
        <label class="form_label" for="captureadmin_password"><?=_("Passwort")?>:</label>
        <input id="group_name" type="password" name="captureadmin_password" value="<?=$captureadmin_password?>" size="50">
    </fieldset>
    <fieldset>
        <legend><?=_('Raumzuordnung')?></legend>
        <div style="dislay:inline;vertical-align:middle">
            <div style="float:left;width:50%;">
                <p><?=_("Resourcen")?></p>
                <?=var_dump($resources)?>
                <ul>
                <? foreach($resources as $resource) : ?>
                    <li><?=$resource['name'] ?></li>
                <? endforeach; ?>
                </ul>



            </div>
            <div style="float:right;width:50%;">
                <p> <?=_("Capture Agents")?></p>
                test

            </div>
        </div>
    </fieldset>

     <div class="form_submit">
    <?= makebutton("uebernehmen","input") ?>
    <a href="<?=PluginEngine::getLink('opencast/admin/config/')?>"><?= makebutton("abbrechen")?></a>
    </div>
</form>   






<?php
