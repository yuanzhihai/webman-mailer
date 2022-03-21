<?php
/**
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/webman-mailer
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */

namespace yzh52521\mailer\think;


/**
 * Class Mailer
 * @package yzh52521\mailer\think
 */
class Mailer extends \yzh52521\mailer\Mailer
{
    /**
     * 载入一个模板作为邮件内容
     *
     * @param string $template
     * @param array $param
     * @param array $config
     *
     * @return Mailer
     */
    public function view(string $template, array $param = [], array $config = []): Mailer
    {
        $view = new \think\Template(config('view'));
        // 处理变量中包含有对元数据嵌入的变量
        foreach ($param as $k => $v) {
            $this->embedImage($k, $v, $param);
        }
        $content = $view->fetch($template, $param);

        return $this->setHtmlBody($content);
    }
}
