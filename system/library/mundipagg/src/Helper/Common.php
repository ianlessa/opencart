<?php
namespace Mundipagg\Helper;

class Common
{
    /**
     * @param string $snake
     * @return string
     */
    public function fromSnakeToCamel($snake)
    {
        $result = [];
        $length = strlen($snake);

        for ($i = 0; $i < $length ; $i++) {
            if ($snake[$i] === '_') {
                $result[] = ucfirst($snake[++$i]);
            } else {
                $result[] = $snake[$i];
            }
        }

        return implode($result);
    }
}