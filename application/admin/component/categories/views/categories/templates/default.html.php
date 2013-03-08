<?
/**
 * @package     Nooku_Server
 * @subpackage  Categories
 * @copyright   Copyright (C) 2011 - 2012 Timble CVBA and Contributors. (http://www.timble.net).
 * @license     GNU GPLv3 <http://www.gnu.org/licenses/gpl.html>
 * @link        http://www.nooku.org
 */
?>

<!--
<script src="media://koowa/js/koowa.js" />
<style src="media://koowa/css/koowa.css" />
-->
<?= @helper('behavior.sortable') ?>

<?= @template('com://admin/base.view.grid.toolbar.html'); ?>

<? if($state->table == 'articles') : ?>
<ktml:module position="sidebar">
    <?= @template('default_sidebar.html'); ?>
</ktml:module>
<? endif; ?>

<ktml:module  position="inspector">
    <?= @template('com://admin/activities.view.activities.simple.html', array('package' => $state->table, 'name' => 'category')); ?>
</ktml:module>

<form action="" method="get" class="-koowa-grid">
    <input type="hidden" name="section" value="<?= $state->section;?>" />
    <input type="hidden" name="type" value="<?= $state->type;?>" />

    <?= @template('default_scopebar.html'); ?>
    <table>
        <thead>
            <tr>
                <? if($state->sort == 'ordering' && $state->direction == 'asc') : ?>
                <th class="handle"></th>
                <? endif ?>
                <th width="1">
                    <?= @helper('grid.checkall'); ?>
                </th>
                <th>
                    <?= @helper('grid.sort',  array('column' => 'title')); ?>
                </th>
                <th width="1">
                    <?= @helper('grid.sort',  array('column' => 'published')); ?>
                </th>
                <th width="1">
                    <?= @helper('grid.sort',  array( 'title' => 'Articles', 'column' => 'count')); ?>
                </th>
            </tr>
        </thead>

        <tfoot>
            <tr>
                <td colspan="13">
                    <?= @helper('paginator.pagination', array('total' => $total)); ?>
                </td>
            </tr>
        </tfoot>

        <tbody<? if($state->sort == 'ordering' && $state->direction == 'asc') : ?> class="sortable"<? endif ?>>
            <? foreach( $categories as $category) :  ?>
                <tr>
                    <? if($state->sort == 'ordering' && $state->direction == 'asc') : ?>
                    <td class="handle">
                        <span class="text-small data-order"><?= $category->ordering ?></span>
                    </td>
                    <? endif ?>
                    <td align="center">
                        <?= @helper( 'grid.checkbox' , array('row' => $category)); ?>
                    </td>
                    <td>
                        <a href="<?= @route( 'view=category&id='.$category->id ); ?>">
                            <?= @escape($category->title); ?>
                         </a>
                         <? if($category->access) : ?>
                             <span class="label label-important"><?= @text('Registered') ?></span>
                         <? endif; ?>
                    </td>
                    <td align="center">
                        <?= @helper('grid.enable', array('row' => $category, 'field' => 'published')) ?>
                    </td>
                    <td align="center">
                        <?= $category->count; ?>
                    </td>
            	</tr>
            <? endforeach; ?>
       </tbody>
    </table>
</form>
