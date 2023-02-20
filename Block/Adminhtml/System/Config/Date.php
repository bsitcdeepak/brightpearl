<?php

namespace Bsitc\Brightpearl\Block\Adminhtml\System\Config;
 
class Date extends \Magento\Config\Block\System\Config\Form\Field
{
    
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $element->setDateFormat(\Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat(null);
        return parent::render($element);
         
        /*
        //----------  If you want to add a time picker
                $element->setDateFormat(DateTime::DATE_INTERNAL_FORMAT);
                $element->setTimeFormat('HH:mm:ss');
                $element->setShowsTime(true);
                return parent::render($element);
        */
    }
}
