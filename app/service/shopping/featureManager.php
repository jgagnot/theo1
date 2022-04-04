<?php
/**
 * Created by PhpStorm.
 * User: jgagnot
 * Date: 04/06/2019
 * Time: 15:43
 */

namespace service\shopping;

use core\imi;

class featureManager extends imi
{
    public function getReferenceFeatureList($f3){
        $featureCategory = $this->fetchOneFieldByKeysEqual('shoppingFeature', 'name', array('referenceFeature' => true));

        foreach ($featureCategory as $key => $value){
            $featureCategory[$key]['value_array'] = $this->fetchAllByKeysEqual('shoppingFeature', array('name'=> $value['name'], 'referenceFeature' => true));
        }
        return $featureCategory;
    }

    public function getProductFeatureList($f3){
        $featureCategory = $this->fetchOneFieldByKeysEqual('shoppingFeature', 'name', array('referenceFeature' => true));

        foreach ($featureCategory as $key => $value){
            $featureCategory[$key]['value_array'] = $this->fetchAllByKeysEqual('shoppingFeature', array('name'=> $value['name'], 'referenceFeature' => true));
        }
        return $featureCategory;
    }

    public function getFeatureTree($f3, $data){
        $category_array = $this->fetchAllByKeysLike('shoppingFeature', $data);

        $result = $this->recursiveFeatureEnrichment($f3, $category_array, 0);
        if ($result == null)
            return $category_array;
        else
            return $result;
    }

    public function getFeatureChildren($f3, $featureId){
        $children_array = $this->fetchAllByKeysLike('shoppingFeature', array('parentFeatureId' => intval($featureId)));

        foreach($children_array as $key => $value){
            $value['children'] = $this->getFeatureChildren($f3, $value['id']);
            $children_array[$key] = $value;
        }
        return $children_array;
    }

    public function getFeatureParent($f3, $parentId){
        $parent_array = $this->fetchAllByKeysLike('shoppingFeature', array('id' => intval($parentId)));

        foreach($parent_array as $key => $value){
            $value['parent'] = $this->getFeatureParent($f3, $value['parentFeatureId']);
            $parent_array[$key] = $value;
        }
        return $parent_array;
    }

    public function getFeatureArrayChildren($f3, $feature_array){
        foreach ($feature_array as $index => $feature) {
                $feature_array[$index]['children'] = $this->getFeatureChildren($f3, $feature['id']);
        }
        return $feature_array;
    }

    public function deleteFeatureTree($f3, $data){
        $category_array = $this->fetchAllByKeysLike('shoppingFeature', $data);

        $this->recursiveFeatureDelete($f3, $category_array);
    }

    public function resetFeatureByArray($f3, $entity, $featureName, $new){

        $feature = $this->fetchOneCrossTableEqualKeys( 'shoppingFeature','shoppingFeaturePerEntity',  'id','featureId', array_merge($entity,array('name' => $featureName)));

        if (isset($feature)){
            if ( $new !== null)
                $this->updateByArrayById('shoppingFeature', $new, $feature['featureId']);
            else
            {
                $this->deleteById('shoppingFeaturePerEntity', $feature['id']);
                $this->deleteById('shoppingFeature', $feature['featureId']);
            }
        }else{
            $featureId = $this->insertByArray('shoppingFeature', array_merge(array('name' => $featureName), $new), []);

            $this->insertByArray('shoppingFeaturePerEntity',array_merge($entity, array('featureId'=>$featureId)), []);
        }

    }

    private function recursiveFeatureEnrichment($f3, $category_array, $featureId){
        foreach($category_array as $key => $value){
            if (intval($value['parentFeatureId']) === intval($featureId)){
                $feature = $value;
                $feature['children'] = $this->recursiveFeatureEnrichment($f3, $category_array, $value['id']);
                $ret[] = $feature;
            }
        }
        return $ret;
    }

    private function recursiveFeatureDelete($f3, $category_array){

        foreach($category_array as $key => $value){

            $children_array = $this->fetchAllByKeysLike('shoppingFeature', array('parentFeatureId' => $value['id']));

            if (count($children_array) !== 0)
                $this->recursiveFeatureDelete($f3, $children_array);

            $this->deleteById('shoppingFeature', intval($value['id']));
            $this->deleteByKeysEqual('shoppingFeaturePerEntity', array('featureId' => intval($value['id'])));
        }
    }
}