<?php
function smarty_function_chain2($params,$tpl) {
    \Box\Brainy\Runtime\PluginLoader::loadPlugin(\Box\Brainy\Brainy::PLUGIN_FUNCTION, 'chain3', $tpl->smarty);
    return smarty_function_chain3($params,$tpl);
}
