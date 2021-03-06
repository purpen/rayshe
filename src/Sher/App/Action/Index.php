<?php
/**
 * 首页,列表页面
 */
class Sher_App_Action_Index extends Sher_App_Action_Authorize {
	public $stash = array(
		'page'=>1,
		'sort'=>'latest',
		'rank'=>'day',
		'q'=>'',
		'ref'=>'',
		// 邀请码
		'l'=>'',
	);
	
	// 一个月时间
	protected $month =  2592000;
	
	protected $page_tab = 'page_index';
	protected $page_html = 'page/index.html';
	
	protected $exclude_method_list = array('execute','home','verify_code','help','about','contact');

	/**
	 * 入口
	 */
	public function execute(){
		if($this->visitor->id){
			$user_home_url = Sher_Core_Helper_Url::user_home_url($this->visitor->id); 
			return $this->to_redirect($user_home_url);
		}
		return $this->home();
	}
    /**
     * 首页
     * @return string
     */
    public function home() {
		$this->gen_login_token();
		
        return $this->to_html_page('page/home.html');
    }

	/**
	 * 她单身列表
	 */
	public function she_list() {
		$this->set_target_css_state('she_list');
		return $this->_display_user_list(2);
	}
	
	/**
	 * 他单身列表
	 */
	public function he_list() {
		$this->set_target_css_state('he_list');
		return $this->_display_user_list(1);
	}
	
	/**
	 * 帮助中心
	 */
	public function help() {
		return $this->to_html_page('page/help.html');
	}
	
	/**
	 * 关于我们
	 */
	public function about() {
		return $this->to_html_page('page/about.html');
	}
	
	/**
	 * 联系我们
	 */
	public function contact() {
		return $this->to_html_page('page/contact.html');
	}
	
	/**
	 * 显示单身列表
	 */
	protected function _display_user_list($sex=1){
		// 仅允许搜索单身用户
		$this->stash['marital'] = Sher_Core_Model_User::MARR_SINGLE;
		$this->stash['sex'] = $sex;
		$this->stash['only_ok'] = 1;
		
		return $this->to_html_page('page/user_list.html');
	}

	/**
	 * 发送手机验证码
	 */
	public function verify_code() {
		$phone = $this->stash['phone'];
		$code = Sher_Core_Helper_Auth::generate_code();
		
		$verify = new Sher_Core_Model_Verify();
		$ok = $verify->create(array('phone'=>$phone,'code'=>$code));
		if($ok){
			// 开始发送
			Sher_Core_Helper_Util::send_register_mms($phone, $code);
		}
		
		return $this->to_json(200,'正在发送');
	}
	
	/**
	 * 生成临时token
	 */
	protected function gen_login_token() {
        $service = DoggyX_Session_Service::instance();
        $token = Sher_Core_Helper_Auth::generate_random_password();
        $service->session->login_token = $token;
        $this->stash['login_token'] = $token;
    }

	
	
}
?>