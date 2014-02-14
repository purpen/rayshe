<?php
/**
 * 后台管理功能
 */
class Sher_App_Action_Admin extends Sher_App_Action_Authorize {
	public $stash = array(
		'category_id'=>0,
		'page'=>1,
		'sort'=>'latest',
		'rank'=>'day',
		'q'=>'',
	);
	
	// 一个月时间
	protected $month =  2592000;
	
	protected $page_tab = 'page_index';
	protected $page_html = 'page/index.html';
	
	protected $exclude_method_list = array();
	
	protected $admin_method_list = '*';
	/**
	 * 入口
	 */
	public function execute(){
		return $this->dashboard();
	}
    /**
     * 首页
     * @return string
     */
    public function dashboard() {
    	$this->set_target_css_state('page_dashboard');
        return $this->to_html_page('admin/dashboard.html');
    }

	/**
     * 用户列表
     * @return string
     */
    public function user_list() {
    	$this->set_target_css_state('page_user');
        return $this->to_html_page('admin/user_list.html');
    }

	/**
	 * 邀请码列表
	 */
	public function invitation() {
		$this->set_target_css_state('page_invitation');
		return $this->to_html_page('admin/invitation.html');
	}
	
	/**
	 * 邀请码 增发
	 */
	public function new_invitation() {
		$this->set_target_css_state('page_invitation');
		return $this->to_html_page('admin/new_invitation.html');
	}
	/**
	 * 生成邀请码
	 */
	public function gen_invitation() {
		$invite = new Sher_Core_Model_Invitation();
		$user_id = $this->stash['user_id'];
		$quantity = $this->stash['quantity'];
		
		$result = $invite->generate_for_user($user_id, $quantity);
		return $this->invitation();
	}
	/** 
	 * 爱情短语
	 */
	public function cake() {
		$this->set_target_css_state('page_cake');
		return $this->to_html_page('admin/cake_list.html');
	}
	
	/**
	 * 新增短语
	 */
	public function new_cake() {
		$cake = new Sher_Core_Model_Cake();
		if(!empty($this->stash['id'])) {
			$this->stash['cake'] = $cake->find_by_id($this->stash['id']);
		}
		$this->set_target_css_state('page_cake');
		return $this->to_html_page('admin/new_cake.html');
	}
	/**
	 * 保存短语
	 */
	public function save_cake() {
		$row = array();
		$row['user_id'] = (int)$this->visitor->id;
		$row['content'] = $this->stash['content'];
		// 验证数据
		if(empty($row['content'])){
			return $this->ajax_note('获取数据错误,请重新提交', true);
		}
		
		$cake = new Sher_Core_Model_Cake();
		if(empty($this->stash['_id'])){
			$ok = $cake->apply_and_save($row);
		}else{
			$row['_id'] = $this->stash['_id'];
			$ok = $cake->apply_and_update($row);
		}
		
		if(!$ok){
			return $this->ajax_note('数据保存失败,请重新提交', true);
		}
		
		return $this->ajax_notification('短语保存成功.');
	}
	
	/**
	 * 删除短语
	 */
	public function delete_cake() {
		$id = $this->stash['id'];
		if(!empty($id)){
			$cake = new Sher_Core_Model_Cake();
			$cake->remove($id);
		}
		$this->stash['id'] = $id;
		return $this->to_taconite_page('admin/del_ok.html');
	}
}
?>