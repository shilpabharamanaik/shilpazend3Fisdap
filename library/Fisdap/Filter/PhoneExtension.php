<?php
class Fisdap_Filter_PhoneExtension implements Zend\Filter\FilterInterface
{
    public function filter($value)
    {
        //Trim any whitespace from the beginning/end of phone number
        $value = trim($value);
        
        //Split the phone number into two pieces, everything before the extension, and everything after
        $values = preg_split("/ext/", $value);
        
        //if the extension is empty, just return the first part
        if (empty($values[1])) {
            return trim($values[0]);
        }
        
        //Otherwise, return the whole thing
        return $value;
    }
}
