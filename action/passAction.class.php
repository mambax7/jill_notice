<?php
use XoopsModules\Tadtools\CkEditor;
use XoopsModules\Tadtools\FormValidator;
use XoopsModules\Tadtools\Jeditable;
use XoopsModules\Tadtools\SweetAlert;
use XoopsModules\Tadtools\TadUpFiles;

class PassAction extends Action
{
    private $_notice = null;
    private $_cate   = null;
    public function __construct()
    {
        parent::__construct();
        $this->_notice = new NoticeModel();
        $this->_cate   = new NoticeCateModel();
        list($this->_F['cate_sn'],
            $this->_F['status']
        ) = $this->getArry(array(
            isset($_REQUEST['cate_sn']) ? Tool::setFormString($_REQUEST['cate_sn'], "int") : null,
            isset($_REQUEST['status']) ? Tool::setFormString($_REQUEST['status'], "int") : null,
        ));
    }

    //頁面載入
    public function main()
    {
        // 分類選單
        $_allCate = $this->_cate->findCateTitle(array("cate_sn in({$_SESSION['auditors']})"));

        $_cates = array();
        foreach ($_allCate['cates'] as $c => $title) {
            $_ischeck   = ($_allCate['status'][$c] == 1) ? _MD_JILLNOTICE_PASS : _MD_JILLNOTICE_CHECK;
            $_cates[$c] = $title . " : " . $_ischeck;
        }

        $this->_tpl->assign('allCate', $_cates);

        $statusArr = array(0 => _MD_JILLNOTICE_STATUS0, 1 => _MD_JILLNOTICE_STATUS1, 2 => _MD_JILLNOTICE_STATUS2);
        $this->_tpl->assign('statusArr', $statusArr);

        if (!isset($_POST['send'])) {
            $_cateValues         = array_keys($_allCate['cates']);
            $this->_F['cate_sn'] = $_cateValues[0];
            $this->_F['status']  = 0;
        }
        if (isset($_GET['sn'])) {
            $_OneNotice = $this->_notice->findOne();
            $this->_tpl->assign('now_op', "notice_show_one");
            $this->_tpl->assign("OneNotice", $_OneNotice[0]);

        } else {
            $_AllNotice = $this->_notice->notice_list(array("cate_sn='{$this->_F['cate_sn']}'", "status='{$this->_F['status']}'"));
            // die(var_dump($_AllNotice));
            $this->_tpl->assign('AllNotice', $_AllNotice);
            // 刪除
            $sweet_alert = new SweetAlert();

            $sweet_alert->render('delete_notice_func', "{$_SERVER['PHP_SELF']}?op=delete&sn=", "sn");

            // 點擊編輯
            $file      = "pass_save.ajax.php";
            $jeditable = new Jeditable();
            $jeditable->setSelectCol(".jq_select", $file, "{0:'" . _MD_JILLNOTICE_STATUS0 . "' , 1:'" . _MD_JILLNOTICE_STATUS1 . "',2:'" . _MD_JILLNOTICE_STATUS2 . "'}");
            $jeditable->render();

            $this->_tpl->assign('def_cate_sn', $this->_F['cate_sn']);
            $this->_tpl->assign('def_status', $this->_F['status']);
            $this->_tpl->assign('now_op', "pass_list");
            $this->_tpl->assign('can_notice', 1);
        }
        $this->_tpl->assign('action', $_SERVER['PHP_SELF']);
    }

    // 刪除
    public function delete()
    {
        if (isset($_GET['sn'])) {
            $_row = $this->_notice->notice_delete();
        }
        header("location: {$_SERVER['PHP_SELF']}");
    }

    // 新增、編輯
    public function notice_form()
    {
        // 上傳
        $TadUpFiles = new TadUpFiles("jill_notice");

        if (isset($_POST['send'])) {
            //XOOPS表單安全檢查
            if (!$GLOBALS['xoopsSecurity']->check()) {
                $error = implode("<br />", $GLOBALS['xoopsSecurity']->getErrors());
                redirect_header($_SERVER['PHP_SELF'], 3, $error);
            }
            if (isset($_POST['next_op'])) {
                if ($_POST['next_op'] == "update") {
                    $_sn      = $this->_notice->notice_update();
                    $_message = empty($_sn) ? "修改失敗" : "修改成功!";
                }
                if ($_POST['next_op'] == "add") {
                    $_sn      = $this->_notice->notice_add();
                    $_message = empty($_sn) ? "新增失敗" : "新增成功!";
                }
                if (!empty($_sn)) {
                    if ($_POST['type'] == "img" || $_POST['type'] == "text" || $_POST['type'] == "url") {
                        $TadUpFiles->set_col("sn", $_sn);
                        // 單檔判斷
                        if ($_FILES['up_sn']['name'][0] != "") {
                            $TadUpFiles->del_files();
                        }
                        $TadUpFiles->upload_file('up_sn', '1280', '140', '', '', true, false);
                    }
                }

            }

            redirect_header($_SERVER['PHP_SELF'], 3, $_message);
            exit();
        }
        if (isset($_GET['sn'])) {
            $_OneNotice = $this->_notice->findOne();
            if ($_OneNotice[0]['type'] == "url") {
                $_OneNotice[0]['content'] = strip_tags($_OneNotice[0]['content']);
            }
            // die(var_dump($_OneNotice));
            $TadUpFiles->set_col("sn", $_OneNotice[0]['sn']);
            $this->_tpl->assign('next_op', "update");
            $this->_tpl->assign("OneNotice", $_OneNotice[0]);
            $_type = $_OneNotice[0]['type'];

        } else {
            // 給定預設值
            $this->_tpl->assign('typeArr', $this->_notice->setType());
            $_type = !isset($_GET['type']) ? 'text' : Tool::setFormString($_GET['type']);
            $this->_tpl->assign('next_op', "add");
        }
        $this->_tpl->assign('def_type', $_type);
        if ($_type == "ckeditor") {
            $_content = (empty($_OneNotice)) ? "" : $_OneNotice[0]['content'];
            $ck       = new CkEditor("jill_notice", "content", $_content);
            $ck->setWidth(400);
            $this->_tpl->assign('editor_content', $ck->render());
        }
        // die(var_dump($TadUpFiles));
        $up_sn_form = $TadUpFiles->upform(false, "up_sn", "1");
        $this->_tpl->assign('up_sn_form', $up_sn_form);
        // 分類選單
        $_allCate = $this->_cate->findCateTitle();
        $this->_tpl->assign('allCate', $_allCate['cates']);

        //套用formValidator驗證機制
        $formValidator      = new FormValidator("#myForm", true);
        $formValidator_code = $formValidator->render();
        //加入Token安全機制
        include_once XOOPS_ROOT_PATH . "/class/xoopsformloader.php";
        $token      = new \XoopsFormHiddenToken();
        $token_form = $token->render();
        $this->_tpl->assign("token_form", $token_form);
        $this->_tpl->assign('now_op', "notice_form");
        $this->_tpl->assign('action', $_SERVER['PHP_SELF']);

    }

}
