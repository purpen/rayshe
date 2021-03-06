<?php
/**
 * 用户验证等
 */
class Sher_App_Action_Auth extends Sher_App_Action_Base {
	public $stash = array(
			'email' => '',
			'account' => '',
			'nickname' => '',
			'invite_code' => null,
	);
	
	/**
	 * 登录页面
	 *
	 * @return void
	 */
	public function login(){
       $this->gen_login_token();
		$this->set_target_css_state('login_box','item_active');
		return $this->to_html_page('page/auth_login.html');
	}
	/**
	 * 注册页面
	 */
	public function register(){
	    $this->gen_login_token();
		$this->set_target_css_state('register_box','item_active');
		return $this->to_html_page('page/auth_login.html');
	}

	/**
	 * 忘记密码页面
	 */
	public function forget(){
		return $this->to_html_page('page/auth_forget.html');
	}
	/**
	 * 退出
	 */
	public function logout(){
        $service = DoggyX_Session_Service::instance();
        $service->revoke_auth_cookie();
        $service->stop_visitor_session();
		return $this->display_note_page('您已成功的退出登录,稍候将跳转到主页.', Doggy_Config::$vars['app.url.index']);
	}

	/**
	 * 执行用户登录流程
	 */
	public function do_login(){
       if (empty($this->stash['account']) || empty($this->stash['password']) ||empty($this->stash['t'])) {
            return $this->ajax_note('数据错误,请重新登录',true,Doggy_Config::$vars['app.url.login']);
        }
        $service = DoggyX_Session_Service::instance();
        $s_t = $service->session->login_token;
        if (empty($s_t) || $s_t != $this->stash['t']) {
            return $this->ajax_note('页面已经超时,您需要重新刷新后登录',true,Doggy_Config::$vars['app.url.login']);
        }
		$user = new Sher_Core_Model_User();
		$result = $user->first(array('account'=>$this->stash['account']));
        if (empty($result)) {
            return $this->ajax_note('帐号不存在!');
        }
        if ($result['password'] != sha1($this->stash['password'])) {
            return $this->ajax_note('登录账号和密码不匹配',true);
        }
        $user_id = (int) $result['_id'];
		$nickname = $result['nickname'];
        $user_state = $result['state'];
        
        if ($user_state == Sher_Core_Model_User::STATE_BLOCKED) {
            return $this->ajax_note('此帐号涉嫌违规已经被禁用!',true,'/');
        }
		
        Sher_Core_Helper_Auth::create_user_session($user_id);
		
        $redirect_url = isset($_COOKIE['auth_return_url']) ? $_COOKIE['auth_return_url'] : Sher_Core_Helper_Url::user_home_url($user_id);
        if (empty($redirect_url)) {
            $redirect_url = '/';
        }
		
        $this->clear_auth_return_url();
		
		return $this->ajax_note('欢迎,'.$nickname.' 回来.', false, $redirect_url);
	}
    
	/**
	 * 创建Passport和灵感库帐号,完成提交注册信息
	 */
	public function do_register(){
	    if (empty($this->stash['account']) || empty($this->stash['password']) || empty($this->stash['invite_code']) || empty($this->stash['verify_code'])) {
            return $this->ajax_note('数据错误',true,Doggy_Config::$vars['app.url.register']);
        }
        $service = DoggyX_Session_Service::instance();
        $s_t = $service->session->login_token;
        if (empty($s_t) || $s_t != $this->stash['t']) {
            return $this->ajax_note('页面已经超时,您需要重新刷新后登录',true,Doggy_Config::$vars['app.url.register']);
        }
		// 验证密码是否一致
		$password_confirm = $this->stash['password_confirm'];
		if(empty($password_confirm) || $this->stash['password_confirm'] != $this->stash['password']){
			return $this->ajax_note('两次输入密码不一致！',true,Doggy_Config::$vars['app.url.register']);
		}
		
		// 验证验证码是否有效
		if(!empty($this->stash['verify_code'])){
			$verify = new Sher_Core_Model_Verify();
			$row = $verify->first(array('phone'=>$this->stash['account'],'code'=>$this->stash['verify_code']));
			if(empty($row)){
				return $this->ajax_note('验证码有误，请重新获取！',true,Doggy_Config::$vars['app.url.register']);
			}else{
				// 删除验证码
				$verify->remove($row['_id']);
			}
		}
		
		// 验证邀请码
		if (!$this->_invitation_is_ok()) {
			return $this->ajax_note('邀请码不存在或已被使用！',true);
		}
		
        try {
			$user = new Sher_Core_Model_User();
            $ok = $user->create(array(
                'account' => $this->stash['account'],
                'state' => Sher_Core_Model_User::STATE_OK,
                'phone' => $this->stash['account'],
				'invitation' => $this->stash['invite_code'],
                'password' => sha1($this->stash['password']),
            ));
			
			if($ok){
				$user_id = $user->id;

				// 设置邀请码已使用
				$this->mark_invitation_used($user_id);

				Sher_Core_Helper_Auth::create_user_session($user_id);
			}
			
        } catch (Sher_Core_Model_Exception $e) {
            Doggy_Log_Helper::error('Failed to create_passport:'.$e->getMessage());
            return $this->ajax_note("注册失败:".$e->getMessage(), true);
        }
		
		$user_profile_url = Doggy_Config::$vars['app.url.my'].'/profile';
		
		return $this->ajax_note("注册成功，欢迎你加入悦伊！", false, $user_profile_url);
	}

	/**
	 * 验证邀请码
	 */
	public function ajax_check_invite_code(){
		/* 验证邀请码 */
		if (empty($this->stash['invite_code'])) {
            return $this->to_raw_json('请填写邀请码');
		}
		if (!$this->_invitation_is_ok()) {
            return $this->to_raw_json('邀请码不存在或已被使用.');
		}
		return $this->to_raw_json(true);
	}
	
	protected function _invitation_is_ok($check_used = true) {
	    $invite_code = $this->stash['invite_code'];
        $invitation = new Sher_Core_Model_Invitation();
        $row = $invitation->find_by_id($invite_code);
        if (empty($row)) {
            return false;
        }
        if ($check_used && $row['used']) {
            $service = DoggyX_Session_Service::instance();
            $service->session->invite_code = null;
            return false;
        }
        return true;
	}
	
    protected function gen_login_token() {
        $service = DoggyX_Session_Service::instance();
        $token = Sher_Core_Helper_Auth::generate_random_password();
        $service->session->login_token = $token;
        $this->stash['login_token'] = $token;
    }
	
    protected function mark_invitation_used($user_id) {
        $invitation = new Sher_Core_Model_Invitation();
        $invitation->mark_used($this->stash['invite_code'],$user_id);
    }
	
    protected function clear_auth_return_url() {
        @setcookie('auth_return_url','',time()-259200,'/');
    }
}
?>