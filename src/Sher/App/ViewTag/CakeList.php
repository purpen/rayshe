<?php
/**
 * 短语列表标签
 */
class Sher_App_ViewTag_CakeList extends Doggy_Dt_Tag {
    protected $argstring;
	
    public function __construct($argstring, $parser, $pos = 0) {
        $this->argstring = $argstring;
    }

    /**
     * 列表的条件保持与索引顺序一致(non-PHPdoc)
     * @see Doggy/Dt/Doggy_Dt_Node#render()
     */
    public function render($context, $stream) {
        $page = 1;
        $size = 10;
        $user_id = 0;
        $var = 'list';
        $include_pager = 0;
        $pager_var = 'pager';
        $sort = 'latest';
		$random = false;

        extract($this->resolve_args($context,$this->argstring,EXTR_IF_EXISTS));

        $page = (int) $page;
        $page || $page = 1;
        $size = (int)$size;
		
        $query = array();
		
		// 随机获取一条
		if ($random) {
			srand((double)microtime()*1000000);
			$rand = rand(0,99999999);
			
			Doggy_Log_Helper::warn("Get cake random ${rand}.");
			
			$cake = new Sher_Core_Model_Cake();
			$result = $cake->first(array(
				'random' => array('$lt'=>$rand)
			),array('created_on' => -1));
			if (empty($result)) {
				$result = $cake->first(array(
					'random' => array('$gte'=>$rand)
				),array('created_on' => 1));
			}
			
			$context->set($var, $result);
			
			return;
		}
		
        if ($user_id) {
            if(is_array($user_id)){
                $query['user_id'] = array('$in'=>$user_id);
            }else{
                $query['user_id'] = (int) $user_id;
            }
        }
		
        $service = Sher_Core_Service_Cake::instance();
        $options['page'] = $page;
        $options['size'] = $size;
		
        $result = $service->get_cake_list($query,$options);
        
        $context->set($var,$result);
        if ($include_pager) {
            $context->set($pager_var,$result['pager']);
        }
        
    }
}
?>