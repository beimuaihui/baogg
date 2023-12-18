<?php
namespace Baogg;

class Expression
{
    public function testing()
    {
        $expr = "1 and 6 or 2 and 3 or 4 and 5";

        if ($expr == 'and' ||  $expr == 'or') {
            return $expr;
        }

        //判断左右括号是否相等
        if (!$this->check($expr)) {
            error_log(__FILE__.__LINE__." \n bracket is not matched");
            return '';
        }
        


        $res = $this->parse($expr); // (1 and 2)  or (3 and 4) or (5 and 6)

        var_dump($res);
    }

    /**
     * 根据字符串表达转成数组
     *
     * @param string  $expr  表达式，如 "1 and 6 or 2 and 3 or 4 and 5"
     * @param integer $level 深度
     *
     * @return array 返回数组结果，如 array(or => ([and => (1,6)],[ and => (2,3)], ...)
     */
    public function parse($expr = '', $level = 0)
    {
        $expr = $this->fix($expr);
        // echo "after fixed expr = ".var_export($expr, true)." \n ";

        $expr_len = strlen($expr);

        //获取子表达式及（以及）的位置
        $arr_sub_group = $this->getSubGroupFromExpr($expr);
        $arr_sub_group_replace = array(); //直接替换内容
        if ($arr_sub_group) {
            //获取子表达式的替换数组 expr => -(key +1)
            foreach ($arr_sub_group as $k_sub_group => $v_sub_group) {
                $arr_sub_group_replace[$v_sub_group] = -1*($k_sub_group + 1);
            }
            $expr = strtr($expr, $arr_sub_group_replace);
        }
        // echo "\n\n\n\n\n\n".__FILE__.__LINE__." \n expr =".var_export($expr, true)."\n arr_sub_group = ".var_export($arr_sub_group, true)." \n arr_sub_group_replace=". var_export($arr_sub_group_replace, true);


        if (strpos($expr, 'or') !== false) {
            $arr_expr = explode('or', $expr);
            foreach ($arr_expr as $k_sub_expr => $v_sub_expr) {
                //$v_sub_expr = 0 + $v_sub_expr;
                if ($v_sub_expr < 0) {
                    // echo "\n\n\n\n\n\n".__FILE__.__LINE__." \n  sub_expr_key = {$v_sub_expr}; sub_expr =" .array_search(trim($v_sub_expr), $arr_sub_group_replace);

                    $arr_expr[$k_sub_expr] = $this->parse(array_search($v_sub_expr, $arr_sub_group_replace), $level++);
                }
            }

            return array('or' => $arr_expr);
        } elseif (strpos($expr, 'and') !== false) {
            $arr_expr = explode('and', $expr);
            foreach ($arr_expr as $k_sub_expr => $v_sub_expr) {
                //$v_sub_expr = 0 + $v_sub_expr;
                if ($v_sub_expr < 0) {
                    $arr_expr[$k_sub_expr] = $this->parse(array_search($v_sub_expr, $arr_sub_group_replace), $level++);
                }
            }

            return array('and' => $arr_expr);
        } else {
            return $expr;
        }

        return $expr ;
    }

    /**
     * 检测表达式括号是否匹配，不允许数字重复，不允许()andor0123456789中其他的字符
     *
     * @param string $expr 表达式
     *
     * @return bool
     */
    protected function check($expr = '')
    {
        $expr_len = strlen($expr);
        
        // 检测表达式括号是否匹配
        $bracket_num = 0;
        $flag = true; //最前而及最后面的括号是否匹配
        for ($i = 0; $i < $expr_len; $i++) {
            if (substr($expr, $i, 1) == '(') {
                $bracket_num++;
            } elseif (substr($expr, $i, 1) == ')') {
                $bracket_num--;
            }
            if ($bracket_num < 0) {
                $flag = false;
                break;
            }
        }
        if ($flag) {
            if ($bracket_num != 0) {
                $flag = false;
            }
        }

        //检测不允许数字重复
        if ($flag) {
            preg_match('/\d+/', $expr, $matched);
            if (array_unique($matched) != $matched) {
                // echo __FILE__.__LINE__." \n matched number is =".var_export($matched, true);
                // 暂时允许重复数字
            }
        }

        
        // 不允许()andor0123456789中其他的字符
        if ($flag) {
            for ($i = 0; $i < $expr_len; $i++) {
                if (!in_array(substr($expr, $i, 1), array(' ', '(', ')', 'a', 'n', 'd', 'o', 'r', '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'))) {
                    // echo __FILE__.__LINE__." \n invalid char is =".substr($expr, $i, 1);
                    $flag = false;
                    break;
                }
            }
        }

        return $flag;
    }

    /**
     * 修正表达式，删除或增加括号
     *
     * @param string  $expr  表达式
     * @param integer $level 级别
     *
     * @return string
     */
    protected function fix($expr = '', $level = 0)
    {
        $expr = \trim(\strtolower($expr));
        $expr_len = strlen($expr);

        
        //删除单表达式中有括号的情况
        $expr = preg_replace('/\(\s*\d+\s*\)/i', '$1', $expr);
        // echo __FILE__.__LINE__." \n expr =".var_export($expr, true);


        //删除多余的最前而及最后面的括号，删除最前面最后面的空格
        if (substr($expr, 0, 1) == '(') {
            $bracket_num = 0;
            $flag = false; //最前而及最后面的括号是否匹配
            for ($i = 0; $i < $expr_len; $i++) {
                if (substr($expr, $i, 1) == '(') {
                    $bracket_num++;
                } elseif (substr($expr, $i, 1) == ')') {
                    $bracket_num--;
                }
                if ($bracket_num == 0) {
                    if ($i == $expr_len - 1) {
                        $flag = true;
                    }
                    break;
                }
            }
            if ($flag) {
                $expr = substr($expr, 1, -1); // 删除多余的括号
            }
        }
        // echo __FILE__.__LINE__." \n expr =".var_export($expr, true);

       

        //获取子表达式及（以及）的位置
        $arr_sub_group = array();
        $arr_sub_group_replace = array(); //直接替换内容
        $arr_sub_gorup = $this->getSubGroupFromExpr($expr);
        if ($arr_sub_group) {
            //获取子表达式的替换数组 expr => -(key +1)
            
            foreach ($arr_sub_group as $k_sub_group => $v_sub_group) {
                $arr_sub_group_replace[$v_sub_group] = -1*($k_sub_group + 1);
            }
            $expr = strtr($expr, $arr_sub_group_replace);
        }
        // echo " \n \n\n\n".__FILE__.__LINE__." \n expr =".var_export($expr, true)." \n arr_sub_group = ".var_export($arr_sub_group, true)        . "\n arr_sub_group_replace".var_export($arr_sub_group_replace, true)." \n \n\n\n";


        // 添加缺少的括号
        if (strpos($expr, 'and') !== false && strpos($expr, 'or') !== false && strpos($expr, '(') === false) {
            $arr_str = explode('or', $expr);
            foreach ($arr_str as $k_str=>$v_str) {
                if (strpos($v_str, 'and') !== false) {
                    $arr_str[$k_str] = '('.trim($v_str).')';
                } else {
                    $arr_str[$k_str] = trim($v_str);
                }
            }
            $expr = implode(' or ', $arr_str);
        }
        // echo __FILE__.__LINE__." \n expr =".var_export($expr, true);

        
        if ($arr_sub_group) {
            $expr= strtr($expr, array_reverse($arr_sub_group_replace));
        }
        // echo __FILE__.__LINE__." \n expr =".var_export($expr, true);


        $arr_sub_group_sub_replace = array();
        foreach ($arr_sub_group as $k_sub_group => $v_sub_group) {
            $expr_to_fixed = substr($v_sub_group, 1, -1); //删除子组中的()
            $arr_sub_group_sub_replace[$expr_to_fixed] = $this->fix($expr_to_fixed, $level++);
        }
        $expr = strtr($expr, $arr_sub_group_sub_replace);
        // echo __FILE__.__LINE__." \n expr =".var_export($expr, true);


        return $expr ;
    }

    protected function getSubGroupFromExpr($expr = '')
    {
        $expr_len = strlen($expr);
        $left_bracket_pos = -1; //子表达式左括号中的位置
        $bracket_num = 0;
        $arr_sub_group = [];

        for ($i = 0; $i < $expr_len; $i++) {
            if (''.substr($expr, $i, 1) === '(') {
                $bracket_num++;
                if ($bracket_num == 1) {
                    $left_bracket_pos = $i;
                }
            } elseif (''.substr($expr, $i, 1) === ')') {
                $bracket_num--;

                if ($bracket_num == 0 && $i>0 && $left_bracket_pos>=0) {
                    $arr_sub_group[] = substr($expr, $left_bracket_pos, $i - $left_bracket_pos + 1);

                    // echo __FILE__.__LINE__." \n arr_sub_group =".var_export($arr_sub_group, true)." ;left pos = {$left_bracket_pos};right pos = {$i}";


                    $left_bracket_pos = -1;
                }
            }
        }
        return $arr_sub_group;
    }

    /**
     * 将表达式数组转在字符串
     *
     * @param array $arr_expr 表达式数组，如 array(or => ([and => (1,6)],[ and => (2,3)], ...)
     *
     * @return string 表达式字符串，如 (1 and 2)  or (3 and 4) or (5 and 6)
     */
    public function stringify($arr_expr)
    {
        if (!is_array($arr_expr)) {
            return $arr_expr;
        }
        
        $arr_stringify = array();
        if (isset($arr_expr['or'])) {
            foreach ($arr_expr['or'] as $k_expr => $v_expr) {
                $arr_stringify[$k_expr] = $this->stringify($v_expr);
            }
            // echo __FILE__.__LINE__." \n arr_expr = ".var_export($arr_stringify, true);

            return '('.\implode(' or ', $arr_stringify).')';
        } elseif (isset($arr_expr['and'])) {
            foreach ($arr_expr['and'] as $k_expr => $v_expr) {
                $arr_stringify[$k_expr] = $this->stringify($v_expr);
            }
            return '('.\implode(' and ', $arr_stringify).')';
        }

        return '';
    }
}
