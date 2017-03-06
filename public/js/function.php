<?php
/**
 * 返回数组中指定的一列
 * http://www.onethink.cn
 * /Application/Common/Common/function.php
 *
 * array_column — PHP 5 >= 5.5.0 默认函数
 * PHP 5 < 5.5.0 则使用自定义函数
 *
 * @access public
 * @param array $input 需要取出数组列的多维数组（或结果集）
 * @param string $columnKey 需要返回值的列，它可以是索引数组的列索引，或者是关联数组的列的键。也可以是NULL，此时将返回整个数组（配合indexKey参数来重置数组键的时候，非常管用）
 * @param string $indexKey 作为返回数组的索引/键的列，它可以是该列的整数索引，或者字符串键值。
 * @return array
 */
if (! function_exists('array_column'))
{
    function array_column(array $input, $columnKey, $indexKey = null)
    {
        $result = array();
        if (null === $indexKey)
        {
            if (null === $columnKey)
            {
                $result = array_values($input);
            }
            else
            {
                foreach ($input as $row)
                {
                    $result[] = $row[$columnKey];
                }
            }
        }
        else
        {
            if (null === $columnKey)
            {
                foreach ($input as $row)
                {
                    $result[$row[$indexKey]] = $row;
                }
            }
            else
            {
                foreach ($input as $row)
                {
                    $result[$row[$indexKey]] = $row[$columnKey];
                }
            }
        }
        return $result;
    }
}