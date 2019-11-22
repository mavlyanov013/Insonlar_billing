<?php
use common\components\Config;

?>

<div class="form-group">
    <label class="control-label" for="config_<?= $item['path'] ?>"><?= $item['label'] ?></label>
    <input type="password"
           class="form-control"
           id="config_<?= $item['path'] ?>"
           name="config[<?= $item['path'] ?>]"
           placeholder="<?= $item['label'] ?>"
           value="<?= Config::getEncrypted($item['path']) ? Config::PASSWORD_FAKE_VALUE : '' ?>"
        >
    <div class="help-block"><?= (isset($item['help']) ?$item['help']: '') ?></div>
</div>
