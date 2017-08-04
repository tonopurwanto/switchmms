<?php

namespace App\Mms\Operators;

class Telkomsel implements OperatorsContract
{
    /**
     * @var string
     */
    private $name = 'Telkomsel';

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function prefix()
    {
        return [
            '0811', '0812', '0813', '0821', '0822', '0823', '0851', '0852', '0853'
        ];
    }
}