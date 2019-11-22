<?php
namespace backend\models;

use common\models\product\Type;
use Yii;

/**
 * Login form
 */
class AccessResources
{

    public static function parseResources($refresh = false)
    {
        $result = Yii::$app->cache->get('__resources');
        if ($refresh || !$result) {

            $resources = array();
            foreach (glob(Yii::getAlias('@backend') . '/controllers/*Controller.php') as $file) {
                $content = file_get_contents($file);
                if (preg_match_all('/\@resource([^\\n^|]*)\|([^\\n^|]*)\|([^\\n]*)\\n/si', $content, $matches)) {
                    if (count($matches) == 4) {
                        foreach ($matches[1] as $id => $group) {
                            $group    = trim($group);
                            $resource = trim($matches[2][$id]);
                            if (!isset($resources[$group])) $resources[$group] = [];
                            if (!isset($resources[$group][$resource])) $resources[$group][$resource] = [];
                            $resources[$group][$resource][] = trim($matches[3][$id]);
                        }
                    }
                }
            }
            $result = [];

            foreach ($resources as $group => $data) {
                if (!isset($result[$group])) $result[$group] = [];
                foreach ($data as $resource => $items) {
                    $result[$group][implode(',', $items)] = $resource;
                }
            }

            Yii::$app->cache->set('__resources', $result);
        }

        return $result;
    }
}
