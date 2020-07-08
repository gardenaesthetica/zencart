<?php
/**
 * categories_tabs.php module
 *
 * @package templateSystem
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: Scott C Wilson Sat Aug 25 07:25:23 2018 -0400 Modified in v1.5.6 $
 */
if (!defined('IS_ADMIN_FLAG')) {
  die('Illegal Access');
}
$order_by = " order by c.sort_order, cd.categories_name ";

$sql = "SELECT c.sort_order, c.categories_id, cd.categories_name
        FROM " . TABLE_CATEGORIES . " c
        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd USING (categories_id)
        WHERE c.parent_id= " . (int)TOPMOST_CATEGORY_PARENT_ID . "
        AND cd.language_id=" . (int)$_SESSION['languages_id'] . "
        AND c.categories_status=1 " .
        $order_by;
$categories_tab = $db->Execute($sql);

$links_list = array();
while (!$categories_tab->EOF) {

  // currently selected category
  if ((int)$cPath == $categories_tab->fields['categories_id']) {
    $new_style = 'category-top';
    $categories_tab_current = '<span class="category-subs-selected">' . $categories_tab->fields['categories_name'] . '</span>';
  } else {
    $new_style = 'category-top';
    $categories_tab_current = $categories_tab->fields['categories_name'];
  }

  // create link to top level category
  $links_list[] = '<a class="' . $new_style . '" href="' . zen_href_link(FILENAME_DEFAULT, 'cPath=' . (int)$categories_tab->fields['categories_id']) . '">' . $categories_tab_current . '</a> ';
  $categories_tab->MoveNext();
}
