<?php
/**
 * Jill Notice module
 * @copyright  The XOOPS Project http://sourceforge.net/projects/xoops/
 * @license    http://www.fsf.org/copyleft/gpl.html GNU public license
 * @package    Jill Notice
 * @since      2.5
 * @author     tnjaile
 * @version    $Id $
 **/

/*-----------引入檔案區--------------*/
include_once $_SERVER["DOCUMENT_ROOT"] . "/modules/jill_notice/configs/run.inc.php";
$_SESSION['notice_adm'] = true;
$_obj                   = new AuditorsAction();
$_obj->run();
/*-----------秀出結果區--------------*/
$xoTheme->addStylesheet('/modules/tadtools/css/font-awesome/css/font-awesome.css');
$xoTheme->addStylesheet(XOOPS_URL . '/modules/tadtools/css/xoops_adm3.css');
include_once 'footer.php';
