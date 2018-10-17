<?php
/**
 * API app 认证
 * @author yanghao
 * @date 17-03-29
 * @deprecated 以后启用
 */
class Api_Token {
	protected $appid;
	protected $appsecret;
	protected $appinfo;
	protected $signature;
	protected $timestamp;

	public function verify($appid, $signature, $timestamp, array $route) {
		return;
//		$this->appid = $appid;
//		$this->signature = $signature;
//		$this->timestamp = $timestamp;
//		$this->checkSign();
//		return;
	}
	

	/**
	 * 验证sign
	 * sign加密算法
	 */
	private function checkSign()
	{
		if($this->timestamp+30<time() or md5($this->appid.$this->appsecret.$this->timestamp) != $this->signature){
			throw new Exception("sign error",10003);
		}
	}

	
}