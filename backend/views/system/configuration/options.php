<label for="config_<?= $item['path'] ?>" class="col-sm-4 control-label">
    <?= $item['label'] ?>
</label>

<div class="col-sm-8">
    <select class="form-control selectpicker show-tick"
            name="config[<?= $item['path'] ?>]"
            id="config_<?= $item['path'] ?>"
        >
        <?php foreach ($item['options'] as $value => $label): ?>
            <option value="<?= $value ?>"
                <?= ($value == Config::get($item['path'])) ? 'selected="selected"' : '' ?>
                >
                <?= $label ?>
            </option>
        <?php endforeach; ?>
    </select>
    <div class="help-block"><?= (isset($item['help']) ?$item['help']: '') ?></div>
</div>