<?php

namespace Bsitc\Brightpearl\Model;

class Queuestatus
{
    /**#@+
     * Status values
     */
    const STATUS_PENDING = 1;
    const STATUS_PROCESSING = 2;
    const STATUS_COMPLETED = 3;
    const STATUS_ERROR = 4;
    const STATUS_NOTPAID = 5;
    const STATUS_FAILED = 6;
    
    const STATUS_GO_RELEASE = 7;
    const STATUS_GO_PROCESS = 8;
    const STATUS_IT_RECEIVED = 9;
    const STATUS_GO_ERROR = 10;
    
    const STATUS_SETTLED = 20;
    const STATUS_IGNORE = 30;
	
    const STATUS_INVOICE_AUTHORIZED  = 99;
    
    
    

    

    /**
     * Retrieve option array
     *
     * @return string[]
     */
    public function getOptionArray()
    {
        return [
            self::STATUS_PENDING => __('Pending'),
            self::STATUS_PROCESSING => __('Processing'),
            self::STATUS_COMPLETED => __('Completed'),
            self::STATUS_ERROR => __('Error'),
            self::STATUS_NOTPAID => __('Not_Paid'),
            self::STATUS_FAILED => __('Failed'),
            self::STATUS_GO_RELEASE => __('GO_Release') ,
            self::STATUS_GO_PROCESS => __('GO_Processing') ,
            self::STATUS_IT_RECEIVED => __('IT_Received') ,
            self::STATUS_GO_ERROR => __('GO_Error'),
            self::STATUS_SETTLED => __('Manually Settled'),
            self::STATUS_IGNORE => __('Ignore'),
            self::STATUS_INVOICE_AUTHORIZED => __('Invoice Authorized Only')
        ];
    }

    public function getQueueOptionArray()
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
