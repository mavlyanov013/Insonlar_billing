<?php

/* @var $model common\models\Category */

use common\models\Category;
use yii\helpers\Url;

$attributes = [];
$this->registerJs('sortAttributes();');

$expanded = isset($_COOKIE['category_expand']) ? explode(',', $_COOKIE['category_expand']) : [];
$expanded = array_flip($expanded);

function renderTree($categories, $model, $expanded)
{
    ?>
    <?php foreach ($categories as $category): ?>
    <li
            class="<?= isset($expanded[$category->id]) ? 'dd-collapsed' : '' ?> dd-item dd3-item <?= $category->id == $model->id ? 'dd-active' : '' ?>"
            data-id="<?= $category->id ?>"">
    <div class="dd-handle dd3-handle">
        &nbsp;
    </div>
    <div class="dd3-content">
        <a href="<?= Url::to(['category/index', 'id' => $category->id]) ?>"><?= $category->name ?></a>
    </div>
    <?php if (count($category->child)): ?>
        <ol class="dd-list">
            <?php renderTree($category->child, $model, $expanded) ?>
        </ol>
    <?php endif; ?>
    </li>
<?php endforeach; ?>

<?php } ?>

<div class="panel">
    <div class="panel-heading border">
        <h4 class="pull-left"><?= __('Categories') ?></h4>

        <div class="pull-right">

            <?php if ($model->id): ?>
                <!--<button style="margin-left: 10px" onclick="addProductShow()"
                        class="btn btn-success pull-right">
                    <i class="fa fa-plus"></i> <? /*= __('Product') */ ?>
                </button>-->
                <a style="margin-left: 10px" href="<?= Url::to(['category/index', 'parent' => $model->id]) ?>"
                   class="btn btn-success pull-right">
                    <i class="fa fa-plus"></i> <?= __('Child') ?>
                </a>
                <a style="margin-left: 10px" href="<?= Url::to(['category/index', 'parent' => $model->parent]) ?>"
                   class="btn btn-success pull-right">
                    <i class="fa fa-plus"></i> <?= __('Brother') ?>
                </a>

            <?php endif; ?>

            <a href="<?= Url::to(['category/index']) ?>" class="btn btn-primary pull-right">
                <i class="fa fa-plus"></i> <?= __('Root') ?>
            </a>
        </div>
        <div class="clearfix"></div>
    </div>
    <div class="panel-body">
        <div class="row">
            <div class="col col-md-12">
                <div class="cf nestable-lists-attribute dd " id="categories">
                    <ol class='dd-list' id='nestable-lists'>
                        <?php renderTree(Category::getCategoryTree(), $model, $expanded); ?>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="application/javascript">
    function sortAttributes() {
        $('#categories').nestable({
            maxDepth: 20
        }).on('change', function () {
            var items = $('#categories').nestable('serialize');
            var data = {};
            data.data = JSON.stringify(items);
            data._csrf = $('input[name="_csrf"]').val();
            $.post('<?=Url::to(['category/index', 'id' => $model->id, 'sort' => 1])?>', data, function () {

            })
        });

        <?php if(!Yii::$app->request->isAjax):?>
        $(document).on('click', '.dd-item button', function (element) {
            var expanded = Cookies.get('category_expand') != undefined ? Cookies.get('category_expand').split(',') : [];
            var id = $(element.target).parent().data('id');

            if ($(element.target).data('action') == 'expand') {
                if (expanded.indexOf(id) > -1) {
                    expanded.splice(expanded.indexOf(id), 1);
                }
            } else {
                expanded.push(id);
            }
            console.log(expanded);
            Cookies.set('category_expand', expanded.join(','));
        });
        <?php endif;?>

    }

</script>