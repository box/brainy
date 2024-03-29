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
 * Smarty {html_select_date} plugin
 *
 * Type:     function<br>
 * Name:     html_select_date<br>
 * Purpose:  Prints the dropdowns for date selection.
 *
 * ChangeLog:
 * <pre>
 *            - 1.0 initial release
 *            - 1.1 added support for +/- N syntax for begin
 *              and end year values. (Monte)
 *            - 1.2 added support for yyyy-mm-dd syntax for
 *              time value. (Jan Rosier)
 *            - 1.3 added support for choosing format for
 *              month values (Gary Loescher)
 *            - 1.3.1 added support for choosing format for
 *              day values (Marcus Bointon)
 *            - 1.3.2 support negative timestamps, force year
 *              dropdown to include given date unless explicitly set (Monte)
 *            - 1.3.4 fix behaviour of 0000-00-00 00:00:00 dates to match that
 *              of 0000-00-00 dates (cybot, boots)
 *            - 2.0 complete rewrite for performance,
 *              added attributes month_names, *_id
 * </pre>
 *
 * @link    http://www.smarty.net/manual/en/language.function.html.select.date.php {html_select_date}
 *      (Smarty online manual)
 * @version 2.0
 * @author  Andrei Zmievski
 * @author  Monte Ohrt <monte at ohrt dot com>
 * @author  Rodney Rehm
 * @param   array    $params   parameters
 * @param   Template $template template object
 * @return  string
 */
function smarty_function_html_select_date($params, $template)
{

    $template->assertIsNotStrict('`{html_select_date}` is a deprecated plugin and is not allowed in strict mode');

    // generate timestamps used for month names only
    static $_month_timestamps = null;
    static $_current_year = null;
    if ($_month_timestamps === null) {
        $_current_year = date('Y');
        $_month_timestamps = array();
        for ($i = 1; $i <= 12; $i++) {
            $_month_timestamps[$i] = mktime(0, 0, 0, $i, 1, 2000);
        }
    }

    /* Default values. */
    $options = array(
        'prefix' => "Date_",
        'start_year' => null,
        'end_year' => null,
        'display_days' => true,
        'display_months' => true,
        'display_years' => true,
        'month_format' => "%B",
        /* Write months as numbers by default  GL */
        'month_value_format' => "%m",
        'day_format' => "%02d",
        /* Write day values using this format MB */
        'day_value_format' => "%d",
        'year_as_text' => false,
        /* Display years in reverse order? Ie. 2000,1999,.... */
        'reverse_years' => false,
        /* Should the select boxes be part of an array when returned from PHP?
           e.g. setting it to "birthday", would create "birthday[Day]",
           "birthday[Month]" & "birthday[Year]". Can be combined with prefix */
        'field_array' => null,
        /* <select size>'s of the different <select> tags.
           If not set, uses default dropdown. */
        'day_size' => null,
        'month_size' => null,
        'year_size' => null,
        /* Unparsed attributes common to *ALL* the <select>/<input> tags.
           An example might be in the template: all_extra ='class ="foo"'. */
        'all_extra' => null,
        /* Separate attributes for the tags. */
        'day_extra' => null,
        'month_extra' => null,
        'year_extra' => null,
        /* Order in which to display the fields.
           "D" -> day, "M" -> month, "Y" -> year. */
        'field_order' => 'MDY',
        /* String printed between the different fields. */
        'field_separator' => "\n",
        'option_separator' => "\n",
        'time' => null,
        'rel_time' => null,
        'extra_attrs' => '',
        'all_id' => null,
        'day_id' => null,
        'month_id' => null,
        'year_id' => null,

        'all_empty' => null,
        'day_empty' => null,
        'month_empty' => null,
        'year_empty' => null,
    );

    foreach ($params as $_key => $_value) {
        switch ($_key) {
            case 'rel_time':
            case 'time':
                if (!is_array($_value) && $_value !== null) {
                    $options[$_key] = smarty_make_timestamp($_value);
                }
                break;

            case 'month_names':
                if (is_array($_value) && count($_value) == 12) {
                    $options[$_key] = $_value;
                } else {
                    trigger_error("html_select_date: month_names must be an array of 12 strings", E_USER_NOTICE);
                }
                break;

            case 'prefix':
            case 'field_array':
            case 'start_year':
            case 'end_year':
            case 'day_format':
            case 'day_value_format':
            case 'month_format':
            case 'month_value_format':
            case 'day_size':
            case 'month_size':
            case 'year_size':
            case 'all_extra':
            case 'day_extra':
            case 'month_extra':
            case 'year_extra':
            case 'all_empty':
            case 'day_empty':
            case 'month_empty':
            case 'year_empty':
            case 'field_order':
            case 'field_separator':
            case 'option_separator':
            case 'all_id':
            case 'month_id':
            case 'day_id':
            case 'year_id':
                $options[$_key] = (string) $_value;
                break;

            case 'display_days':
            case 'display_months':
            case 'display_years':
            case 'year_as_text':
            case 'reverse_years':
                $options[$_key] = (bool) $_value;
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

    if (!empty($params['rel_time'])) {
        $_current_year = date('Y', $params['rel_time']);
    }

    $timeData = array(
        '_day' => null,
        '_month' => null,
        '_year' => null,
    );

    // Note: date() is faster than strftime()
    // Note: explode(date()) is faster than date() date() date()
    if (isset($params['time']) && is_array($params['time'])) {
        if (isset($params['time'][$options['prefix'] . 'Year'])) {
            // $_REQUEST[$options['field_array']] given
            foreach (array('Y' => 'Year',  'm' => 'Month', 'd' => 'Day') as $_elementKey => $_elementName) {
                $_variableName = '_' . strtolower($_elementName);
                $timeData[$_variableName] = isset($params['time'][$options['prefix'] . $_elementName])
                    ? $params['time'][$options['prefix'] . $_elementName]
                    : date($_elementKey);
            }
            $options['time'] = mktime(0, 0, 0, $timeData['_month'], $timeData['_day'], $timeData['_year']);
        } elseif (isset($params['time'][$options['field_array']][$options['prefix'] . 'Year'])) {
            // $_REQUEST given
            foreach (array('Y' => 'Year',  'm' => 'Month', 'd' => 'Day') as $_elementKey => $_elementName) {
                $_variableName = '_' . strtolower($_elementName);
                $timeData[$_variableName] = isset($params['time'][$options['field_array']][$options['prefix'] . $_elementName])
                    ? $params['time'][$options['field_array']][$options['prefix'] . $_elementName]
                    : date($_elementKey);
            }
            $options['time'] = mktime(0, 0, 0, $timeData['_month'], $timeData['_day'], $timeData['_year']);
        } else {
            // no date found, use NOW
            list($timeData['_year'], $timeData['_month'], $timeData['_day']) = $options['time'] = explode('-', date('Y-m-d'));
        }
    } elseif ($options['time'] === null) {
        if (array_key_exists('time', $params)) {
            $timeData['_year'] = $timeData['_month'] = $timeData['_day'] = $options['time'] = null;
        } else {
            list($timeData['_year'], $timeData['_month'], $timeData['_day']) = $options['time'] = explode('-', date('Y-m-d'));
        }
    } else {
        list($timeData['_year'], $timeData['_month'], $timeData['_day']) = $options['time'] = explode('-', date('Y-m-d', $options['time']));
    }

    // make syntax "+N" or "-N" work with $options['start_year'] and $options['end_year']
    // Note preg_match('!^(\+|\-)\s*(\d+)$!', $options['end_year'], $match) is slower than trim+substr
    foreach (array('start', 'end') as $key) {
        $key .= '_year';
        $t = $options[$key];
        if ($t === null) {
            $options[$key] = (int) $_current_year;
        } elseif ($t[0] == '+') {
            $options[$key] = (int) ($_current_year + trim(substr($t, 1)));
        } elseif ($t[0] == '-') {
            $options[$key] = (int) ($_current_year - trim(substr($t, 1)));
        } else {
            $options[$key] = (int) $options[$key];
        }
    }

    // flip for ascending or descending
    if (($options['start_year'] > $options['end_year'] && !$options['reverse_years']) || ($options['start_year'] < $options['end_year'] && $options['reverse_years'])) {
        $t = $options['end_year'];
        $options['end_year'] = $options['start_year'];
        $options['start_year'] = $t;
    }

    // generate year <select> or <input>
    if ($options['display_years']) {
        $_html_years = '';
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Year]') : ($options['prefix'] . 'Year');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['year_extra']) {
            $_extra .= ' ' . $options['year_extra'];
        }

        if ($options['year_as_text']) {
            $_html_years = '<input type="text" name="' . $_name . '" value="' . $timeData['_year'] . '" size="4" maxlength="4"' . $_extra . $options['extra_attrs'] . ' />';
        } else {
            $_html_years = '<select name="' . $_name . '"';
            if ($options['year_id'] !== null || $options['all_id'] !== null) {
                $_html_years .= ' id="' . smarty_function_escape_special_chars(
                    $options['year_id'] !== null ? ( $options['year_id'] ? $options['year_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
                ) . '"';
            }
            if ($options['year_size']) {
                $_html_years .= ' size="' . $options['year_size'] . '"';
            }
            $_html_years .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

            if (isset($options['year_empty']) || isset($options['all_empty'])) {
                $_html_years .= '<option value="">' . ( isset($options['year_empty']) ? $options['year_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
            }

            $op = $options['start_year'] > $options['end_year'] ? -1 : 1;
            for ($i=$options['start_year']; $op > 0 ? $i <= $options['end_year'] : $i >= $options['end_year']; $i += $op) {
                $_html_years .= '<option value="' . $i . '"'
                    . ($timeData['_year'] == $i ? ' selected="selected"' : '')
                    . '>' . $i . '</option>' . $options['option_separator'];
            }

            $_html_years .= '</select>';
        }
    }

    // generate month <select> or <input>
    if ($options['display_months']) {
        $_html_month = '';
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Month]') : ($options['prefix'] . 'Month');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['month_extra']) {
            $_extra .= ' ' . $options['month_extra'];
        }

        $_html_months = '<select name="' . $_name . '"';
        if ($options['month_id'] !== null || $options['all_id'] !== null) {
            $_html_months .= ' id="' . smarty_function_escape_special_chars(
                $options['month_id'] !== null ? ( $options['month_id'] ? $options['month_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['month_size']) {
            $_html_months .= ' size="' . $options['month_size'] . '"';
        }
        $_html_months .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['month_empty']) || isset($options['all_empty'])) {
            $_html_months .= '<option value="">' . ( isset($options['month_empty']) ? $options['month_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        for ($i = 1; $i <= 12; $i++) {
            $_val = sprintf('%02d', $i);
            $_text = isset($options['month_names']) ? smarty_function_escape_special_chars($options['month_names'][$i]) : ($options['month_format'] == "%m" ? $_val : __brainy_format_month($options['month_format'], $_month_timestamps[$i]));
            $_value = $options['month_value_format'] == "%m" ? $_val : __brainy_format_month($options['month_value_format'], $_month_timestamps[$i]);
            $_html_months .= '<option value="' . $_value . '"'
                . ($_val == $timeData['_month'] ? ' selected="selected"' : '')
                . '>' . $_text . '</option>' . $options['option_separator'];
        }

        $_html_months .= '</select>';
    }

    // generate day <select> or <input>
    if ($options['display_days']) {
        $_html_day = '';
        $_extra = '';
        $_name = $options['field_array'] ? ($options['field_array'] . '[' . $options['prefix'] . 'Day]') : ($options['prefix'] . 'Day');
        if ($options['all_extra']) {
            $_extra .= ' ' . $options['all_extra'];
        }
        if ($options['day_extra']) {
            $_extra .= ' ' . $options['day_extra'];
        }

        $_html_days = '<select name="' . $_name . '"';
        if ($options['day_id'] !== null || $options['all_id'] !== null) {
            $_html_days .= ' id="' . smarty_function_escape_special_chars(
                $options['day_id'] !== null ? ( $options['day_id'] ? $options['day_id'] : $_name ) : ( $options['all_id'] ? ($options['all_id'] . $_name) : $_name )
            ) . '"';
        }
        if ($options['day_size']) {
            $_html_days .= ' size="' . $options['day_size'] . '"';
        }
        $_html_days .= $_extra . $options['extra_attrs'] . '>' . $options['option_separator'];

        if (isset($options['day_empty']) || isset($options['all_empty'])) {
            $_html_days .= '<option value="">' . ( isset($options['day_empty']) ? $options['day_empty'] : $options['all_empty'] ) . '</option>' . $options['option_separator'];
        }

        for ($i = 1; $i <= 31; $i++) {
            $_val = sprintf('%02d', $i);
            $_text = $options['day_format'] == '%02d' ? $_val : sprintf($options['day_format'], $i);
            $_value = $options['day_value_format'] ==  '%02d' ? $_val : sprintf($options['day_value_format'], $i);
            $_html_days .= '<option value="' . $_value . '"'
                . ($_val == $timeData['_day'] ? ' selected="selected"' : '')
                . '>' . $_text . '</option>' . $options['option_separator'];
        }

        $_html_days .= '</select>';
    }

    // order the fields for output
    $_html = '';
    for ($i = 0; $i <= 2; $i++) {
        switch ($options['field_order'][$i]) {
            case 'Y':
            case 'y':
                if (isset($_html_years)) {
                    if ($_html) {
                        $_html .= $options['field_separator'];
                    }
                    $_html .= $_html_years;
                }
                break;

            case 'm':
            case 'M':
                if (isset($_html_months)) {
                    if ($_html) {
                        $_html .= $options['field_separator'];
                    }
                    $_html .= $_html_months;
                }
                break;

            case 'd':
            case 'D':
                if (isset($_html_days)) {
                    if ($_html) {
                        $_html .= $options['field_separator'];
                    }
                    $_html .= $_html_days;
                }
                break;
        }
    }

    return $_html;
}

/**
 * This function behaves like strftime(). strftime() is deprecated, though,
 * so this translates the format to a `DateTime::format`-compatible format for
 * use with date().
 */
function __brainy_format_month($strftime_format, $value) {
    switch ($strftime_format) {
        case '%b':
        case '%h':
            return date('M', $value);
        case '%B':
            return date('F', $value);
        case '%m':
            return date('m', $value);
        default:
            throw new \Box\Brainy\Exceptions\SmartyException("Cannot use month format '$strftime_format'");
    }
}
