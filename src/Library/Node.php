<?php
/**
 * MIT License
 * ===========
 *
 * Copyright (c) 2016 陈泽韦 <549226266@qq.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 * IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 * CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 * SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 * @author     陈泽韦 <549226266@qq.com>
 * @copyright  2016 陈泽韦.
 * @license    http://www.opensource.org/licenses/mit-license.php  MIT License
 * @version    1.0.0
 * @link       http://
 */
namespace Ppeerit\Tree\Library;

/**
 *
 */
class Node
{
    /**
     * 以正确顺序排列的子节点数组
     *
     * @var array
     */
    protected $children = [];

    /**
     * 默认配置
     * @var [type]
     */
    protected $config = [
        'pk'     => 'id',
        'parent' => 'pid',
    ];

    /**
     * 节点属性索引数组
     * @var array
     */
    protected $properties = [];

    /**
     * 用于树型数组完成递归格式的全局变量
     * @var [type]
     */
    private $formatTree = [];

    /**
     * 构造方法
     * @param array $properties [description]
     * @param array $config     [description]
     */
    public function __construct(array $properties, array $config = [])
    {
        if (!empty($config)) {
            $this->config = array_merge($this->config, array_change_key_case($config, CASE_LOWER));
        }
        if (!empty($properties)) {
            $this->properties = array_change_key_case($properties, CASE_LOWER);
        }
    }

    /**
     * 添加到树
     * @param Node $child [description]
     */
    public function addChild(Node $child)
    {
        $this->children[]                           = $child;
        $child->parent                              = $this;
        $child->properties[$this->config['parent']] = $this->properties[$this->config['pk']];
    }

    public function formatNode()
    {
        //dump($this);
        $this->toFormatTree($this);
        return $this->formatTree;
    }

    public function getChildren()
    {
        return $this->children;
    }

    /**
     * 获取后裔
     * @param  boolean $includeSelf [description]
     * @return [type]               [description]
     */
    public function getDescendants($includeSelf = false)
    {
        $descendants = $includeSelf ? [$this] : [];
        foreach ($this->children as $childnode) {
            $descendants[] = $childnode;
            if ($childnode->hasChildren()) {
                $descendants = array_merge($descendants, $childnode->getDescendants());
            }
        }
        return $descendants;
    }

    /**
     * 是否存在子节点
     * @return boolean [description]
     */
    public function hasChildren()
    {
        return count($this->children) ? true : false;
    }

    /**
     * 返回属性数组
     * @return [type] [description]
     */
    public function toArray()
    {
        return $this->properties;
    }

    /**
     * 格式化树
     * @param  array   $list  [description]
     * @param  integer $level [description]
     * @return [type]         [description]
     */
    protected function toFormatTree($data, $level = 0)
    {

        $title = $this->config['title'];

        if (is_object($data)) {
            $tmp_str = str_repeat("&nbsp;", $level * 2);
            $tmp_str .= "└";
            $this->properties['level']      = $level;
            $this->properties['title_show'] = $level == 0 ? $data->properties[$title] . "&nbsp;" : $tmp_str . $data->properties[$title] . "&nbsp;";
            //$data->level = $level;
            //$data->title_show = $level == 0 ? $data->properties[$title] . "&nbsp;" : $tmp_str . $data->properties[$title] . "&nbsp;";
            if (!$data->children) {
                array_push($this->formatTree, $data->toArray());
            } else {
                array_push($this->formatTree, $data->toArray());
                $this->toFormatTree($data->children, $level + 1); //进行下一层递归
            }
        } else {
            foreach ($data as $key => $val) {
                $tmp_str = str_repeat("&nbsp;", $level * 2);
                $tmp_str .= "└";
                $val->properties['level']      = $level;
                $val->properties['title_show'] = $level == 0 ? $val->properties[$title] . "&nbsp;" : $tmp_str . $val->properties[$title] . "&nbsp;";
                // $val->level = $level;
                // $val->title_show = $level == 0 ? $val->properties[$title] . "&nbsp;" : $tmp_str . $val->properties[$title] . "&nbsp;";

                if (!$val->children) {
                    array_push($this->formatTree, $val->toArray());
                } else {
                    array_push($this->formatTree, $val->toArray());
                    $this->toFormatTree($val->children, $level + 1); //进行下一层递归
                }

            }
        }
        return;
    }
}
