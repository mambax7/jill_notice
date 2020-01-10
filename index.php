<?php
/**
 * @copyright  The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license    http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package
 * @since
 * @author
 * @version    $Id $
 * die(print_r(get_declared_classes()));--檢查引入物件
 **/
/*-----------引入檔案區--------------*/
use XoopsModules\Tadtools\Utility;

include_once "configs/run.inc.php";

$_obj = new NoticeAction();

$_obj->run();

// /*-----------秀出結果區--------------*/
// var_dump($interface_menu);
$xoopsTpl->assign('toolbar', Utility::toolbar_bootstrap($interface_menu));
include_once XOOPS_ROOT_PATH . '/footer.php';
