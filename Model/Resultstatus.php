<?php

namespace Bsitc\Brightpearl\Model;

class Resultstatus
{
    /**#@+
     * Status values
     */
    const STATUS_FAILED = 0;

    const STATUS_SUCCESS = 1;
    
    
    const STATUS_SETTLED = 2;
    
    const STATUS_IGNORE = 3;
    

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [self::STATUS_FAILED => __('Failed'), self::STATUS_SUCCESS => __('Success'), self::STATUS_SETTLED => __('Manually Settled'), self::STATUS_IGNORE => __('Ignore')];
    }
    
    public function getResultOptionArray()
    {
        $data = [];
        foreach (self::getOptionArray() as $index => $value) {
             $data[$value->getText()] =  $index ;
        }
        return $data;
    }

    /**
     * Retrieve option array with empty value
     *
     * @return string[]
     */
    public function getAllOptions()
    {
        $result = [];

        foreach (self::getOptionArray() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }

        return $result;
    }

    /**
     * Retrieve option text by option value
     *
     * @param string $optionId
     * @return string
     */
    public function getOptionText($optionId)
    {
        $options = self::getOptionArray();

        return isset($options[$optionId]) ? $options[$optionId] : null;
    }
}
