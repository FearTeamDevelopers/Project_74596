<?php

namespace Admin\Helper;

class FormPrinter
{
    /**
     * @param type $object
     * @param type $atribute
     *
     * @return type
     */
    public static function iset($object, $atribute, $default = '')
    {
        return isset($object) ? htmlentities($object->$atribute) : $default;
    }

    /**
     * @param type $name
     * @param type $value
     * @param type $class
     * @param type $required
     *
     * @return type
     */
    public static function textInput($name, $value = array(), $required = false, $class = 'width80', $placeholder = '')
    {
        $htmlTag = '<input type="text" name="'.$name.'"';
        $htmlTagEnd = '/>';

        if (is_array($value) && !empty($value)) {
            $default = isset($value[2]) ? $value[2] : '';
            $defaultValue = self::iset($value[0], $value[1], $default);
            $htmlTag .= ' value="'.$defaultValue.'" ';
        }

        if ($placeholder !== '') {
            $htmlTag .= ' placeholder="'.$placeholder.'" ';
        }

        if ($class !== '') {
            $htmlTag .= ' class="'.$class.'" ';
        }

        if ($required) {
            $htmlTag .= ' required ';
        }

        return mb_ereg_replace('\s+', ' ', $htmlTag.$htmlTagEnd);
    }

    /**
     * @param type $name
     * @param type $value
     * @param type $required
     * @param type $class
     * @param type $placeholder
     *
     * @return type
     */
    public static function timeInput($name, $value = array(), $required = false, $class = 'width80', $placeholder = '')
    {
        $htmlTag = '<input type="time" name="'.$name.'"';
        $htmlTagEnd = '/>';

        if (is_array($value) && !empty($value)) {
            $default = isset($value[2]) ? $value[2] : '';
            $defaultValue = self::iset($value[0], $value[1], $default);
            $htmlTag .= ' value="'.$defaultValue.'" ';
        }

        if ($placeholder !== '') {
            $htmlTag .= ' placeholder="'.$placeholder.'" ';
        }

        if ($class !== '') {
            $htmlTag .= ' class="'.$class.'" ';
        }

        if ($required) {
            $htmlTag .= ' required ';
        }

        return mb_ereg_replace('\s+', ' ', $htmlTag.$htmlTagEnd);
    }

    /**
     * @param type $type
     * @param type $name
     * @param type $value
     * @param type $options
     *
     * @return type
     */
    public static function input($type, $name, $value = array(), $options = array())
    {
        $htmlTag = '<input type="%s" name="%s"';
        $htmlTagEnd = '/>';

        if (is_array($value) && !empty($value)) {
            $default = isset($value[2]) ? $value[2] : '';
            $defaultValue = self::iset($value[0], $value[1], $default);
            $htmlTag .= ' value="'.$defaultValue.'" ';
        }

        if (!isset($options['class'])) {
            $htmlTag .= ' class="width80" ';
        }

        foreach ($options as $key => $value) {
            if ($value === true) {
                $htmlTag .= ' '.$key.' ';
            } else {
                $htmlTag .= ' '.$key.'="'.$value.'" ';
            }
        }

        return mb_ereg_replace('\s+', ' ', sprintf($htmlTag, $type, $name).$htmlTagEnd);
    }
}
