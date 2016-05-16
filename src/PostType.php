<?php

/*
 * The MIT License
 *
 * Copyright 2016 DJ Walker <donwalker1987@gmail.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace dwalkr\WPAdminUtility;

/**
 * Description of PostType
 *
 * @author DJ Walker <donwalker1987@gmail.com>
 */
class PostType {

    private static $registerOptions = array(//all the options you can pass into register_post_type minus the ones already being used
        'description',
        'public',
        'exclude_from_search',
        'publicly_queryable',
        'show_ui',
        'show_in_nav_menus',
        'show_in_menu',
        'show_in_admin_bar',
        'menu_position',
        'menu_icon',
        'capability_type',
        'capabilities',
        'map_meta_cap',
        'hierarchical',
        'supports',
        'taxonomies',
        'has_archive',
        'permalink_epmask',
        'rewrite',
        'query_var',
        'can_export',
        'show_in_rest',
        'rest_base',
        'rest_controller_class',
    );

    private $configData;
    private $templateHandler;
    private $metaboxes = array();

    public static function createFromConfig($configData, $templateHandler) {
        return new static($configData, $templateHandler);
    }

    public function __construct($configData, $templateHandler) {
        $this->configData = $configData;
        $this->templateHandler = $templateHandler;

        add_action('init', array($this, 'register'));
        add_action('save_post', array($this, 'save'));

    }

    public function register() {
        $args = self::generatePostArgs($this->configData);
        $args['register_meta_box_cb'] = array($this, 'addMetaBoxes');

        register_post_type($this->configData->name, $args);

        if (property_exists($this->configData, 'metaboxes')) {
            foreach ($this->configData->metaboxes as $boxData) {
                $this->metaboxes[] = new MetaBox($boxData, $this->templateHandler);
            }
        }
    }

    public function addMetaBoxes() {
        foreach ($this->metaboxes as $i=>$metabox) {
            add_meta_box($this->configData->name.'_'.$i,
                        $metabox->getTitle(),
                        array($metabox, 'display'),
                        $metabox->getScreen(),
                        $metabox->getContext(),
                        $metabox->getPriority());
        }
    }

    public function save($post_id) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }
        if (get_post_type($post_id) !== $this->configData->name) {
            return $post_id;
        }
        foreach ($this->metaboxes as $metabox) {
            $metabox->save($post_id);
        }
    }

    private static function generatePostArgs($data) {
        $args = array();

        if (!$data->labels->singular) {
            $data->labels->singular = $data->name;
        }
        if (!$data->labels->plural) {
            $data->labels->plural = $data->labels->singular . 's';
        }

        $args['labels'] = self::generateLabelsArray($data->labels);

        foreach (self::$registerOptions as $key) {
            if (property_exists($data, $key)) {
                $args[$key] = $data->$key;
            }
        }

        return $args;
    }

    private static function generateLabelsArray($labels) {
        return array(
            'name' => $labels->plural,
            'singular_name' => $labels->singular,
            'menu_name' => $labels->plural,
            'new_admin_bar' => $labels->singular,
            'add_new' => 'Add New '.$labels->singular,
            'add_new_item' => 'Add '.$labels->singular,
            'new_item' => $labels->singular,
            'edit_item' => $labels->singular,
            'view_item' => 'View '.$labels->singular,
            'all_items' => 'All '.$labels->plural,
            'search_item' => 'Search ' . $labels->singular,
            'parent_item_colon' => 'Parent ' .$labels->singular . ':',
            'not_found' => 'No '.$labels->plural . ' found',
            'not_found_in_trash' => 'No ' . $labels->plural . ' found in trash',
        );
    }

}