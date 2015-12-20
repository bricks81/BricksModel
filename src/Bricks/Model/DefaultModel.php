<?php

/**
 * Bricks Framework & Bricks CMS
 * http://bricks-cms.org
 *
 * The MIT License (MIT)
 * Copyright (c) 2015 bricks-cms.org
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Bricks\Model;

class DefaultModel 
implements ModelInterface {

	/**
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * @param array $data
	 */
	public function setData($key,$value=null){
		if(!is_array($key)){
			$key = array($key => $value);
		}
		foreach($key AS $k => $v){
			$this->__set($k,$v);
		}				
	}
	
	/**
	 * @param string key
	 * @return array 
	 */
	public function getData($key=null){
		$return = array();		
		if(null != $name && !isset($this->data[$key])){
			return null;
		}
		$data = $this->data;
		if(null != $name){
			return $this->__get($key);
		}
		foreach(array_keys($this->data) AS $key){
			$return[$key] = $this->__get($key);			
		}
		return $return;
	}
	
	/**
	 * @param string key
	 * @param mixed param[,param][,param]
	 */
	public function __set($key,$value){
		$method = 'set'.ucfirst($key);
		if(method_exists($this,$method)){
			$args = func_get_args();
			unset($args[0]);
			call_user_func_array(array($this,$method),$args);
		} else {
			$this->data[$key] = $value;
		}
	}
	
	/**
	 * @param string key [,param][,param]
	 * @return mixed
	 */
	public function __get($key){
		$method = 'set'.ucfirst($key);
		if(method_exists($this,$method)){
			$args = func_get_args();
			unset($args[0]);
			return call_user_func_array(array($this,$method),$args);
		} else {
			return isset($this->data[$key])?$this->data[$key]:null;
		}
	}
	
}