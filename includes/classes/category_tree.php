<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2007 osCommerce

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License v2 (1991)
  as published by the Free Software Foundation.
*/

  class osC_CategoryTree {

/**
 * Flag to control if the total number of products in a category should be calculated
 *
 * @var boolean
 * @access protected
 */

    protected $_show_total_products = false;

/**
 * Array containing the category structure relationship data
 *
 * @var array
 * @access protected
 */

    protected $_data = array();

    var $root_category_id = 0,
        $max_level = 0,
        $root_start_string = '',
        $root_end_string = '',
        $parent_start_string = '',
        $parent_end_string = '',
        $parent_group_start_string = '<ul>',
        $parent_group_end_string = '</ul>',
        $child_start_string = '<li>',
        $child_end_string = '</li>',
        $breadcrumb_separator = '_',
        $breadcrumb_usage = true,
        $spacer_string = '',
        $spacer_multiplier = 1,
        $follow_cpath = false,
        $cpath_array = array(),
        $cpath_start_string = '',
        $cpath_end_string = '',
        $category_product_count_start_string = '&nbsp;(',
        $category_product_count_end_string = ')';

/**
 * Constructor; load the category structure relationship data from the database
 *
 * @access public
 */

    public function __construct() {
      global $osC_Database, $osC_Cache, $osC_Language;

      if ( SERVICES_CATEGORY_PATH_CALCULATE_PRODUCT_COUNT == '1' ) {
        $this->_show_total_products = true;
      }

      if ( $osC_Cache->read('category_tree-' . $osC_Language->getCode(), 720) ) {
        $this->_data = $osC_Cache->getCache();
      } else {
        $Qcategories = $osC_Database->query('select c.categories_id, c.parent_id, c.categories_image, cd.categories_name from :table_categories c, :table_categories_description cd where c.categories_id = cd.categories_id and cd.language_id = :language_id order by c.parent_id, c.sort_order, cd.categories_name');
        $Qcategories->bindTable(':table_categories', TABLE_CATEGORIES);
        $Qcategories->bindTable(':table_categories_description', TABLE_CATEGORIES_DESCRIPTION);
        $Qcategories->bindInt(':language_id', $osC_Language->getID());
        $Qcategories->execute();

        while ( $Qcategories->next() ) {
          $this->_data[$Qcategories->valueInt('parent_id')][$Qcategories->valueInt('categories_id')] = array('name' => $Qcategories->value('categories_name'),
                                                                                                             'image' => $Qcategories->value('categories_image'),
                                                                                                             'count' => 0);
        }

        if ( $this->_show_total_products === true ) {
          $this->_calculateProductTotals();
        }

        $osC_Cache->write($this->_data);
      }
    }

    function reset() {
      $this->root_category_id = 0;
      $this->max_level = 0;
      $this->root_start_string = '';
      $this->root_end_string = '';
      $this->parent_start_string = '';
      $this->parent_end_string = '';
      $this->parent_group_start_string = '<ul>';
      $this->parent_group_end_string = '</ul>';
      $this->child_start_string = '<li>';
      $this->child_end_string = '</li>';
      $this->breadcrumb_separator = '_';
      $this->breadcrumb_usage = true;
      $this->spacer_string = '';
      $this->spacer_multiplier = 1;
      $this->follow_cpath = false;
      $this->cpath_array = array();
      $this->cpath_start_string = '';
      $this->cpath_end_string = '';
      $this->_show_total_products = (SERVICES_CATEGORY_PATH_CALCULATE_PRODUCT_COUNT == '1') ? true : false;
      $this->category_product_count_start_string = '&nbsp;(';
      $this->category_product_count_end_string = ')';
    }

/**
 * Return a formated string representation of a category and its subcategories
 *
 * @param int $parent_id The parent ID of the category to build from
 * @param int $level Internal flag to note the depth of the category structure
 * @access protected
 * @return string
 */

    protected function _buildBranch($parent_id, $level = 0) {
      $result = $this->parent_group_start_string;

      if ( isset($this->_data[$parent_id]) ) {
        foreach ( $this->_data[$parent_id] as $category_id => $category ) {
          if ( $this->breadcrumb_usage === true ) {
            $category_link = $this->buildBreadcrumb($category_id);
          } else {
            $category_link = $category_id;
          }

          $result .= $this->child_start_string;

          if ( isset($this->_data[$category_id]) ) {
            $result .= $this->parent_start_string;
          }

          if ( $level === 0 ) {
            $result .= $this->root_start_string;
          }

          if ( ($this->follow_cpath === true) && in_array($category_id, $this->cpath_array) ) {
            $link_title = $this->cpath_start_string . $category['name'] . $this->cpath_end_string;
          } else {
            $link_title = $category['name'];
          }

          $result .= str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . osc_link_object(osc_href_link(FILENAME_DEFAULT, 'cPath=' . $category_link), $link_title);

          if ( $this->_show_total_products === true ) {
            $result .= $this->category_product_count_start_string . $category['count'] . $this->category_product_count_end_string;
          }

          if ( $level === 0 ) {
            $result .= $this->root_end_string;
          }

          if ( isset($this->_data[$category_id]) ) {
            $result .= $this->parent_end_string;
          }

          $result .= $this->child_end_string;

          if ( isset($this->_data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1)) ) {
            if ( $this->follow_cpath === true ) {
              if ( in_array($category_id, $this->cpath_array) ) {
                $result .= $this->_buildBranch($category_id, $level+1);
              }
            } else {
              $result .= $this->_buildBranch($category_id, $level+1);
            }
          }
        }
      }

      $result .= $this->parent_group_end_string;

      return $result;
    }

    function buildBranchArray($parent_id, $level = 0, $result = '') {
      if (empty($result)) {
        $result = array();
      }

      if (isset($this->_data[$parent_id])) {
        foreach ($this->_data[$parent_id] as $category_id => $category) {
          if ($this->breadcrumb_usage == true) {
            $category_link = $this->buildBreadcrumb($category_id);
          } else {
            $category_link = $category_id;
          }

          $result[] = array('id' => $category_link,
                            'title' => str_repeat($this->spacer_string, $this->spacer_multiplier * $level) . $category['name']);

          if (isset($this->_data[$category_id]) && (($this->max_level == '0') || ($this->max_level > $level+1))) {
            if ($this->follow_cpath === true) {
              if (in_array($category_id, $this->cpath_array)) {
                $result = $this->buildBranchArray($category_id, $level+1, $result);
              }
            } else {
              $result = $this->buildBranchArray($category_id, $level+1, $result);
            }
          }
        }
      }

      return $result;
    }

    function buildBreadcrumb($category_id, $level = 0) {
      $breadcrumb = '';

      foreach ($this->_data as $parent => $categories) {
        foreach ($categories as $id => $info) {
          if ($id == $category_id) {
            if ($level < 1) {
              $breadcrumb = $id;
            } else {
              $breadcrumb = $id . $this->breadcrumb_separator . $breadcrumb;
            }

            if ($parent != $this->root_category_id) {
              $breadcrumb = $this->buildBreadcrumb($parent, $level+1) . $breadcrumb;
            }
          }
        }
      }

      return $breadcrumb;
    }

/**
 * Return a formated string representation of the category structure relationship data
 *
 * @access public
 * @return string
 */
	/*
    public function getTree() {
      return $this->_buildBranch($this->root_category_id);
    }
	*/
    public function getTree(){
    	var_dump($this->_data);
    }
    
/**
 * Magic function; return a formated string representation of the category structure relationship data
 *
 * This is used when echoing the class object, eg:
 *
 * echo $osC_CategoryTree;
 *
 * @access public
 * @return string
 */

    public function __toString() {
      return $this->getTree();
    }

    function getArray($parent_id = '') {
      return $this->buildBranchArray((empty($parent_id) ? $this->root_category_id : $parent_id));
    }

    function exists($id) {
      foreach ($this->_data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return true;
          }
        }
      }

      return false;
    }

    function getChildren($category_id, &$array) {
      foreach ($this->_data as $parent => $categories) {
        if ($parent == $category_id) {
          foreach ($categories as $id => $info) {
            $array[] = $id;
            $this->getChildren($id, $array);
          }
        }
      }

      return $array;
    }

    function getData($id) {
      foreach ($this->_data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return array('id' => $id,
                         'name' => $info['name'],
                         'parent_id' => $parent,
                         'image' => $info['image'],
                         'count' => $info['count']
                        );
          }
        }
      }

      return false;
    }

/**
 * Calculate the number of products in each category
 *
 * @access protected
 */

    protected function _calculateProductTotals($filter_active = true) {
      global $osC_Database;

      $totals = array();

      $Qtotals = $osC_Database->query('select p2c.categories_id, count(*) as total from :table_products p, :table_products_to_categories p2c where p2c.products_id = p.products_id');

      if ( $filter_active === true ) {
        $Qtotals->appendQuery('and p.products_status = :products_status');
        $Qtotals->bindInt(':products_status', 1);
      }

      $Qtotals->appendQuery('group by p2c.categories_id');
      $Qtotals->bindTable(':table_products', TABLE_PRODUCTS);
      $Qtotals->bindTable(':table_products_to_categories', TABLE_PRODUCTS_TO_CATEGORIES);
      $Qtotals->execute();

      while ( $Qtotals->next() ) {
        $totals[$Qtotals->valueInt('categories_id')] = $Qtotals->valueInt('total');
      }

      foreach ( $this->_data as $parent => $categories ) {
        foreach ( $categories as $id => $info ) {
          if ( isset($totals[$id]) && ($totals[$id] > 0) ) {
            $this->_data[$parent][$id]['count'] = $totals[$id];

            $parent_category = $parent;

            while ( $parent_category != $this->root_category_id ) {
              foreach ( $this->_data as $parent_parent => $parent_categories ) {
                foreach ( $parent_categories as $parent_category_id => $parent_category_info ) {
                  if ( $parent_category_id == $parent_category ) {
                    $this->_data[$parent_parent][$parent_category_id]['count'] += $this->_data[$parent][$id]['count'];

                    $parent_category = $parent_parent;

                    break 2;
                  }
                }
              }
            }
          }
        }
      }
    }

    function getNumberOfProducts($id) {
      foreach ($this->_data as $parent => $categories) {
        foreach ($categories as $category_id => $info) {
          if ($id == $category_id) {
            return $info['count'];
          }
        }
      }

      return false;
    }

    function setRootCategoryID($root_category_id) {
      $this->root_category_id = $root_category_id;
    }

    function setMaximumLevel($max_level) {
      $this->max_level = $max_level;
    }

    function setRootString($root_start_string, $root_end_string) {
      $this->root_start_string = $root_start_string;
      $this->root_end_string = $root_end_string;
    }

    function setParentString($parent_start_string, $parent_end_string) {
      $this->parent_start_string = $parent_start_string;
      $this->parent_end_string = $parent_end_string;
    }

    function setParentGroupString($parent_group_start_string, $parent_group_end_string) {
      $this->parent_group_start_string = $parent_group_start_string;
      $this->parent_group_end_string = $parent_group_end_string;
    }

    function setChildString($child_start_string, $child_end_string) {
      $this->child_start_string = $child_start_string;
      $this->child_end_string = $child_end_string;
    }

    function setBreadcrumbSeparator($breadcrumb_separator) {
      $this->breadcrumb_separator = $breadcrumb_separator;
    }

    function setBreadcrumbUsage($breadcrumb_usage) {
      if ($breadcrumb_usage === true) {
        $this->breadcrumb_usage = true;
      } else {
        $this->breadcrumb_usage = false;
      }
    }

    function setSpacerString($spacer_string, $spacer_multiplier = 2) {
      $this->spacer_string = $spacer_string;
      $this->spacer_multiplier = $spacer_multiplier;
    }

    function setCategoryPath($cpath, $cpath_start_string = '', $cpath_end_string = '') {
      $this->follow_cpath = true;
      $this->cpath_array = explode($this->breadcrumb_separator, $cpath);
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setFollowCategoryPath($follow_cpath) {
      if ($follow_cpath === true) {
        $this->follow_cpath = true;
      } else {
        $this->follow_cpath = false;
      }
    }

    function setCategoryPathString($cpath_start_string, $cpath_end_string) {
      $this->cpath_start_string = $cpath_start_string;
      $this->cpath_end_string = $cpath_end_string;
    }

    function setShowCategoryProductCount($show_category_product_count) {
      if ($show_category_product_count === true) {
        $this->_show_total_products = true;
      } else {
        $this->_show_total_products = false;
      }
    }

    function setCategoryProductCountString($category_product_count_start_string, $category_product_count_end_string) {
      $this->category_product_count_start_string = $category_product_count_start_string;
      $this->category_product_count_end_string = $category_product_count_end_string;
    }
  }
?>
