<?php
/**
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/webman-mailer
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
declare (strict_types=1);

namespace yzh52521\mailer\twig;

use support\view\Twig;

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
     * @param string $app
     * @return Mailer
     */
    public function view(string $template, array $param = [], string $app = ''): Mailer
    {
        // 处理变量中包含有对元数据嵌入的变量
        foreach ($param as $k => $v) {
            $this->embedImage($k, $v, $param);
        }
        $content = Twig::render($template, $param, $app);

        return $this->setHtmlBody($content);
    }
}
