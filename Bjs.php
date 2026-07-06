<?php

/**
 * Parse BJS files.
 *
 * Supports:
 * - Legacy format: top-level "items" array, objects with xtype, "String name", etc.
 * - BJS v3 format: top-level "tree" with node-type, prop-type, children, prop-name, prop-val.
 *
 * Extracts $this->fields (form field names) and $this->cols (grid column model objects).
 * Also $this->urls, $this->fieldDetails, $this->titles, $this->tabs for UI catalog.
 */
class Pman_Core_Bjs
{
    var $json;
    var $fields = array();
    var $cols = array();
    var $urls = array();
    var $fieldDetails = array();
    var $titles = array();
    var $tabs = array();
    var $fieldTabPaths = array();
    var $tabPathsSeen = array();

    function __construct($file)
    {
        $this->json = json_decode(file_get_contents($file));
        if (isset($this->json->tree)) {
            $this->walkTreeV3($this->json->tree);
            $this->extractDialogTabsFromTreeV3($this->json->tree);
        } else {
            $items = isset($this->json->items) ? $this->json->items : array();
            $this->walkItems($items);
            $this->extractDialogTabsFromModuleItems($items);
        }
    }

    function tablesFromRooUrl($url)
    {
        if (!$url || !preg_match_all("/\\/Roo\\/([a-zA-Z0-9_]+)(?:\\.php)?/", $url, $m)) {
            return array();
        }
        $tables = array();
        foreach ($m[1] as $table) {
            $tables[] = strtolower($table);
        }
        return $tables;
    }

    function addTableUrl($table, &$scope = null)
    {
        $table = strtolower($table);
        if ($scope !== null) {
            $scope['urls'][] = $table;
            return;
        }
        $this->urls[] = $table;
    }

    function addFieldDetail($name, $o, $xtype, &$scope = null)
    {
        $detail = array(
            'name' => $name,
            'label' => $this->fieldLabel($name, $this->prop($o, 'fieldLabel')),
            'type' => $xtype,
        );
        if ($scope !== null) {
            $scope['fields'][$name] = $detail;
            return;
        }
        $this->fields[] = $name;
        $this->fieldDetails[$name] = $detail;
    }

    function prop($o, $name)
    {
        if (isset($o->{'* ' . $name})) {
            return $o->{'* ' . $name};
        }
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

    function walkItems($items, &$scope = null)
    {
        if (!$items || !is_array($items)) {
            return;
        }

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
                        $this->addTableUrl($tableName, $scope);
                    }
                    break;

                case 'Form':
                    if ($xns != 'Roo.form') {
                        break;
                    }
                    foreach ($this->tablesFromRooUrl($this->prop($o, 'url')) as $table) {
                        $this->addTableUrl($table, $scope);
                    }
                    break;

                case 'HttpProxy':
                    if ($xns != 'Roo.data') {
                        break;
                    }
                    foreach ($this->tablesFromRooUrl($this->prop($o, 'url')) as $table) {
                        $this->addTableUrl($table, $scope);
                    }
                    break;

                case 'ColumnModel':
                    if ($scope === null) {
                        $this->cols[] = $o;
                    }
                    break;

                case 'ComboBox':
                    if ($xns != 'Roo.form') {
                        break;
                    }
                    if (!($hiddenName = $this->prop($o, 'hiddenName'))) {
                        break;
                    }
                    $this->addFieldDetail($hiddenName, $o, $xtype, $scope);
                    if ($name = $this->prop($o, 'name')) {
                        $this->addFieldDetail($name, $o, $xtype, $scope);
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
                case 'Password':
                case 'Checkbox':
                    if ($xns != 'Roo.form' || !($name = $this->prop($o, 'name'))) {
                        break;
                    }
                    $this->addFieldDetail($name, $o, $xtype, $scope);
                    break;

                case 'MoneyField':
                    if ($xns != 'Roo.form') {
                        break;
                    }
                    if ($currencyName = $this->prop($o, 'currencyName')) {
                        $this->addFieldDetail($currencyName, $o, $xtype, $scope);
                    }
                    if ($name = $this->prop($o, 'name')) {
                        $this->addFieldDetail($name, $o, $xtype, $scope);
                    }
                    break;
            }

            if ($scope !== null) {
                if ($xtype == 'Form' && $xns == 'Roo.form') {
                    $scope['hasForm'] = true;
                }
                if (in_array($xtype, array('Grid', 'GridPanel'))) {
                    $scope['hasGrid'] = true;
                }
            }

            if (!empty($o->items)) {
                $this->walkItems($o->items, $scope);
            }
        }
    }

    function walkTreeV3($node, &$scope = null)
    {
        if (!is_object($node)) {
            return;
        }
        $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';

        if ($scope === null && ($propType == 'Roo.LayoutDialog' || $propType == 'Roo.NestedLayoutPanel')) {
            $title = $this->getNodeProp($node, 'title');
            if ($title && $title[0] != '{') {
                $this->titles[] = $title;
            }
        }
        if ($propType == 'Roo.GridPanel') {
            if ($tableName = $this->getNodeProp($node, 'tableName')) {
                $this->addTableUrl($tableName, $scope);
            }
        }
        if ($propType == 'Roo.form.Form') {
            foreach ($this->tablesFromRooUrl($this->getNodeProp($node, 'url')) as $table) {
                $this->addTableUrl($table, $scope);
            }
        }
        if ($propType == 'Roo.data.HttpProxy') {
            foreach ($this->tablesFromRooUrl($this->getNodeProp($node, 'url')) as $table) {
                $this->addTableUrl($table, $scope);
            }
        }
        if ($scope === null && $propType === 'Roo.grid.ColumnModel') {
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
            $fieldObj = new stdClass();
            if ($fieldLabel !== null) {
                $fieldObj->{'String fieldLabel'} = $fieldLabel;
            }
            switch ($type) {
                case 'ComboBox':
                    if ($hiddenName = $this->getNodeProp($node, 'hiddenName')) {
                        $this->addFieldDetail($hiddenName, $fieldObj, $type, $scope);
                    }
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->addFieldDetail($name, $fieldObj, $type, $scope);
                    }
                    break;
                case 'MoneyField':
                    if ($currencyName = $this->getNodeProp($node, 'currencyName')) {
                        $this->addFieldDetail($currencyName, $fieldObj, $type, $scope);
                    }
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->addFieldDetail($name, $fieldObj, $type, $scope);
                    }
                    break;
                case 'ComboBoxArray':
                case 'Row':
                case 'FieldSet':
                case 'Form':
                    break;
                default:
                    if ($name = $this->getNodeProp($node, 'name')) {
                        $this->addFieldDetail($name, $fieldObj, $type, $scope);
                    }
                    break;
            }
        }

        if ($scope !== null) {
            if ($propType == 'Roo.form.Form') {
                $scope['hasForm'] = true;
            }
            if ($propType == 'Roo.GridPanel') {
                $scope['hasGrid'] = true;
            }
        }

        if (!empty($node->children) && is_array($node->children)) {
            foreach ($node->children as $child) {
                $this->walkTreeV3($child, $scope);
            }
        }
    }

    function isTabRegion($o)
    {
        if (!$this->prop($o, 'tabPosition')) {
            return false;
        }
        if (in_array($this->prop($o, 'xtype'), array('Region', 'LayoutRegion'))) {
            return true;
        }
        return in_array($this->prop($o, 'prop'), array('center', 'south', 'north', 'east', 'west'));
    }

    function isTabPanel($o)
    {
        $region = $this->prop($o, 'region');
        if (!$region || strtolower($region) != 'center') {
            return false;
        }
        if (in_array($this->prop($o, 'xtype'), array(
            'Content', 'ContentPanel', 'Grid', 'GridPanel', 'NestedLayoutPanel', 'BorderLayout',
        ))) {
            return true;
        }
        return $this->prop($o, 'title') !== null;
    }

    function findTabRegionIndex($items)
    {
        if (!$items || !is_array($items)) {
            return -1;
        }
        foreach ($items as $i => $o) {
            if ($this->isTabRegion($o)) {
                return $i;
            }
        }
        return -1;
    }

    function findNestedTabRegionItems($items)
    {
        if (!$items || !is_array($items)) {
            return false;
        }
        foreach ($items as $o) {
            if ($this->prop($o, 'prop') == 'layout'
                && !empty($o->items)
                && $this->findTabRegionIndex($o->items) >= 0
            ) {
                return $o->items;
            }
            if (!in_array($this->prop($o, 'xtype'), array('BorderLayout', 'NestedLayoutPanel'))) {
                if (empty($o->items)) {
                    continue;
                }
                $nested = $this->findNestedTabRegionItems($o->items);
                if ($nested !== false) {
                    return $nested;
                }
                continue;
            }
            if (empty($o->items) || $this->findTabRegionIndex($o->items) < 0) {
                continue;
            }
            return $o->items;
        }
        return false;
    }

    function scanPanelSubtree($panel)
    {
        $scope = array(
            'urls' => array(),
            'fields' => array(),
            'hasForm' => false,
            'hasGrid' => false,
        );
        $items = $this->prop($panel, 'items');
        if (!$items || !is_array($items)) {
            return $scope;
        }
        $this->walkItems($items, $scope);
        $scope['fields'] = array_values($scope['fields']);
        $scope['urls'] = array_values(array_unique($scope['urls']));
        return $scope;
    }

    function addFlatTab($path, $title, $type, $scan)
    {
        if (isset($this->tabPathsSeen[$path])) {
            return;
        }
        $this->tabPathsSeen[$path] = true;

        $entry = array(
            'path' => $path,
            'title' => $title,
            'type' => $type,
        );
        if (!empty($scan['urls'])) {
            $entry['relatedTables'] = $scan['urls'];
        }
        if (empty($scan['fields'])) {
            $this->tabs[] = $entry;
            return;
        }
        $entry['fields'] = $scan['fields'];
        foreach ($scan['fields'] as $f) {
            if (empty($f['name'])) {
                continue;
            }
            $this->fieldTabPaths[$f['name']] = $path;
        }
        $this->tabs[] = $entry;
    }

    function processTabRegionItems($items, $ancestorSlugs, $depth, $isRootRegion)
    {
        $regionIdx = $this->findTabRegionIndex($items);
        if ($regionIdx < 0) {
            return;
        }

        $tabIndex = 0;
        for ($i = $regionIdx + 1; $i < count($items); $i++) {
            $panel = $items[$i];
            if (!$this->isTabPanel($panel)) {
                continue;
            }

            $title = $this->prop($panel, 'title');
            if (!$title || $title[0] == '{') {
                $title = 'Untitled';
            } else {
                $title = trim($title);
            }

            $slug = trim(preg_replace(
                '#[^a-z0-9]+#',
                '-',
                preg_replace('#[/\\\\]+#', '-', strtolower(trim($title)))
            ), '-');
            if ($slug === '') {
                $slug = 'untitled';
            }

            if ($isRootRegion && $depth == 0 && $tabIndex == 0) {
                $path = '/';
                $childAncestorSlugs = array($slug);
            } elseif ($depth == 0) {
                $path = '/' . $slug;
                $childAncestorSlugs = array($slug);
            } else {
                $childAncestorSlugs = array_merge($ancestorSlugs, array($slug));
                $path = '/' . implode('/', $childAncestorSlugs);
            }

            $panelItems = $this->prop($panel, 'items');
            $nestedItems = $this->findNestedTabRegionItems(
                $panelItems && is_array($panelItems) ? $panelItems : array()
            );
            $scan = $this->scanPanelSubtree($panel);

            $type = 'mixed';
            if ($nestedItems === false) {
                if ($scan['hasForm'] && !$scan['hasGrid']) {
                    $type = 'form';
                }
                if ($scan['hasGrid'] && !$scan['hasForm']) {
                    $type = 'grid';
                }
            }

            $this->addFlatTab($path, $title, $type, $scan);

            if ($nestedItems !== false) {
                $this->processTabRegionItems($nestedItems, $childAncestorSlugs, $depth + 1, false);
            }

            $tabIndex++;
        }
    }

    function extractDialogTabsFromModuleItems($items)
    {
        if (!$items || !is_array($items)) {
            return;
        }
        $this->tabPathsSeen = array();
        foreach ($items as $o) {
            if ($this->prop($o, 'xtype') != 'LayoutDialog' || empty($o->items)) {
                continue;
            }
            $this->processTabRegionItems($o->items, array(), 0, true);
            return;
        }
    }

    function itemsFromTreeChildren($nodes)
    {
        if (!$nodes || !is_array($nodes)) {
            return array();
        }
        $items = array();
        foreach ($nodes as $node) {
            if (!is_object($node)) {
                continue;
            }
            $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';
            if (!$propType || strpos($propType, 'Roo.') !== 0 || $propType == 'Roo.Button') {
                continue;
            }
            $o = new stdClass();
            if (strpos($propType, 'Roo.form.') === 0) {
                $o->{'$ xns'} = 'Roo.form';
                $o->xtype = substr($propType, strlen('Roo.form.'));
            } elseif (strpos($propType, 'Roo.layout.') === 0) {
                $o->{'$ xns'} = 'Roo.layout';
                $o->xtype = substr($propType, strlen('Roo.layout.'));
            } elseif (strpos($propType, 'Roo.panel.') === 0) {
                $o->{'$ xns'} = 'Roo.panel';
                $o->xtype = substr($propType, strlen('Roo.panel.'));
            } else {
                $o->{'$ xns'} = 'Roo';
                $o->xtype = preg_replace('/^Roo\\./', '', $propType);
            }
            $propName = isset($node->{'prop-name'}) ? $node->{'prop-name'} : '';
            if (in_array($propName, array('center', 'south', 'north', 'east', 'west', 'layout'))) {
                $o->{'* prop'} = $propName;
            }
            foreach (array('title', 'region', 'tabPosition', 'tableName') as $p) {
                $v = $this->getNodeProp($node, $p);
                if ($v !== null && $v !== '') {
                    $o->{$p} = $v;
                }
            }
            if (!empty($node->children) && is_array($node->children)) {
                $childItems = $this->itemsFromTreeChildren($node->children);
                if ($childItems) {
                    $o->items = $childItems;
                }
            }
            $items[] = $o;
        }
        return $items;
    }

    function extractDialogTabsFromTreeV3($node)
    {
        if (!is_object($node)) {
            return;
        }
        $propType = isset($node->{'prop-type'}) ? $node->{'prop-type'} : '';
        if ($propType == 'Roo.LayoutDialog' && !empty($node->children)) {
            $this->tabPathsSeen = array();
            $this->processTabRegionItems($this->itemsFromTreeChildren($node->children), array(), 0, true);
            return;
        }
        if (empty($node->children) || !is_array($node->children)) {
            return;
        }
        foreach ($node->children as $child) {
            $this->extractDialogTabsFromTreeV3($child);
            if (!empty($this->tabs)) {
                return;
            }
        }
    }
}
