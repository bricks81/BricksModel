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

use Bricks\Config\ConfigAwareInterface;
use Bricks\Config\ConfigInterface;
use Bricks\Loader\LoaderAwareInterface;
use Bricks\Loader\LoaderInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\AdapterAwareInterface;
use Zend\Db\RowGateway\RowGatewayInterface;

class Model implements LoaderAwareInterface, ConfigAwareInterface, AdapterAwareInterface {

    /**
     * @var LoaderInterface
     */
    protected $loader;

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var array
     */
    protected $tables = [];

    /**
     * @var array
     */
    protected $tableNames = [];

    /**
     * @var array
     */
    protected $rowClasses = [];

    /**
     * @var array
     */
    protected $rowsetClasses = [];

    /**
     * @param LoaderInterface $loader
     */
    public function setLoader(LoaderInterface $loader){
        $this->loader = $loader;
    }

    /**
     * @return LoaderInterface
     */
    public function getLoader(){
        return $this->loader;
    }

    /**
     * @param ConfigInterface $config
     */
    public function setConfig(ConfigInterface $config){
        $this->config = $config;
    }

    /**
     * @return ConfigInterface
     */
    public function getConfig(){
        return $this->config;
    }

    /**
     * @param Adapter $adapter
     */
    public function setDbAdapter(Adapter $adapter){
        $this->adapter = $adapter;
    }

    /**
     * @return Adapter
     */
    public function getDbAdapter(){
        return $this->adapter;
    }

    /**
     * @param string $schema
     * @param string $name
     * @param string $namespace
     * @return string
     */
    public function solveRowClass($schema,$name,$namespace=null){
        $namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
        if(!isset($this->rowClasses[$namespace][$schema][$name])) {
            $tableName = $this->solveTableName($schema,$name,$namespace);
            $config = $this->getConfig()->get('bricks-model.databases.'.$schema.'.tables.'.$tableName);
            $class = 'Bricks\Model\Model\DefaultModel';
            if(isset($config['rowClass'])){
                $class = $config['rowClass'];
            }
            $this->rowClasses[$namespace][$schema][$name] = $class;
        }
        return $this->rowClasses[$namespace][$schema][$name];
    }

    /**
     * @param string $schema
     * @param string $name
     * @param string $namespace
     * @return string
     */
    public function solveRowsetClass($schema,$name,$namespace=null){
        $namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
        if(!isset($this->rowsetClasses[$namespace][$schema][$name])) {
            $tableName = $this->solveTableName($schema,$name,$namespace);
            $config = $this->getConfig()->get('bricks-model.databases.'.$schema.'.tables.'.$tableName);
            $class = 'Bricks\Model\Collection';
            if(isset($config['rowsetClass'])){
                $class = $config['rowsetClass'];
            }
            $this->rowsetClasses[$namespace][$schema][$name] = $class;
        }
        return $this->rowsetClasses[$namespace][$schema][$name];
    }

    /**
     * @param string $schema
     * @param string $name
     * @param string $namespace
     * @return mixed
     */
    public function solveTableName($schema,$name,$namespace=null){
        $namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
        if(!isset($this->tableNames[$namespace][$schema][$name])) {
            $dbConfig = $this->getConfig()->get('bricks-model.databases.'.$schema, $namespace);
            $table = preg_split('[A-Z]',$name);
            $prefix = '';
            $_table = $this->getConfig()->get('bricks-model.databases.'.$schema.'.tables.'.strtolower(implode('_',$table)));
            if($_table){
                $table = isset($_table['table'])?$_table['table']:$table;
                $prefix = isset($_table['prefix'])?$_table['prefix']:$prefix;
            }
            $this->tableNames[$namespace][$schema][$name] = $prefix.$table;
        }
        return $this->tableNames[$namespace][$schema][$name];
    }

    /**
     * @param string $name
     * @param string $namespace
     * @return \Zend\Db\TableGateway\TableGatewayInterface
     */
    public function get($name,$namespace=null){
        $namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
        $schema = $this->getDbAdapter()->getDriver()->getConnection()->getCurrentSchema();
        $tableName = $this->solveTableName($schema,$name,$namespace);
        $tableClass = $this->solveTableClass($schema,$name,$namespace);
        $rowClass = $this->solveRowClass($schema,$name,$namespace);
        $rowsetClass = $this->solveRowsetClass($schema,$name,$namespace);

        /** @var \Zend\Db\RowGateway\RowGatewayInterface $row */
        $row = $this->getLoader()->get($rowClass,array(null,$tableName));
        $row->setNamespace($namespace);
        /** @var \Zend\Db\ResultSet\ResultSetInterface $rowset */
        $rowset = $this->getLoader()->get($rowsetClass,array(),$namespace);
        $rowset->setNamespace($namespace);
        /** @var \Zend\Db\TableGateway\TableGatewayInterface $table */
        $table = $this->getLoader()->get($tableClass,array($this->solveTableName($schema,$name,$namespace),$this->getDbAdapter(),$row,$rowset),$namespace);
        $table->setNamespace($namespace);
        return $table;
    }

    /**
     * @param string $name
     * @param string $namespace
     * @return \Zend\Db\TableGateway\TableGatewayInterface
     */
    public function singleton($name,$namespace=null){
        $namespace = $namespace?:$this->getConfig()->getDefaultNamespace();
        $schema = $this->getDbAdapter()->getDriver()->getConnection()->getCurrentSchema();
        $tableClass = $this->solveTableClass($schema,$name,$namespace);
        $table = $this->get($name,$namespace);
        $this->getLoader()->set($tableClass,$table,$namespace);
        return $table;
    }

}