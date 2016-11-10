<?php

namespace Ppeerit\Tree;

use Ppeerit\Tree\Exceptions\InvalidParentException;
use Ppeerit\Tree\Library\Node;
use think\Config;

class Tree
{
    /**
     * 默认配置
     * @var [type]
     */
    protected $_config = [
        'pk'     => 'id',
        'parent' => 'pid',
        'title'  => 'title',
        'child'  => '_child',
        'rootid' => 0,
    ];

    /**
     * @var array
     */
    protected $nodes = [];

    /**
     * @var object 对象实例
     */
    protected static $tree;

    /**
     * 构造方法
     * @param array $config [description]
     */
    public function __construct()
    {
        // 将应用配置替换默认配置
        if (Config::has('tree')) {
            $config        = Config::get('tree');
            $this->_config = array_merge($this->_config, $config);
        }
    }

    /**
     * 创建树形结构
     * 静态方法
     * @param  array  $list    [description]
     * @param  array  $config [description]
     * @return [type]          [description]
     */
    public static function build(array $list = [])
    {
        if (null === self::$tree) {
            self::$tree = new static();
        }

        self::$tree->buildTree($list);
        return self::$tree;
    }

    /**
     * 获取某个节点
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function getNodeById($id = 0)
    {
        if (empty($this->nodes[$id])) {
            throw new \InvalidArgumentException("Invalid node primary key $id");
        }
        if (!$id) {
            $id = $this->_config['rootid'];
        }
        return $this->nodes[$id];
    }

    /**
     * 获取根结点
     * @return [type] [description]
     */
    public function getRoot()
    {
        return $this->nodes[$this->_config['rootid']]->getChildren();
    }

    /**
     * 获取格式化后的根结点数组
     * @return [type] [description]
     */
    public function getRootFormat()
    {
        $result = [];
        $roots  = $this->getRoot();
        foreach ($roots as $key => $node) {

            $v = $node->formatNode();

            $result = array_merge($result, $v);
        }
        return $result;
    }

    /**
     * 获取整个树
     * @return [type] [description]
     */
    public function getTree()
    {
        $nodes = [];
        foreach ($this->nodes[$this->_config['rootid']]->getDescendants() as $subnode) {
            $nodes[] = $subnode;
        }
        return $nodes;
    }

    /**
     * 创建树的核心方法
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    protected function buildTree(array $data)
    {
        $children                             = [];
        $properties                           = [];
        $properties[$this->_config['pk']]     = $this->_config['rootid'];
        $properties[$this->_config['parent']] = null;
        // 创建跟节点
        $this->nodes[$this->_config['rootid']] = $this->createNode($properties);

        foreach ($data as $row) {
            if (is_object($row)) {
                $row = $row->toArray();
            }
            $this->nodes[$row[$this->_config['pk']]] = $this->createNode($row);
            if (empty($children[$row[$this->_config['parent']]])) {
                $children[$row[$this->_config['parent']]] = [$row[$this->_config['pk']]];
            } else {
                $children[$row[$this->_config['parent']]][] = $row[$this->_config['pk']];
            }
        }

        foreach ($children as $pid => $childids) {
            foreach ($childids as $id) {
                if ((string) $pid === (string) $id) {
                    throw new InvalidParentException(
                        "节点ID引用了自己的ID作为父ID"
                    );
                }
                if (isset($this->nodes[$pid])) {
                    $this->nodes[$pid]->addChild($this->nodes[$id]);
                } else {
                    throw new InvalidParentException(
                        "节点ID$id使用不存在的父级ID$pid"
                    );
                }
            }
        }
    }

    /**
     * 创建节点
     * @param  array  $properties [description]
     * @return [type]             [description]
     */
    protected function createNode(array $properties)
    {
        return new Node($properties, $this->_config);
    }
}
