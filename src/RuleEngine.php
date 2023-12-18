<?php
/**
 * User: bao
 * Date: 19-7-2
 * Time: 上午11:38
 */

namespace Baogg;

class RuleEngine
{
    const OP_AND = 'and';
    const OP_OR = 'or';

    //combine variables ,such as parent fans,with level3 ,generation 99,then var name is parentFans_Level-3_generation-99
    //such as json {
    //  "or": [
    //    {
    //      "and": [
    //        {
    //          "level": 3
    //        },
    //        {
    //          "total_money": 5985
    //        },
    //        {
    //          "or": [
    //            {
    //              "parentFans_level:2": 15
    //            },
    //            {
    //              "parent3_fans": 6
    //            }
    //          ]
    //        }
    //      ]
    //    },
    //    {
    //      "and": [
    //        {
    //          "level": 4
    //        },
    //        {
    //          "total_money": 42933
    //        },
    //        {
    //          "parent4_fans": 3
    //        }
    //      ]
    //    }
    //  ]
    //}
    protected $where = array();

    //such as json {
    //  "level": {
    //    "title": "个人成为 {level_name}",
    //    "perc": 0,
    //    "unit": "",
    //    "option": {
    //      "1": "普通会员",
    //      "2": "黄金会员",
    //      "3": "白金会员",
    //      "4": "钻石会员"
    //    }
    //  },
    //  "total_money": {
    //    "title": "业绩",
    //    "perc": 0,
    //    "unit": "元"
    //  },
    //  "parent2_fans": {
    //    "title": "直接销售黄金会员",
    //    "perc": 0,
    //    "unit": "人"
    //  },
    //  "parent3_fans": {
    //    "title": "直接销售白金会员",
    //    "perc": 0,
    //    "unit": "人"
    //  },
    //  "parent4_fans": {
    //    "title": "直接销售钻石会员",
    //    "perc": 0,
    //    "unit": "人"
    //  }
    //}
    protected $arr_label = array();

    //such as json {
    //  "level": 3,
    //  "total_money": 60000,
    //  "parent2_fans": 20,
    //  "parent3_fans": 10,
    //  "parent4_fans": 7
    //}
    protected $arr_var = array();

    protected $form_value = array();

    protected $is_consume = 1;

    protected $customer_id = 0;
    protected $user_id = 0;

    protected $RuleExpChecker ; //rule express checker,such as array('level'=>4);if level db value is 3 then return false;

    public function __construct($customer_id =0)
    {
        $this->customer_id = (int)$customer_id;
        $this->RuleExpChecker = new \Baogg\App\Model\BaoggWeixinCommonshopShareholderPlusRule();
        $this->where = \Baogg\File::getSetting('Baogg.promoter.is_consume_upgrade_by_ruler');
        $this->arr_label = \Baogg\Language::get('promoter.is_consume_upgrade_by_ruler_label');

        $form_value = array();
        array_walk_recursive($this->where, function ($v, $k) use (&$form_value) {
            //error_log(__FILE__.__LINE__." \n {$k}=>{$v}");
            $form_value[$k]=$v;
        });
        $this->form_value = $form_value;

        //error_log(__FILE__.__LINE__." \n form_value = ".var_export($form_value,true));
    }


    public function check($where=array(), $form_value=array(), $arr_var=array(), $depth=0)
    {
        $result = true;
        foreach ($where as $op=>$sub_where) {
            if (trim($op) == 'or') {
                $result = false;
                foreach ($sub_where as $k_sub_where=>$v_sub_where) {
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $result_cur =  $this->check($v_sub_where, $form_value, $arr_var, $depth+1);
                        if ($result_cur == true) {
                            return true;
                        }
                    } else {
                        if ($this->checkRow($v_sub_where, $form_value, $arr_var)) {
                            return true;
                        }
                    }
                }
            } elseif (trim($op) == 'and') {
                $result = true;
                foreach ($sub_where as $v_sub_where) {
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $result_cur =  $this->check($v_sub_where, $form_value, $arr_var, $depth+1);
                        if (!$result_cur) {
                            return false;
                        }
                    } else {
                        if (!$this->checkRow($v_sub_where, $form_value, $arr_var)) {
                            return false;
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function checkRow($v_sub_where=array(), $form_value=array(), $arr_var=array())
    {
        $key = key($v_sub_where);

        $simple_key =  substr($key, 0, strrpos($key, '_'));
        error_log(__FILE__.__LINE__." \n form_value=".var_export($form_value, true));

        if (count($v_sub_where) == 1) {
            error_log(__FILE__.__LINE__." \n key={$key};simple_key={$simple_key};variable={$arr_var[$simple_key]};target={$form_value[$key]}");
            return $arr_var[$simple_key] >= $form_value[$key];
        } else {
            $index = 0;
            $combine_key = '';
            $form_key = key($v_sub_where);
            foreach ($v_sub_where as $key=>$v) {
                $simple_key =  substr($key, 0, strrpos($key, '_'));

                $combine_key .= $index==0?$simple_key:'_'.$simple_key.'-'.$form_value[$key];
                $index ++;
            }
            error_log(__FILE__.__LINE__." \n key={$key};simple_key={$simple_key};combine_key={$combine_key}; variable={$arr_var[$combine_key]};target={$form_value[$form_key]}");
            return $arr_var[$combine_key] >= $form_value[$form_key];
        }
    }


    /**
     * @param $where 规则
     * @param $form_value　设置的目标值
     * @param $arr_var　统计计算值
     * @param $arr_label　显示标签内容
     * @param $depth　深度
     * @return string
     */
    public function show($where=array(), $form_value=array(), $arr_var=array(), $arr_label=array(), $depth=0)
    {
        if (!$where) {
            $where = $this->where[$this->is_consume];
        }
        if (!$form_value) {
            $form_value = $this->form_value;
        }
        if (!$arr_label) {
            $arr_label = $this->arr_label;
        }
        if (!$arr_var) {
            $arr_var = $this->arr_var;
        }

        $arr_head = array('一','二','三','四','五','六','七','八','九','十');

        $ret = '';
        foreach ($where as $op=>$sub_where) {  // just get op value
            if (trim($op) == 'or') {
                $result = false;
                $or_index = 0;

                foreach ($sub_where as $k_sub_where=>$v_sub_where) { // travel op value item
                    if ($depth<=0) {
                        $ret .= '<p class="p02 cl_888">标准'.$arr_head[$or_index].'</p>';
                    }
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $ret .=  $this->show($v_sub_where, $form_value, $arr_var, $arr_label, $depth+1);
                    } else {
                        //$ret .= $or_index==0?'<div>':';或者';
                        $ret .= $this->showRow($v_sub_where, $form_value, $arr_var, $arr_label, $or_index==0?'':';或者');
                    }

                    if ($depth<=0) {
                        $ret .= '<br />';
                    }
                    $or_index ++;
                }
                //$ret .= "</div>";
            } elseif (trim($op) == 'and') {
                foreach ($sub_where as $v_sub_where) {
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $ret .=    $this->show($v_sub_where, $form_value, $arr_var, $arr_label, $depth+1);
                    } else {
                        $ret .= '<div>'.$this->showRow($v_sub_where, $form_value, $arr_var, $arr_label).'</div>';
                    }
                }
            }
        }

        return $ret;
    }

    public function showRow($v_sub_where = array(), $form_value=array(), $arr_var=array(), $arr_label=array(), $label_pre='')
    {
        $ret = '';
        $label_param_name = '';
        $var_param_name = '';
        $form_param_name = '';
        $index = 0 ;
        $arr_tr = array();
        foreach ($v_sub_where as $param_name=>$param_value) {
            $simple_param_name = substr($param_name, 0, strrpos($param_name, '_'));
            $label_param_name .= ($index == 0 ? $simple_param_name : '_' . $simple_param_name);
            $form_param_name .=  ($index == 0 ? $param_name : '');

            //error_log(__FILE__.__LINE__." \n param_name={$param_name};form_value=".var_export($form_value,true));

            $var_param_name .=  ($index == 0 ? $simple_param_name : '_' . $simple_param_name.'-'.$form_value[$param_name]);

            if (isset($arr_label[$simple_param_name]['option'])) {
                $arr_tr = array('{' . $simple_param_name . '2}' => $arr_label[$simple_param_name]['option'][$form_value[$param_name]]);
            }
            $index++;
        }
        //error_log(__FILE__.__LINE__." \n label_param_name={$label_param_name}; var_param_name={$var_param_name};form_param_name={$form_param_name}");

        $title = $arr_label[$label_param_name]['title'];
        if ($arr_tr) {
            $title = strtr($title, $arr_tr);
        }

        if (isset($arr_var[$var_param_name])) {
            $var = $arr_var[$var_param_name];
        } elseif (isset($this->arr_var[$var_param_name])) {
            $var = $this->arr_var[$var_param_name];
        } else {
            error_log(__FILE__.__LINE__." \n var_param_name={$var_param_name}");
            $var = $this->getVar($var_param_name);
        }

        $setting = $form_value[$form_param_name];
        $perc = bcdiv(bcmul($var, 100), $setting);
        return '<div class="msg_box">
					<div class="prograss_bar">
						<div class="Percentage" style="width:'.$perc.'%;max-width:100%;min-width:0;"></div>
					</div>
					<div class="table msg" style="font-size:11px;">
						<p class="cl_o p04">'.$label_pre.$title.'</p>
						<p class="cl_o p05">'.$var.'/'.$setting.$arr_label[$label_param_name]['unit'].'</p>
					</div>
				</div>';
        //return "<br /> title={$title};perc ={$perc}%; var={$var};setting={$setting};unit={$arr_label[$label_param_name]['unit']}";
    }






    public function edit($where=array(), $form_value=array(), $arr_label=array(), $depth=0)
    {
        if (!$where) {
            $where = $this->where[$this->is_consume];
        }
        if (!$form_value) {
            $form_value = $this->form_value;
        }
        if (!$arr_label) {
            $arr_label = $this->arr_label;
        }

        error_log(__FILE__.__LINE__." \n where = ".var_export($where, true));
        error_log(__FILE__.__LINE__." \n arr_label = ".var_export($arr_label, true));

        $ret = '';
        foreach ($where as $op=>$sub_where) {
            if (trim($op) == 'or') {
                $result = false;
                $or_index = 0;
                foreach ($sub_where as $k_sub_where=>$v_sub_where) {
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $ret .=  $this->edit($v_sub_where, $form_value, $arr_label, $depth+1);
                    } else {
                        $ret .= $or_index==0?'<div>':';或者';
                        $ret .= $this->editRow($v_sub_where, $form_value, $arr_label);
                    }


                    if ($depth<=0 && $or_index < count($sub_where) -1) {
                        $ret .= '<br /><hr />';
                    }
                    $or_index ++;
                }
                $ret .= "</div>";
            } elseif (trim($op) == 'and') {
                foreach ($sub_where as $v_sub_where) {
                    //error_log(__FILE__.__LINE__." \n v_sub_where=".var_export($v_sub_where,true));
                    if (isset($v_sub_where['and']) || isset($v_sub_where['or'])) {
                        $ret .=    $this->edit($v_sub_where, $form_value, $arr_label, $depth+1);
                    } else {
                        $ret .= '<div>'.$this->editRow($v_sub_where, $form_value, $arr_label).'</div>';
                    }
                }
            }
        }

        return $ret;
    }

    public function editRow($v_sub_where = array(), $form_value = array(), $arr_label=array())
    {
        $ret = '';
        $index = 0 ;
        foreach ($v_sub_where as $param_name=>$param_value) {
            $arr_param_name = explode('_', $param_name);
            $ret .= "<span style='width:".($index==0?121:65)."px;display: inline-block;margin-top: 6px;'>{$arr_label[$arr_param_name[0]]['form']}:</span>";
            if ($arr_label[$arr_param_name[0]]['option']) {
                $ret .="<select  style='with:60px;' name='Baogg_rule[{$this->is_consume}][{$param_name}]' id='Baogg_rule_{$this->is_consume}_{$param_name}'>  ";
                foreach ($arr_label[$arr_param_name[0]]['option'] as $option_value=>$option_name) {
                    $ret .= '<option value="'.$option_value.'" '.($option_value == $form_value[$param_name]?'selected':'') .'>'.$option_name.'</option>';
                }
                $ret .= "</select>";
            } else {
                $ret .= "<input style='width:60px;' name='Baogg_rule[{$this->is_consume}][{$param_name}]' id='Baogg_rule_{$this->is_consume}_{$param_name}' value='{$form_value[$param_name]}' />";
            }
            $ret .= "         <span style='width:12px;margin-right: 20px;'>{$arr_label[$arr_param_name[0]]['unit']}</span>";
            $index++;
        }
        return $ret;
    }

    public function setIsConsume($is_consume=1)
    {
        $this->is_consume = $is_consume;
    }
    public function setUserId($user_id=0)
    {
        $this->user_id = $user_id;
    }

    public function getVar($var = '')
    {
        $val = $this->RuleExpChecker->getVar($this->customer_id, $this->user_id, $this->is_consume, $var);
        $this->arr_var[$var] = $val;
        return $val;
    }
}
