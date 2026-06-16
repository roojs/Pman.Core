<?php

/**
 * Parse BJS files.
 *
 * Supports:
 * - Legacy format: top-level "items" array, objects with xtype, "String name", etc.
 * - BJS v3 format: top-level "tree" with node-type, prop-type, children, prop-name, prop-val.
 *
 * Extracts $this->fields (form field names) and $this->cols (grid column model objects).
 */

class Pman_Core_Bjs {

    var $json;
    var $fields = array();
    var $cols = array();

    function __construct($file)
    {
        $this->json = json_decode(file_get_contents($file));
        if (isset($this->json->tree)) {
            $this->iterateFieldsV3($this->json->tree);
            $this->iterateColumnsV3($this->json->tree);
        } else {
            $items = isset($this->json->items) ? $this->json->items : array();
            $this->iterateFields($items);
            $this->iterateColumns($items);
        }
    }

    /**
     * Get a property value from a v3 node's children (prop-name => prop-val).
     */
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

    function iterateFieldsV3($node)
    {
        if (!is_object($node)) {
            return;
        }
        $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';
        if (strpos($propType, 'Roo.form.') === 0) {
            $type = str_replace('Roo.form.', '', $propType);
            switch ($type) {
                case 'ComboBox':
                    $hiddenName = $this->getNodeProp($node, 'hiddenName');
                    if ($hiddenName !== null) {
                        $this->fields[] = $hiddenName;
                    }
                    $name = $this->getNodeProp($node, 'name');
                    if ($name !== null) {
                        $this->fields[] = $name;
                    }
                    break;
                case 'MoneyField':
                    $currencyName = $this->getNodeProp($node, 'currencyName');
                    if ($currencyName !== null) {
                        $this->fields[] = $currencyName;
                    }
                    $name = $this->getNodeProp($node, 'name');
                    if ($name !== null) {
                        $this->fields[] = $name;
                    }
                    break;
                case 'ComboBoxArray':
                    break;
                case 'Row':
                case 'FieldSet':
                case 'Form':
                    break;
                default:
                    $name = $this->getNodeProp($node, 'name');
                    if ($name !== null) {
                        $this->fields[] = $name;
                    }
                    break;
            }
        }
        if (!empty($node->children) && is_array($node->children)) {
            foreach ($node->children as $child) {
                $this->iterateFieldsV3($child);
            }
        }
    }

    function iterateColumnsV3($node)
    {
        if (!is_object($node)) {
            return;
        }
        $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';
        if ($propType === 'Roo.grid.ColumnModel') {
            $col = new stdClass();
            $col->dataIndex = $this->getNodeProp($node, 'dataIndex');
            $col->header = $this->getNodeProp($node, 'header');
            $col->width = $this->getNodeProp($node, 'width');
            if ($col->dataIndex !== null || $col->header !== null) {
                $this->cols[] = $col;
            }
        }
        if (empty($node->children) || !is_array($node->children)) {
            return;
        }
        foreach ($node->children as $child) {
            $this->iterateColumnsV3($child);
        }
    }

    function iterateFields($ar)
    {
        foreach ($ar as $o) {
            switch ($o->xtype) {
                case "ComboBox":
                    if (!isset($o->{'String hiddenName'})) {
                        continue 2;
                    }
                    $this->fields[] = $o->{'String hiddenName'};
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    if (!isset($o->{$k})) {
                        break;
                    }
                    $this->fields[] = $o->{$k};

                case "Input":
                case "TextArea":
                case "CheckBox":
                case "DateField":
                case "Radio":
                case "RadioSet":
                case "PhoneInput":
                case "NumberField":
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    if (!isset($o->{$k})) {
                        echo "missing string name";
                        print_r($o);
                        exit;
                    }
                    $this->fields[] = $o->{$k};
                    break;

                case "MoneyField":
                    $k = isset($o->{'String currencyName'}) ? 'String currencyName' : 'string currencyName';
                    $this->fields[] = $o->{$k};
                    $k = isset($o->{'String name'}) ? 'String name' : 'string name';
                    $this->fields[] = $o->{$k};
                    break;
                default:
                    if (isset($o->items)) {
                        $this->iterateFields($o->items);
                    }
            }
        }
    }

    function iterateColumns($ar)
    {
        foreach ($ar as $o) {
            switch ($o->xtype) {
                case "ColumnModel":
                    $this->cols[] = $o;
                    break;
                default:
                    if (isset($o->items)) {
                        $this->iterateColumns($o->items);
                    }
            }
        }
    }
}
