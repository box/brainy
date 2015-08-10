<?php

/**
 * Pass-through plugin to mark content as already sanitized.
 * @author Matt Basta
 * @param array $params parameters
 * @return string with compiled code
 */
function smarty_modifiercompiler_unsafe_noescape($params, $compiler)
{
    return $params[0];
}
