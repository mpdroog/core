<?php

class ArrayUtils
{
    // Wrapper around implode with some input validation
    // @return bool|string
    public static function implode($glue, array $pieces)
    {
        foreach ($pieces as $piece) {
            if (1 !== preg_match("/^[a-z0-9_]{2,}$/i", $piece)) {
                user_error("ArrayUtils::implode received invalid piece=$piece");
            }
        }
        return implode($glue, $pieces);
    }
}
