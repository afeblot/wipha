<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.is_in_array.php
 * Type:     modifier
 * Name:     is_in_array
 * Params: $string: the string to modify
 *         $array: the array to look for $string in
 *         $yes: the value to  return if $string is in $array
 *         $no: the value to  return if $string is not in $array
 *
 * Purpose:  Returns $yes or $no according to wether $string
 *           is in $array or not.
 * -------------------------------------------------------------
 */
function smarty_modifier_is_in_array($string, $array, $yes, $no="")
{
    return (in_array($string, $array)) ? $yes : $no;
}
?> 
