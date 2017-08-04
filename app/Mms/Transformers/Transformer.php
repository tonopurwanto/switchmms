<?php

namespace App\Mms\Transformers;

abstract class Transformer
{
    /**
     * @param array $items
     * @return array
     */
    public function transformCollection(array $items)
    {
    	return array_map([$this, 'transform'], $items);
    }

    /**
     * @param $item
     * @return mixed
     */
    public abstract function transform($item);
}