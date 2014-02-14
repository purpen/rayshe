<?php
class Sher_Core_Helper_Util {
	/**
	 * 发送注册验证码
	 */
	public static function send_register_mms($phone,$code) {
		$message = "验证码：${code}，切勿泄露给他人，如非本人操作，建议及时修改账户密码。【悦伊】";
		return self::send_mms($phone, $message);
	}
	
	/**
	 * 发送短信息
	 */
	public static function send_mms($phone, $message) {
		if(empty($phone) || empty($message)) {
			return false;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://sms-api.luosimao.com/v1/send.json");

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
		curl_setopt($ch, CURLOPT_HEADER, FALSE);

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE); 
		curl_setopt($ch, CURLOPT_SSLVERSION , 3);

		curl_setopt($ch, CURLOPT_HTTPAUTH , CURLAUTH_BASIC);
		curl_setopt($ch, CURLOPT_USERPWD, 'api:key-ec086f86baeaeb6442ccf6a66b3fdb0c');


		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('mobile' => $phone,'message' => $message));

		$res = curl_exec( $ch );
		curl_close( $ch );
		
		// $res  = curl_error( $ch );
		// {"error":0,"msg":"ok"}
		// var_dump($res);
		// if (!$res['error']){
		//	return false;
		// }
		
		return true;
	}
	/**
	 * 验证url是否合法
	 */
	public static function is_url($str){
		return preg_match("/^http:\/\/[A-Za-z0-9-%_]+\.[A-Za-z0-9-%_]+[\/=\?%\-&_~`@[\]\':+!]*([^<>\"])*$/", $str);
	}
	public static function is_gif($str){
		return (strtolower(substr($str,-4)) == ".gif"); 
	}
	
	/**
	 * 计算年龄段
	 */
	public static function calc_age($year,$mouth,$day){
		$now = date_create("now");
		$birth = date_create("$year-$mouth-$day");  // $birthdate from DB
		if($now < $birth){
		    $age = '你还没出生呢';
		} else {
		    $interval = date_diff($now, $birth);
		    $age = $interval->format('%y');
		}
		return $age;
	}
	/**
	 * 计算年龄所属的间隔
	 */
	public static function belong_age_interval($age){
		$age_text = '';
		if($age < 18){
			$age_text = '小于18岁';
		}elseif($age >= 18 && $age < 25){
			$age_text = '18-25岁';
		}elseif($age >= 25 && $age < 30){
			$age_text = '25-30岁';
		}elseif($age >= 30 && $age < 35){
			$age_text = '30-35岁';
		}else{
			$age_text = '大于35岁';
		}
		
		return $age_text;
	}

	/**
	 * form表单验证元素是否为空
	 * @example empty_scheme(array('name','title'=>'has_title') ,array('name','title' ) )  return false
	 * @example empty_scheme(array('name'=>'has_name','title'=>'has_title') ,array('name','title' ) )  return true
	 * @param array	$data	form表单
	 * @param array	$scheme	基本元素
	 * @return true or false 为空返回1,通过返回0
	 */
	public static function empty_scheme($data = array(), $scheme = array()){
		foreach ($scheme as  $name) {
			if (! isset($data[$name]) || empty($data[$name])) {
				return true;
			}
		}
		return false;
	}
    /**
     * 标签验证过滤器
     */
	public static function filtor_tag($tag_s){
		return array_values(array_unique(preg_split('/[,，\s]+/u',$tag_s)));
	}
	/**
	 * 生产随机数
	 */
	public static function gen_random(){
		srand((double)microtime()*10000000);
		return rand();
	}
	/**
	 * 生成加密key
	 */
	public static function gen_secrect_key($user1,$user2){
		if($user1 > $user2){
			$key = md5($user2.'_'.$user1);
		}else{
			$key = md5($user1.'_'.$user2);
		}
		return $key;
	}
	/**
	 * 两个数组合并
	 */
	public static function ary_merge($a1,$a2){
		if(is_array($a1) && is_array($a2)){
			for($i=0;$i<count($a2);$i++){
				array_push($a1,$a2[$i]);
			}
			return array_unique($a1);
		}
		return array();
	}
	/**
	 * 截取字符
	 */
	public static function utf_substr($str,$len){
		for($i=0;$i<$len;$i++){
			$temp_str=substr($str,0,1);
			if(ord($temp_str) > 127){
				$i++;
				if($i<$len){
					$new_str[]=substr($str,0,3);
					$str=substr($str,3);
				}
			} else {
				$new_str[]=substr($str,0,1);
				$str=substr($str,1);
			}
		}
		return join($new_str);
	}
}
?>