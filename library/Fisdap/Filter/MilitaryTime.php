<?php

class Fisdap_Filter_MilitaryTime implements Zend\Filter\FilterInterface
{
    /**
     * Filter a time to add leading zeroes
     * @var string $value
     * @return string
     */
    public function filter($value)
    {
        // if nothing is entered, let it be null
        if ($value === null) {
            return $value;
        }
        
        //trim leading/trailing whitespace
        $value = trim($value);

        $charDiff = 4 - strlen($value);

        //Don't filter if we have more than 4 characters, the validator will catch that
        if ($charDiff < 0) {
            return $value;
        }

        //Add leading zeroes
        for ($i=0; $i < $charDiff; $i++) {
            $value = "0" . $value;
        }

        return $value;
    }
}
