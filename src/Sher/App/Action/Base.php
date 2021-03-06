<?php
/**
 * 基本代码
 */
class Sher_App_Action_Base extends DoggyX_Action_Base {
	protected $dt_view_tags= array('sher_app');
    /* 页面标示,用于前台css高亮,模板逻辑判断 */
	protected $page_tab = 'page_index';
	/* 默认模板 */
	protected $page_html = 'page/index.html';
    public $stash = array();

	/**
	 * 设置目标对象的css属性
	 */
	protected function set_target_css_state($target, $css_state='active') {
		$this->stash['css_'.$target] = $css_state;
	}
    /**
     * alias ajax_note
     */
	public function ajax_notification($msg,$is_error=false,$url=null){
		return $this->ajax_note($msg,$is_error,$url);
	}
	/**
	 * 显示ajax提示信息
	 */
    public function ajax_note($note,$is_error=false,$url=null) {
        if (!empty($url)) {
            $this->stash['redirect_url'] = $url;
        }
        $this->stash['note'] = $note;
        $this->stash['is_error'] = $is_error;
        return $this->to_taconite_page('ajax/note.html');
    }
    /**
     * alsia display_note_page
     */
    public function show_message_page($note, $url = null, $delay = 3000){
    	return $this->display_note_page($note,$url,$delay);
    }
    /**
     * 显示一个通用的信息跳转页面
     */
    public function display_note_page($note, $url = null, $delay = 3000) {
        if (!empty($url)) {
            $this->stash['redirect_url'] = $url;
        	$this->stash['delay'] = $delay;
		}
        $this->stash['note'] = $note;
        return $this->to_html_page('page/note_page.html');
    }

	/**
	 * 高亮tab,page标记,输出模板
	 */
	protected  function display_tab_page($list_tab,$page_html = NULL) {
		if (is_null($page_html)) {
			$page_html = $this->page_html;
		}
		$this->set_target_css_state($list_tab);
		return $this->to_html_page($page_html);
	}

	public function ok_remove($message,$selector) {
        $this->stash['message'] = $message;
        $this->stash['remove_selector'] = $selector;
        return $this->to_taconite_page('ajax/ok_remove.html');
    }
    

    protected function before_to_view() {
        parent::before_to_view();
        Sher_Core_Helper_View::setup_deploy_information($this->stash);
        Sher_Core_Helper_View::setup_site_menu($this->stash);
    }

}
?>