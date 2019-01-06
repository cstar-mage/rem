<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\SearchAdapter;

use Amasty\Shopby\Model\Search\RequestGenerator as ShopbyRequestGenerator;

class Mapper
{
    /**
     *
     * @param \Magento\Elasticsearch\SearchAdapter\Mapper $subject
     * @param array $query
     * @return array
     */
    public function afterBuildQuery($subject, array $query)
    {
        $query = $this->fixAggregationSizeLimitedResponse($query);

        if (!isset($query['body']['query']['bool'])) {
            return $query;
        }

        return $this->adjustRequestQuery($query);
    }

    /**
     * By default it is not more than 10 options per filter
     *
     * @param array $query
     * @return array
     */
    private function fixAggregationSizeLimitedResponse(array $query)
    {
        if (isset($query['body']['aggregations']) && is_array($query['body']['aggregations'])) {
            foreach ($query['body']['aggregations'] as &$bucket) {
                if (isset($bucket['terms'])) {
                    $bucket['terms']['size'] = '1000';
                }
            }
        }

        return $query;
    }

    /**
     * Update a request query. In case it contains values from "MULTIPLY SELECTION" + "AND CONDITION" filter.
     *
     * @param array $query
     * @return array
     */
    private function adjustRequestQuery(array $query)
    {
        $queryBool = $query['body']['query']['bool'];
        $updatedQueryBool = $this->getQueryWithNodesInRightPlaces($queryBool);
        $query['body']['query']['bool'] = $updatedQueryBool;

        return $query;
    }

    /**
     * @param $queryBool
     * @return array
     */
    private function getQueryWithNodesInRightPlaces($queryBool)
    {
        foreach (['should', 'must'] as $part) {
            if (!isset($queryBool[$part]) || !is_array($queryBool[$part])) {
                continue;
            }

            foreach ($queryBool[$part] as $index => &$node) {
                //there could be either "terms" or "term" unify it
                if (isset($node['terms'])) {
                    $node['term'] = $node['terms'];
                }

                if (!isset($node['term']) || !is_array($node['term'])) {
                    continue;
                }

                $moved = $this->removeFakeSuffixFromNode($node);

                //restore unified "term" to "terms"
                if (isset($node['terms'])) {
                    $node['terms'] = $node['term'];
                    unset($node['term']);
                }

                if ($moved) {
                    $queryBool['must'][] = $node;
                    unset($queryBool[$part][$index]);
                }
            }
        }

        if (isset($queryBool['must'])) {
            //transform [0 => ..., 1 => ..., 3 => ...] to [0 => ..., 1 => ..., 2 => ...]
            $queryBool['must'] = array_values($queryBool['must']);
        }
        return $queryBool;
    }

    /**
     * Replace key 'attribute_FAKE_SUFFIX_1' or 'attribute_FAKE_SUFFIX_1_raw' with 'attribute'.
     *
     * @param array $node
     * @return int
     */
    private function removeFakeSuffixFromNode(array &$node)
    {
        $moved = 0;
        $template = '#'. ShopbyRequestGenerator::FAKE_SUFFIX . '[0-9]+#';
        foreach ($node['term'] as $code => $value) {
            $correctedAttrCode = preg_replace($template, '', $code);
            if ($code !== $correctedAttrCode) {
                $node['term'][$correctedAttrCode] = $value;
                unset($node['term'][$code]);
                $moved++;
            }
        }

        return $moved;
    }
}
