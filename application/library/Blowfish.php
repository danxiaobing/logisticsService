<?php

/**
 * php blowfish 算法
 * Class blowfish
 */
class Blowfish{
  private $key = 'chinayieapp';
  private $iv = '00000000';
 /**
  * blowfish + cbc模式 + pkcs5补码 加密
  * 修改注释：现在采取ECB模式
  * @param string $str 需要加密的数据
  * @return string 加密后base64加密的数据
  */
 #public function blowfish_cbc_pkcs5_encrypt($str)
 public function encrypt($str)
 {
  $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_ECB, '');

  //pkcs5补码
 // $size = mcrypt_get_block_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
 // $str = $this->pkcs5_pad($str, $size);

  if (mcrypt_generic_init($cipher, $this->key, $this->iv) != -1)
  {
   $cipherText = mcrypt_generic($cipher, $str);
   mcrypt_generic_deinit($cipher);

   return strtoupper(bin2hex($cipherText));
  }

  mcrypt_module_close($cipher);
 }

 /**
  * blowfish + cbc模式 + pkcs5 解密 去补码
  * 修改注释：现在采取ECB模式
  * @param string $str 加密的数据
  * @return string 解密的数据
  */
 #public function blowfish_cbc_pkcs5_decrypt($str)
 public function decrypt ($str)
 {
  $cipher = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_ECB, '');

  if (mcrypt_generic_init($cipher, $this->key, $this->iv) != -1)
  {
   $cipherText = mdecrypt_generic($cipher,  hex2bin(strtolower($str)));
   mcrypt_generic_deinit($cipher);

   return $cipherText;
  }

  mcrypt_module_close($cipher);
 }


}