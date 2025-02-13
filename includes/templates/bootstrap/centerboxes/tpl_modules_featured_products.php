<?php
/**
 * Module Template
 * 
 * BOOTSTRAP v3.4.0
 *
 * @copyright Copyright 2003-2005 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: tpl_modules_featured_products.php 2935 2006-02-01 11:12:40Z birdbrain $
 */
$zc_show_featured = false;
require DIR_WS_MODULES . zen_get_module_directory('centerboxes/' . FILENAME_FEATURED_PRODUCTS_MODULE);
?>
<!-- bof: featured products  -->
<?php
if ($zc_show_featured === true) {
?>
<div class="card mb-3">
<?php
    if (!empty($title)) {
        echo $title;
    }
?>
    <div id="featuredCenterbox-card-body" class="card-body text-center">
<?php
    if (is_array($list_box_contents)) {
        for ($row = 0, $n = count($list_box_contents); $row < $n; $row++) {
            $params = '';
?>
        <div class="card-deck text-center">
<?php
            for ($col = 0, $j = count($list_box_contents[$row]); $col < $j; $col++) {
                $r_params = '';
                if (isset($list_box_contents[$row][$col]['params'])) {
                    $r_params .= ' ' . (string)$list_box_contents[$row][$col]['params'];
                }
                if (isset($list_box_contents[$row][$col]['text'])) {
                    echo '<div' . $r_params . '>' . $list_box_contents[$row][$col]['text'] .  '</div>';
                }
            }
?>
        </div>
<?php
        }
    }
?>
    </div>
</div>
<?php
}
?>
<!-- eof: featured products  -->
