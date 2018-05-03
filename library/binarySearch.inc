<?php
/**
 * Perform a binary search on a sorted list.
 * The items in the list should be the same type as the search item.
 * @param mixed $needle The item to search for.
 * @param array $haystack The list to search.
 * @return integer The index in the list of the item, or less than 0 if not
 * found.
*/
function BinarySearch($needle, $haystack)
{
    $high = count($haystack)-1;
    $low = 0;
   
    while ($low <= $high) {
        // Integer division.
        $n = $high + $low;
        $i = ($n - ($n % 2)) / 2;

        if ($haystack[$i] < $needle) {
            $low = $i + 1;
        }
        elseif ($haystack[$i] > $needle) {
            $high = $i - 1;
        }
        else {
            return $i;
        }
    }

    return -1;
}
?>
