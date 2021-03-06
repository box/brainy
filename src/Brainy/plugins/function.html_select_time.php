<?php
/**
 * Smarty plugin
 *
 * @package    Brainy
 * @subpackage PluginsFunction
 */

/**
 * @ignore
 */
require_once BRAINY_PLUGINS_DIR . 'shared.escape_special_chars.php';
/**
 * @ignore
 */
require_once BRAINY_PLUGINS_DIR . 'shared.make_timestamp.php';

/**
 * Smarty {html_select_time} function plugin
 *
 * Type:     function<br>
 * Name:     html_select_time<br>
 * Purpose:  Prints the dropdowns for time selection
 *
 * @link   http://www.smarty.net/manual/en/language.function.html.select.time.php {html_select_time}
 *          (Smarty online manual)
 * @author Roberto Berto <roberto@berto.net>
 * @author Monte Ohrt <monte AT ohrt DOT com>
 * @param  array    $params   parameters
 * @param  Template $template template object
 * @return string
 * @uses   smarty_make_timestamp()
 */
function smarty_function_html_select_time($params, $template)
{

    $template->assertIsNotStrict('`{html_select_time}` is a deprecated plugin and is not allowed in strict mode');

    $options = array(
        'prefix' => "Time_",
        'field_array' => null,
        'field_separator' => "\n",
        'option_separator' => "\n",
        'time' => null,

        'display_hours' => true,
        'display_minutes' => true,
        'display_seconds' => true,
        'display_meridian' => true,

        'hour_format' => '%02d',
        'hour_value_format' => '%02d',
        'minute_format' => '%02d',
        'minute_value_format' => '%02d',
        'second_format' => '%02d',
        'second_value_format' => '%02d',

        'hour_size' => null,
        'minute_size' => null,
        'second_size' => null,
        'meridian_size' => null,

        'all_empty' => null,
        'hour_empty' => null,
        'minute_empty' => null,
        'second_empty' => null,
        'meridian_empty' => null,

        'all_id' => null,
        'hour_id' => null,
        'minute_id' => null,
        'second_id' => null,
        'meridian_id' => null,

        'use_24_hours' => true,
        'minute_interval' => 1,
        'second_interval' => 1,

        'extra_attrs' => '',
        'all_extra' => null,
        'hour_extra' => null,
        'minute_extra' => null,
        'second_extra' => null,
        'meridian_extra' => null,
    );

    foreach ($params as $_key => $_value) {
        switch ($_key) {
            case 'time':
                if (!is_array($_value) && $_value !== null) {
                    $options['time'] = smarty_make_timestamp($_value);
                }
                break;

            case 'prefix':
            case 'field_array':
            case 'field_separator':
            case 'option_separator':
            case 'all_extra':
            case 'hour_extra':
            case 'minute_extra':
            case 'second_extra':
            case 'meridian_extra':
            case 'all_empty':
            case 'hour_empty':
            case 'minute_empty':
            case 'second_empty':
            case 'meridian_empty':
            case 'all_id':
            case 'hour_id':
            case 'minute_id':
            case 'second_id':
            case 'meridian_id':
            case 'hour_format':
            case 'hour_value_format':
            case 'minute_format':
            case 'minute_value_format':
            case 'second_format':
            case 'second_value_format':
                $options[$_key] = (string) $_value;
                break;

            case 'display_hours':
            case 'display_minutes':
            case 'display_seconds':
            case 'display_meridian':
            case 'use_24_hours':
                $options[$_key] = (bool) $_value;
                break;

            case 'minute_interval':
            case 'second_interval':
            case 'hour_size':
            case 'minute_size':
            case 'second_size':
            case 'meridian_size':
                $options[$_key] = (int) $_value;
                break;

            default:
                if (!is_array($_value)) {
                    $options['extra_attrs'] .= ' ' . $_key . '="' . smarty_function_escape_special_chars($_value) . '"';
                } else {
                    trigger_error("html_select_date: extra attribute '$_key' cannot be an array", E_USER_NOTICE);
                }
                break;
        }
    }

    $timeData = array(
        '_hour' => null,
        '_minute' => null,
        '_second' => null,
    );

    if (isset($params['time']) && is_array($params['time'])) {
        if (isset($params['time'][$options['prefix'] . 'Hour'])) {
            // $_REQUEST[$options['field_array']] given
            foreach (array('H' => 'Hour',  'i' => 'Minute', 's' => 'Second') as $_elementKey => $_elementName) {
                $_variableName = '_' . strtolower($_elementName);
                $timeData[$_variableName] = isset($params['time'][$options['prefix'] . $_elementName])
                    ? $params['time'][$options['prefix'] . $_elementName]
                    : date($_elementKey);
            }
            $_meridian = isset($params['time'][$options['prefix'] . 'Meridian'])
                ? (' ' . $params['time'][$options['prefix'] . 'Meridian'])
                : '';
            $options['time'] = strtotime($timeData['_hour'] . ':' . $timeData['_minute'] . ':' . $timeData['_second'] . $_meridian);
            list($timeData['_hour'], $timeData['_minute'], $timeData['_second']) = $options['time'] = explode('-', date('H-i-s', $options['time']));
        } elseif (isset($params['time'][$options['field_array']][$options['prefix'] . 'Hour'])) {
            // $_REQUEST given
            foreach (array('H' => 'Hour',  'i' => 'Minute', 's' => 'Second') as $_elementKey => $_elementName) {
                $_variableName = '_' . strtolower($_elementName);
                $timeData[$_variableName] = isset($params['time'][$options['field_array']][$options['prefix'] . $_elementName])
                    ? $params['time'][$options['field_array']][$options['prefix'] . $_elementName]
                    : date($_elementKey);
            }
            $_meridian = isset($params['time'][$options['field_array']][$options['prefix'] . 'Meridian'])
                ? (' ' . $params['time'][$options['field_array']][$options['prefix'] . 'Meridian'])
                : '';
            $options['time'] = strtotime($timeData['_hour'] . ':' . $timeData['_minute'] . ':' . $timeData['_second'] . $_meridian);
            list($timeData['_hour'], $timeData['_minute'], $timeData['_second']) = $options['time'] = explode('-', date('H-i-s', $options['time']));
        } else {
            // no date found, use NOW
            list($_year, $_month, $_day) = $options['time'] = explode('-', date('Y-m-d'));
        }
    } elseif ($options['time'] === null) {
        if (array_key_exists('time', $params)) {
            $timeData['_hour'] = $timeData['_minute'] = $timeData['_second'] = $options['time'] = null;
        } else {
            list($timeData['_hour'], $timeData['_minute'], $timeData['_second']) = $options['time'] = explode('-', date('H-i-s'));
        }
    } else {
        list($timeData['_hour'], $timeData['_minute'], $timeData['_second']) = $options['time'] = explode('-', date('H-i-s', $options['time']));
    }

    // generate hour <select>
    $_html_hours = null;
    if ($options['display_hours']) {
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Hour]') : ($options['prefix'] . 'Hour');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['hour_extra']) {
            $_extra .= ' ' . $options['hour_extra'];
        }

        $_html_hours = '<select name="' . $_name . '"';
        if ($options['hour_id'] !== null || $options['all_id'] !== null) {
            $_html_hours .= ' id="' . smarty_function_escape_special_chars(
                $options['hour_id'] !== null ? ( $options['hour_id'] ? $options['hour_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['hour_size']) {
            $_html_hours .= ' size="' . $options['hour_size'] . '"';
        }
        $_html_hours .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['hour_empty']) || isset($options['all_empty'])) {
            $_html_hours .= '<option value="">' . ( isset($options['hour_empty']) ? $options['hour_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        $start = $options['use_24_hours'] ? 0 : 1;
        $end = $options['use_24_hours'] ? 23 : 12;
        for ($i=$start; $i <= $end; $i++) {
            $_val = sprintf('%02d', $i);
            $_text = $options['hour_format'] == '%02d' ? $_val : sprintf($options['hour_format'], $i);
            $_value = $options['hour_value_format'] == '%02d' ? $_val : sprintf($options['hour_value_format'], $i);

            if (!$options['use_24_hours']) {
                $_hour12 = $timeData['_hour'] == 0
                    ? 12
                    : ($timeData['_hour'] <= 12 ? $timeData['_hour'] : $timeData['_hour'] -12);
            }

            $selected = $timeData['_hour'] !== null ? ($options['use_24_hours'] ? $timeData['_hour'] == $_val : $_hour12 == $_val) : null;
            $_html_hours .= '<option value="' . $_value . '"'
                . ($selected ? ' selected="selected"' : '')
                . '>' . $_text . '</option>' . $options['option_separator'];
        }

        $_html_hours .= '</select>';
    }

    // generate minute <select>
    $_html_minutes = null;
    if ($options['display_minutes']) {
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Minute]') : ($options['prefix'] . 'Minute');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['minute_extra']) {
            $_extra .= ' ' . $options['minute_extra'];
        }

        $_html_minutes = '<select name="' . $_name . '"';
        if ($options['minute_id'] !== null || $options['all_id'] !== null) {
            $_html_minutes .= ' id="' . smarty_function_escape_special_chars(
                $options['minute_id'] !== null ? ( $options['minute_id'] ? $options['minute_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['minute_size']) {
            $_html_minutes .= ' size="' . $options['minute_size'] . '"';
        }
        $_html_minutes .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['minute_empty']) || isset($options['all_empty'])) {
            $_html_minutes .= '<option value="">' . ( isset($options['minute_empty']) ? $options['minute_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        $selected = $timeData['_minute'] !== null ? ($timeData['_minute'] - $timeData['_minute'] % $options['minute_interval']) : null;
        for ($i=0; $i <= 59; $i += $options['minute_interval']) {
            $_val = sprintf('%02d', $i);
            $_text = $options['minute_format'] == '%02d' ? $_val : sprintf($options['minute_format'], $i);
            $_value = $options['minute_value_format'] == '%02d' ? $_val : sprintf($options['minute_value_format'], $i);
            $_html_minutes .= '<option value="' . $_value . '"'
                . ($selected === $i ? ' selected="selected"' : '')
                . '>' . $_text . '</option>' . $options['option_separator'];
        }

        $_html_minutes .= '</select>';
    }

    // generate second <select>
    $_html_seconds = null;
    if ($options['display_seconds']) {
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Second]') : ($options['prefix'] . 'Second');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['second_extra']) {
            $_extra .= ' ' . $options['second_extra'];
        }

        $_html_seconds = '<select name="' . $_name . '"';
        if ($options['second_id'] !== null || $options['all_id'] !== null) {
            $_html_seconds .= ' id="' . smarty_function_escape_special_chars(
                $options['second_id'] !== null ? ( $options['second_id'] ? $options['second_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['second_size']) {
            $_html_seconds .= ' size="' . $options['second_size'] . '"';
        }
        $_html_seconds .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['second_empty']) || isset($options['all_empty'])) {
            $_html_seconds .= '<option value="">' . ( isset($options['second_empty']) ? $options['second_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        $selected = $timeData['_second'] !== null ? ($timeData['_second'] - $timeData['_second'] % $options['second_interval']) : null;
        for ($i=0; $i <= 59; $i += $options['second_interval']) {
            $_val = sprintf('%02d', $i);
            $_text = $options['second_format'] == '%02d' ? $_val : sprintf($options['second_format'], $i);
            $_value = $options['second_value_format'] == '%02d' ? $_val : sprintf($options['second_value_format'], $i);
            $_html_seconds .= '<option value="' . $_value . '"'
                . ($selected === $i ? ' selected="selected"' : '')
                . '>' . $_text . '</option>' . $options['option_separator'];
        }

        $_html_seconds .= '</select>';
    }

    // generate meridian <select>
    $_html_meridian = null;
    if ($options['display_meridian'] && !$options['use_24_hours']) {
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Meridian]') : ($options['prefix'] . 'Meridian');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['meridian_extra']) {
            $_extra .= ' ' . $options['meridian_extra'];
        }

        $_html_meridian = '<select name="' . $_name . '"';
        if ($options['meridian_id'] !== null || $options['all_id'] !== null) {
            $_html_meridian .= ' id="' . smarty_function_escape_special_chars(
                $options['meridian_id'] !== null ? ( $options['meridian_id'] ? $options['meridian_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['meridian_size']) {
            $_html_meridian .= ' size="' . $options['meridian_size'] . '"';
        }
        $_html_meridian .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['meridian_empty']) || isset($options['all_empty'])) {
            $_html_meridian .= '<option value="">' . ( isset($options['meridian_empty']) ? $options['meridian_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        $_html_meridian .= '<option value="am"'. ($timeData['_hour'] < 12 ? ' selected="selected"' : '') .'>AM</option>' . $options['option_separator']
            . '<option value="pm"'. ($timeData['_hour'] < 12 ? '' : ' selected="selected"') .'>PM</option>' . $options['option_separator']
            . '</select>';
    }

    $_html = '';
    foreach (array($_html_hours, $_html_minutes, $_html_seconds, $_html_meridian) as $k) {
        if (!empty($k)) {
            if ($_html) {
                $_html .= $options['field_separator'];
            }
            $_html .= $k;
        }
    }

    return $_html;
}
