<?php
function smarty_function_chain1($params, $tpl) {
    \Box\Brainy\Runtime\PluginLoader::loadPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION, 'chain2', $tpl->smarty);
    return smarty_function_chain2($params, $tpl);
}
