<?php
/**
 *
 * Copyright (C) 2007,2008  Arie Nugraha (dicarve@yahoo.com)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 */

/* Backup Management section */

// key to authenticate
define('INDEX_AUTH', '1');

// main system configuration
require '../../../sysconfig.inc.php';
// IP based access limitation
require_once LIB_DIR.'ip_based_access.inc.php';
do_checkIP('smc');
do_checkIP('smc-system');
// start the session
require SENAYAN_BASE_DIR.'admin/default/session.inc.php';
require SENAYAN_BASE_DIR.'admin/default/session_check.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/table/simbio_table.inc.php';
require SIMBIO_BASE_DIR.'simbio_GUI/paging/simbio_paging.inc.php';
require SIMBIO_BASE_DIR.'simbio_DB/datagrid/simbio_dbgrid.inc.php';

// create token in session
$_SESSION['token'] = utility::createRandomString(32);

// privileges checking
$can_read = utility::havePrivilege('system', 'r');
$can_write = utility::havePrivilege('system', 'w');

if (!($can_read AND $can_write)) {
    die('<div class="errorBox">'.__('You don\'t have enough privileges to view this section').'</div>');
}
/* search form */
?>
<fieldset class="menuBox">
<div class="menuBoxInner backupIcon">
    <?php echo strtoupper(__('Database Backup')); ?> - <input type="button" onclick="$('#createBackup').submit()" class="button notAJAX" value="<?php echo __('Start New Backup'); ?>" />
    <hr />
    <form name="search" action="<?php echo MODULES_WEB_ROOT_DIR; ?>system/backup_proc.php" id="search" method="get" style="display: inline;"><?php echo __('Search'); ?> :
    <input type="text" name="keywords" size="30" />
    <input type="submit" id="doSearch" value="<?php echo __('Search'); ?>" class="button" />
    </form>
    <form name="createBackup" id="createBackup" target="blindSubmit" action="<?php echo MODULES_WEB_ROOT_DIR; ?>system/backup_proc.php" method="post" style="display: inline; visibility: hidden;">
    <input type="hidden" name="start" value="true" />
    <input type="hidden" name="tkn" value="<?php echo $_SESSION['token']; ?>" />
    </form>
</div>
</fieldset>
<?php
/* BACKUP LOG LIST */
// table spec
$table_spec = 'backup_log AS bl LEFT JOIN user AS u ON bl.user_id=u.user_id';

// create datagrid
$datagrid = new simbio_datagrid();
$datagrid->setSQLColumn('u.realname AS \'Backup Executor\'', 'bl.backup_time AS \'Backup Time\'', 'bl.backup_file AS \'Backup File Location\'');
$datagrid->setSQLorder('backup_time DESC');

// is there any search
if (isset($_GET['keywords']) AND $_GET['keywords']) {
   $keywords = $dbs->escape_string($_GET['keywords']);
   $datagrid->setSQLCriteria("bl.backup_time LIKE '%$keywords%' OR bl.backup_file LIKE '%$keywords%'");
}

// set table and table header attributes
$datagrid->table_attr = 'align="center" id="dataList" cellpadding="5" cellspacing="0"';
$datagrid->table_header_attr = 'class="dataListHeader" style="font-weight: bold;"';
// set delete proccess URL
$datagrid->delete_URL = $_SERVER['PHP_SELF'];

// put the result into variables
$datagrid_result = $datagrid->createDataGrid($dbs, $table_spec, 20, false);

if (isset($_GET['keywords']) AND $_GET['keywords']) {
    $msg = str_replace('{result->num_rows}', $datagrid->num_rows, __('Found <strong>{result->num_rows}</strong> from your keywords')); //mfc
    echo '<div class="infoBox">'.$msg.' : "'.$_GET['keywords'].'"</div>';
}

echo $datagrid_result;
