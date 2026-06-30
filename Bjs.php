<?php

/**
 * Parse BJS files.
 *
 * Supports:
 * - Legacy format: top-level "items" array, objects with xtype, "String name", etc.
 * - BJS v3 format: top-level "tree" with node-type, prop-type, children, prop-name, prop-val.
 *
 * Extracts $this->fields (form field names) and $this->cols (grid column model objects).
 * Also $this->urls, $this->fieldDetails, $this->titles for UI catalog.
 */
class Pman_Core_Bjs
{
    var $json;
    var $fields = array();
    var $cols = array();
    var $urls = array();
    var $fieldDetails = array();
    var $titles = array();

    function __construct($file)
    {
        $this->json = json_decode(file_get_contents($file));
        if (isset($this->json->tree)) {
            $this->walkTreeV3($this->json->tree);
        } else {
            $items = isset($this->json->items) ? $this->json->items : array();
            $this->walkItems($items);
        }
    }

    function prop($o, $name)
    {
        if (isset($o->{$name})) {
            return $o->{$name};
        }
        foreach (array(
            'String ' . $name,
            'string ' . $name,
            '$ ' . $name,
            '$ String ' . $name,
            '$$ ' . $name,
            '$$ String ' . $name,
        ) as $key) {
            if (isset($o->{$key})) {
                return $o->{$key};
            }
        }
        return null;
    }

    function getNodeProp($node, $propName)
    {
        if (empty($node->children) || !is_array($node->children)) {
            return null;
        }
        foreach ($node->children as $child) {
            if (isset($child->{'prop-name'}) && $child->{'prop-name'} === $propName && isset($child->{'prop-val'})) {
                $v = $child->{'prop-val'};
                return is_array($v) ? implode("\n", $v) : $v;
            }
        }
        return null;
    }

    function fieldLabel($fname, $fieldLabel)
    {
        $ns = isset($this->json->named_strings) ? (array) $this->json->named_strings : array();
        $str = isset($this->json->strings) ? (array) $this->json->strings : array();
        $labelKey = $fname . '_fieldLabel';
        if (!empty($ns[$labelKey]) && !empty($str[$ns[$labelKey]])) {
            return $str[$ns[$labelKey]];
        }
        if ($fieldLabel) {
            return $fieldLabel;
        }
        return $fname;
    }

    function walkItems($items)
    {
        foreach ($items as $o) {
            $xtype = $this->prop($o, 'xtype');
            $xns = $this->prop($o, 'xns');

            switch ($xtype) {
                case 'LayoutDialog':
                case 'NestedLayoutPanel':
                    $title = $this->prop($o, 'title');
                    if ($title && $title[0] != '{') {
                        $this->titles[] = $title;
                    }
                    break;

                case 'GridPanel':
                    $tableName = $this->prop($o, 'tableName');
                    if ($tableName) {
                        $this->urls[] = strtolower($tableName);
                    }
                    break;

                case 'Form':
                    $url = $this->prop($o, 'url');
                    if ($xns == 'Roo.form'
                        && $url
                        && preg_match_all("/\\/Roo\\/([a-zA-Z0-9_]+)(?:\\.php)?/", $url, $m)
                    ) {
                        foreach ($m[1] as $table) {
                            $this->urls[] = strtolower($table);
                        }
                    }
                    break;

                case 'HttpProxy':
                    $url = $this->prop($o, 'url');
                    if ($xns == 'Roo.data'
                        && $url
                        && preg_match_all("/\\/Roo\\/([a-zA-Z0-9_]+)(?:\\.php)?/", $url, $m)
                    ) {
                        foreach ($m[1] as $table) {
                            $this->urls[] = strtolower($table);
                        }
                    }
                    break;

                case 'ColumnModel':
                    $this->cols[] = $o;
                    break;

                case 'ComboBox':
                    if ($xns != 'Roo.form') {
                        break;
                    }
                    if (!($hiddenName = $this->prop($o, 'hiddenName'))) {
                        break;
                    }
                    $this->fields[] = $hiddenName;
                    $this->fieldDetails[$hiddenName] = array(
                        'name' => $hiddenName,
                        'label' => $this->fieldLabel($hiddenName, $this->prop($o, 'fieldLabel')),
                        'type' => $xtype,
                    );
                    if ($name = $this->prop($o, 'name')) {
                        $this->fields[] = $name;
                        $this->fieldDetails[$name] = array(
                            'name' => $name,
                            'label' => $this->fieldLabel($name, $this->prop($o, 'fieldLabel')),
                            'type' => $xtype,
                        );
                    }
                    break;

                case 'Input':
                case 'TextArea':
                case 'CheckBox':
                case 'DateField':
                case 'Radio':
                case 'RadioSet':
                case 'PhoneInput':
                case 'NumberField':
                case 'Hidden':
                    if ($xns != 'Roo.form' || !($name = $this->prop($o, 'name'))) {
                        break;
                    }
                    $this->fields[] = $name;
                    $this->fieldDetails[$name] = array(
                        'name' => $name,
                        'label' => $this->fieldLabel($name, $this->prop($o, 'fieldLabel')),
                        'type' => $xtype,
                    );
                    break;

                case 'MoneyField':
                    if ($xns != 'Roo.form') {
                        break;
                    }
                    if ($currencyName = $this->prop($o, 'currencyName')) {
                        $this->fields[] = $currencyName;
                        $this->fieldDetails[$currencyName] = array(
                            'name' => $currencyName,
                            'label' => $this->fieldLabel($currencyName, $this->prop($o, 'fieldLabel')),
                            'type' => $xtype,
                        );
                    }
                    if ($name = $this->prop($o, 'name')) {
                        $this->fields[] = $name;
                        $this->fieldDetails[$name] = array(
                            'name' => $name,
                            'label' => $this->fieldLabel($name, $this->prop($o, 'fieldLabel')),
                            'type' => $xtype,
                        );
                    }
                    break;
            }

            if (!empty($o->items)) {
                $this->walkItems($o->items);
            }
        }
    }

    function walkTreeV3($node)
    {
        if (!is_object($node)) {
            return;
        }
        $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';

        if ($propType == 'Roo.LayoutDialog' || $propType == 'Roo.NestedLayoutPanel') {
            $title = $this->getNodeProp($node, 'title');
            if ($title && $title[0] != '{') {
                $this->titles[] = $title;
            }
        }
        if ($propType == 'Roo.GridPanel') {
            $tableName = $this->getNodeProp($node, 'tableName');
            if ($tableName) {
                $this->urls[] = strtolower($tableName);
            }
        }
        if ($propType == 'Roo.form.Form'
            && preg_match_all("/\\/Roo\\/([a-zA-Z0-9_]+)(?:\\.php)?/", $this->getNodeProp($node, 'url'), $m)
        ) {
            foreach ($m[1] as $table) {
                $this->urls[] = strtolower($table);
            }
        }
        if ($propType == 'Roo.data.HttpProxy'
            && preg_match_all("/\\/Roo\\/([a-zA-Z0-9_]+)(?:\\.php)?/", $this->getNodeProp($node, 'url'), $m)
        ) {
            foreach ($m[1] as $table) {
                $this->urls[] = strtolower($table);
            }
        }
        if ($propType === 'Roo.grid.ColumnModel') {
            $col = new stdClass();
            $col->dataIndex = $this->getNodeProp($node, 'dataIndex');
            $col->header = $this->getNodeProp($node, 'header');
            $col->width = $this->getNodeProp($node, 'width');
            if ($col->dataIndex !== null || $col->header !== null) {
                $this->cols[] = $col;
            }
        }
        if (strpos($propType, 'Roo.form.') === 0) {
            $type = str_replace('Roo.form.', '', $propType);
            $fieldLabel = $this->getNodeProp($node, 'fieldLabel');
            switch ($type) {
                case 'ComboBox':
                    if ($hiddenName = $this->getNodeProp($node, 'hiddenName')) {
                        $this->fields[] = $hiddenName;
                        $this->fieldDetails[$hiddenName] = array(
                            'name' => $hiddenName,
                            'label' => $this->fieldLabel($hiddenName, $fieldLabel),
                            'type' => $type,
                        );
                    }
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->fields[] = $name;
                        $this->fieldDetails[$name] = array(
                            'name' => $name,
                            'label' => $this->fieldLabel($name, $fieldLabel),
                            'type' => $type,
                        );
                    }
                    break;
                case 'MoneyField':
                    if ($currencyName = $this->getNodeProp($node, 'currencyName')) {
                        $this->fields[] = $currencyName;
                        $this->fieldDetails[$currencyName] = array(
                            'name' => $currencyName,
                            'label' => $this->fieldLabel($currencyName, $fieldLabel),
                            'type' => $type,
                        );
                    }
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->fields[] = $name;
                        $this->fieldDetails[$name] = array(
                            'name' => $name,
                            'label' => $this->fieldLabel($name, $fieldLabel),
                            'type' => $type,
                        );
                    }
                    break;
                case 'ComboBoxArray':
                case 'Row':
                case 'FieldSet':
                case 'Form':
                    break;
                default:
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->fields[] = $name;
                        $this->fieldDetails[$name] = array(
                            'name' => $name,
                            'label' => $this->fieldLabel($name, $fieldLabel),
                            'type' => $type,
                        );
                    }
                    break;
            }
        }
        if (!empty($node->children) && is_array($node->children)) {
            foreach ($node->children as $child) {
                $this->walkTreeV3($child);
            }
        }
    }
}
