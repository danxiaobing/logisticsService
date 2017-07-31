<?php
/**
 * 统一的RPC服务接口父类
 *
 * @version $Id$
 * @access  public
 */
class Rpc extends yaf_controller_abstract
{
	use TraitHprose;
	public function init() {
   $this->initRpc();
  }

} 