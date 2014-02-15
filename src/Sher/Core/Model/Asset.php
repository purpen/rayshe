<?php
/**
 * 附件
 */
class Sher_Core_Model_Asset extends Sher_Core_Model_Base {

    protected $collection = "asset";

	const ASSET_DOAMIN = "sher";

    const STATE_FAIL = 0;		//处理失败
    const STATE_PENDING = 1;
    const STATE_OK = 2;

	# 照片
    const TYPE_PHOTO = 1;
    # 用户头像
    const TYPE_AVATAR = 4;

    protected $schema = array(
    	'parent_id'=>'',
    	'filepath'=>'',
		'filename'=>'',

    	'state'=>1,
		
        'size'=>0,
        'width'=>0,
        'height'=>0,
		'mime'=>null,
		
		// 缩略图path
        'thumb_big' => '',
		'thumb_middle' => '',
		'thumb_small' => '', 
		
		'asset_type' => self::TYPE_PHOTO,
    );

    protected $required_fields = array(
        'parent_id','filepath'
    );
    protected $int_fields = array('size','width','height','state');
	
    protected function extra_extend_model_row(&$row) {
    	$row['thumb_small_url'] = empty($row['thumb_small'])? Doggy_Config::$vars['app.url.default_thumb_small'] : Sher_Core_Helper_Url::thumb_view_url($row['thumb_small']);
        $row['thumb_middle_url'] = empty($row['thumb_middle'])?Doggy_Config::$vars['app.url.default_thumb_middle'] : Sher_Core_Helper_Url::thumb_view_url($row['thumb_middle']);
        $row['thumb_big_url'] = empty($row['thumb_big'])?Doggy_Config::$vars['app.url.default_thumb_big'] : Sher_Core_Helper_Url::thumb_view_url($row['thumb_big']);
        $row['file_url'] = empty($row['filepath'])? Doggy_Config::$vars['app.url.default_thumb_big'] : Sher_Core_Helper_Url::asset_view_url($row['filepath']);
    }

	/**
	 * 生成记录后，写文件进磁盘
	 */
	protected function after_save() {
		$file = $this->getFile();
	    $path = $this->filepath;
		Doggy_Log_Helper::debug("Path: $path, File: $file.");
		if(!is_null($file) && !is_null($path)){
			try{
				Sher_Core_Util_Asset::storeAsset(self::ASSET_DOAMIN, $path, $file);
			}catch(Sher_Core_Util_Exception $e){
				Doggy_Log_Helper::error('Save asset file failed. ' . $e->getMessage());
				throw new Cms_Core_Model_Exception('Save asset file failed. ' . $e->getMessage());
			}
		}
    }
	
	/**
	 * 删除附件记录及附件文件
	 */
	public function remove_and_file($query=array()) {
		if(empty($query)){
			return false;
		}
		$rows = $this->find($query);
		foreach($rows as $row){
			$file_path = $row['filepath'];
			Sher_Core_Util_Asset::deleteAsset(self::ASSET_DOAMIN, $file_path);
			$this->remove($row['_id']);
		}
		return true;
	}
	
	/**
	 * 删除附件记录
	 */
	public function delete_file($id) {
        $row = $this->find_by_id($id);
        if (empty($row)) {
            return null;
        }
        $file_path = $row['filepath'];
		Sher_Core_Util_Asset::deleteAsset(self::ASSET_DOAMIN, $file_path);
		
        return $this->remove($id);
    }
	
	/**
	 * 存储临时文件路径
	 */
	public function setFile($file){
		$this->file = $file;
	}
	
	public function getFile(){
		return $this->file;
	}
	
	
}
?>