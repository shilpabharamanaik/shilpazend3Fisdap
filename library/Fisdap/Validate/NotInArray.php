<?php

class Fisdap_Validate_NotInArray extends Zend_Validate_InArray
{
    const IN_ARRAY = 'inArray';
 
    protected $_messageTemplates = array(
        self::IN_ARRAY => "'%value%' was found in the haystack"
    );
 
    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if $value is NOT contained in the haystack option. If the strict
     * option is true, then the type of $value is also checked.
     *
     * @param  mixed $value
     * @return boolean
     */
    public function isValid($value)
    {
        $this->_setValue($value);
        if ($this->getRecursive()) {
            $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($this->_haystack));
            foreach ($iterator as $element) {
                if ($this->_strict) {
                    if ($element === $value) {
                        $this->_error(self::IN_ARRAY);
                        return false;
                    }
                } elseif ($element == $value) {
                    $this->_error(self::IN_ARRAY);
                    return false;
                }
            }
            return true;
        } else {
            if (!in_array($value, $this->_haystack, $this->_strict)) {
                return true;
            }
        }

        $this->_error(self::IN_ARRAY);
        return false;
    }
}
