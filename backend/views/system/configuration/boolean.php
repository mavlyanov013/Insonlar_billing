<?php
use common\components\Config;

?>
<div class="form-group">
    <div class="togglebutton checkbo">
        <label class="cb-checkbox">
            <input type="checkbox"
                   id="config_<?= $item['path'] ?>"
                   name="config[<?= $item['path'] ?>]"
                <?= Config::get($item['path']) ? "checked='checked'" : '' ?>
                   value="1">
            <?= $item['label'] ?>

        </label>
    </div>
</div>