<?php
/**
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/webman-mailer
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
declare ( strict_types = 1 );

namespace yzh52521\mailer\mail;

use DateTimeImmutable;
use DateTimeInterface;
use support\Log;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Header\HeaderInterface;
use yzh52521\mailer\exception\Exception;
use yzh52521\mailer\exception\InvalidArgumentException;

/**
 * Class Mailer
 * @package yzh52521\mailer\mail
 */
class Mailer implements MessageWrapperInterface
{

    private $charset = 'utf-8';
    /**
     * @var Email
     */
    protected $message;

    /**
     * @var string|null 错误信息
     */
    protected $err_msg;

    protected bool $debug = false;

    /** @var array|string 发信人 */
    protected $from = [];

    protected $html;

    protected $text;


    /**
     * @var MessageSignerInterface
     */
    private $signer = null;

    /**
     * @see https://symfony.com/doc/current/mailer.html#signing-messages
     * @var MessageEncrypterInterface
     */
    private $encrypter = null;

    public $signerOptions = [];

    private $transport = [];


    public function __construct($transport = [])
    {
        $config          = config( 'plugin.yzh52521.mailer.app' );
        $this->transport = $transport;
        $this->debug     = $config['mailer']['debug'];
        $this->from      = [$config['from']['address'] => $config['from']['name']];
        $this->message   = new Email();
    }


    public function __clone()
    {
        $this->message = clone $this->message;
    }

    /**
     * 获取字符编码
     * @return string
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * 设置字符编码
     *
     * @param string $charset
     *
     * @return $this
     */
    public function setCharset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /**
     * 设置邮件主题
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject): self
    {
        $this->message->subject( $subject );

        return $this;
    }


    /**
     * 获取邮件主题
     * @return string
     */
    public function getSubject(): string
    {
        return (string)$this->message->getSubject();
    }


    /**
     * 获取邮件 date
     * @return DateTimeImmutable|null
     */
    public function getDate(): ?DateTimeImmutable
    {
        return $this->message->getDate();
    }


    /**
     * 设置邮件date
     *
     * @param DateTimeInterface $date
     * @return $this
     */
    public function setDate(DateTimeInterface $date): self
    {
        $this->message->date( $date );

        return $this;
    }


    /**
     * 设置发件人
     *
     * @param array|string $address
     *
     * @return $this
     */
    public function setFrom($address): self
    {
        $this->from = $address;

        return $this;
    }

    /**
     * 设置发件人
     *
     * @param array|string $address
     *
     * @return $this
     */
    protected function addFrom($address): self
    {
        $this->from = $address;

        return $this;
    }

    /**
     * 设置 发件人
     * @return $this
     */
    protected function buildFrom()
    {
        if (!empty( $this->from )) {
            $this->message->from( ...$this->convertStringsToAddresses( $this->from ) );
        }
    }

    /**
     * 获取发件人
     * @return array|string
     */
    public function getFrom()
    {
        return $this->convertAddressesToStrings( $this->message->getFrom() );
    }

    /**
     * 设置收件人
     *
     * @param array|string $address
     *
     * @return $this
     */
    public function setTo($address): self
    {
        $this->message->to( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 设置收件人
     *
     * @param array|string $address
     *
     * @return $this
     */
    public function addTo($address): self
    {
        $this->message->addTo( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 获取收件人
     * @return string|array
     */
    public function getTo()
    {
        return $this->convertAddressesToStrings( $this->message->getTo() );
    }

    /**
     * 设置抄送人
     *
     * @param array|string $address
     * @return $this
     */
    public function setCc($address): self
    {
        $this->message->cc( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 设置抄送人
     *
     * @param array|string $address
     * @return $this
     */
    public function addCc($address): self
    {
        $this->message->addCc( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 获取抄送人
     * @return string|array
     */
    public function getCc()
    {
        return $this->convertAddressesToStrings( $this->message->getCc() );
    }

    /**
     * 设置暗抄人
     * @param array|string $address
     * @return $this
     */
    public function setBcc($address): self
    {
        $this->message->bcc( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 设置暗抄人
     * @param array|string $address
     * @return $this
     */
    public function addBcc($address): self
    {
        $this->message->addBcc( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 获取暗抄人
     *
     * @return array|string
     */
    public function getBcc()
    {
        return $this->convertAddressesToStrings( $this->message->getBcc() );
    }

    /**
     * 获取邮件HTML内容
     * @return string
     */
    public function getHtmlBody(): string
    {
        return (string)$this->message->getHtmlBody();
    }

    /**
     * 设置邮件header
     * @param $name
     * @param $value
     * @return $this
     */
    public function addHeader($name,$value): self
    {
        $this->message->getHeaders()->addTextHeader( $name,$value );
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     */
    public function setHeader($name,$value): self
    {
        $headers = $this->message->getHeaders();

        if ($headers->has( $name )) {
            $headers->remove( $name );
        }

        foreach ( (array)$value as $v ) {
            $headers->addTextHeader( $name,$v );
        }

        return $this;
    }

    /**
     *
     * @param array $headers
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        foreach ( $headers as $name => $value ) {
            $this->setHeader( $name,$value );
        }

        return $this;
    }

    /**
     * 设置邮件内容为HTML内容
     *
     * @param string $content
     * @param array $param
     * @param array $config
     * @return $this
     */
    public function setHtmlBody(string $content,array $param = [],array $config = []): self
    {
        $this->html = $content;

        if ($param) {
            $content = strtr( $content,$this->parseParam( $param,$config ) );
        }
        $this->message->html( $content,$this->charset );


        return $this;
    }


    /**
     * 获取邮件内容为纯文本内容
     * @return string
     */
    public function getTextBody(): string
    {
        return (string)$this->message->getTextBody();
    }

    /**
     * 设置邮件内容为纯文本内容
     *
     * @param string $content
     * @param array $param
     * @param array $config
     *
     * @return $this
     */
    public function setTextBody(string $content,array $param = [],array $config = []): self
    {
        $this->text = $content;

        if ($param) {
            $content = strtr( $content,$this->parseParam( $param,$config ) );
        }
        $this->message->text( $content,$this->charset );

        return $this;
    }

    /**
     * 获取模版
     * @param string $template
     * @param array $vars
     * @param string|null $app
     * @return mixed
     */
    private function template(string $template,array $vars = [],string $app = null)
    {
        $handler = \config( 'view.handler' );
        return $handler::render( $template,$vars,$app );
    }

    /**
     *  调用模板引擎渲染模板
     * @param string $template
     * @param array $param
     * @param string $app
     * @return $this
     */
    public function view(string $template,array $param = [],string $app = ''): Mailer
    {
        // 处理变量中包含有对元数据嵌入的变量
        foreach ( $param as $k => $v ) {
            $this->embedImage( $k,$v,$param );
        }
        $content = $this->template( $template,$param,$app );

        return $this->setHtmlBody( $content );
    }


    /**
     * 添加附件
     *
     * @param string $filePath
     * @param array $options
     *
     * @return $this
     */
    public function attach(string $filePath,array $options = []): self
    {
        $file = [];
        if (!empty( $options['fileName'] )) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = $filePath;
        }
        if (!empty( $options['contentType'] )) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = mime_content_type( $filePath );
        }
        $this->message->attachFromPath( $filePath,$file['name'],$file['contentType'] );

        return $this;
    }

    /**
     * @param $content
     * @param array $options
     *
     * @return $this
     */
    public function attachContent($content,array $options = []): self
    {
        $file = [];
        if (!empty( $options['fileName'] )) {
            $file['name'] = $options['fileName'];
        } else {
            $file['name'] = null;
        }

        if (!empty( $options['contentType'] )) {
            $file['contentType'] = $options['contentType'];
        } else {
            $file['contentType'] = null;
        }

        $this->message->attach( $content,$file['name'],$file['contentType'] );
        return $this;
    }

    /**
     * 设置优先级
     *
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority(int $priority = 1): self
    {
        $this->message->priority( $priority );

        return $this;
    }

    /**
     * 获取优先级
     * @return int
     */
    public function getPriority(): int
    {
        return $this->message->getPriority();
    }

    /**
     * 设置回复邮件地址
     * @param array|string $address
     * @return $this
     */
    public function setReplyTo($address): self
    {
        $this->message->replyTo( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 设置回复邮件地址
     * @param array|string $address
     * @return $this
     */
    public function addReplyTo($address): self
    {
        $this->message->addReplyTo( ...$this->convertStringsToAddresses( $address ) );

        return $this;
    }

    /**
     * 获取回复邮件地址
     * @return array|string
     */
    public function getReplyTo()
    {
        return $this->convertAddressesToStrings( $this->message->getReplyTo() );
    }

    public function getReturnPath(): string
    {
        $returnPath = $this->message->getReturnPath();
        return $returnPath === null ? '' : $returnPath->getAddress();
    }

    /**
     *
     * @param string $address
     * @return $this
     */
    public function setReturnPath(string $address): self
    {
        $this->message->returnPath( $address );

        return $this;
    }

    public function getSender(): string
    {
        $sender = $this->message->getSender();
        return $sender === null ? '' : $sender->getAddress();
    }

    /**
     *
     * @param string $address
     * @return $this
     */
    public function setSender(string $address): self
    {
        $this->message->sender( $address );
        return $this;
    }


    /**
     * 获取头信息
     *
     */
    public function getHeaders($name): array
    {
        $headers = $this->message->getHeaders();
        if (!$headers->has( $name )) {
            return [];
        }

        $values = [];

        /** @var HeaderInterface $header */
        foreach ( $headers->all( $name ) as $header ) {
            $values[] = $header->getBodyAsString();
        }

        return $values;
    }

    /**
     * 获取头信息 (字符串)
     *
     * @return string
     */
    public function getHeadersString(): string
    {
        return $this->message->getHeaders()->toString();
    }

    /**
     * 将参数中的key值替换为可替换符号
     *
     * @param array $param
     * @param array $config
     * @return array
     */
    protected function parseParam(array $param,array $config = [])
    {
        $ret            = [];
        $leftDelimiter  = !empty( $config['tpl_begin'] ) ?: config( 'view.option.tpl_begin','{' );
        $rightDelimiter = !empty( $config['tpl_end'] ) ?: config( 'view.option.tpl_end','}' );
        foreach ( $param as $k => $v ) {
            // 处理变量中包含有对元数据嵌入的变量
            $this->embedImage( $k,$v,$param );
            $ret[$leftDelimiter.$k.$rightDelimiter] = $v;
        }

        return $ret;
    }


    /**
     * Returns a Symfony message instance.
     *
     * @return Email Symfony message instance.
     */
    public function getSymfonyEmail(): Email
    {
        return $this->message;
    }

    /**
     * 发送邮件
     * @param \Closure|null $message
     * @param array $transport
     * @return bool
     * @throws \Exception
     */
    public function send(\Closure $message = null,array $transport = []): bool
    {
        try {
            // 匿名函数
            if ($message instanceof \Closure) {
                call_user_func_array( $message,[&$this,&$this->message] );
            }

            if (empty( $transport ) && $this->transport) {
                $transport = $this->transport;
            }

            $transportInstance = new Transport();
            $transportInstance->setTransport( $transport );
            $mailer = $transportInstance->getSymfonyMailer();

            $message = $this->getSymfonyEmail();

            if ($this->encrypter !== null) {
                $message = $this->encrypter->encrypt( $message );
            }

            if ($this->signer !== null) {
                $message = $this->signer->sign( $message,$this->signerOptions );
            }

            // 发送邮件
            $this->buildFrom();
            $mailer->send( $message );
            return true;
        } catch ( TransportExceptionInterface|\Throwable $e ) {
            $this->err_msg = $e->getMessage();
            throw new Exception($e->getMessage(),$e->getCode(),$e);
        }
    }

    /**
     * 设置debug
     * @param bool $debug
     * @return $this
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }


    /**
     * 获取邮件内容
     * @return string
     */
    public function render(): string
    {
        return $this->html ? $this->getHtmlBody() : $this->getTextBody();
    }


    /**
     * 获取错误信息
     *
     * @return string|null
     */
    public function getError(): ?string
    {
        return $this->err_msg;
    }

    /**
     * 对嵌入元数据的变量进行处理
     *
     * @param string $k
     * @param string|array $v
     * @param array $param
     */
    protected function embedImage(string &$k,&$v,array &$param)
    {
        $cid = 'cid:';
        if (false !== strpos( $k,$cid )) {
            $filename = 'image';
            if (is_array( $v ) && $v) {
                if (!isset( $v[1] )) {
                    $v[1] = $filename;
                }
                if (!isset( $v[2] )) {
                    $v[2] = null;
                }
                [$imgPath,$filename,$fileMime] = $v;
                $this->message->embed( $imgPath,$filename,$fileMime );
            } else {
                $this->message->embedFromPath( $v,$filename );
            }
            unset( $param[$k] );
            $k         = substr( $k,strlen( $cid ) );
            $param[$k] = $cid.$filename;
        }
    }

    /**
     * Converts address instances to their string representations.
     *
     * @param Address[] $addresses
     *
     * @return array<string, string>|string
     */
    private function convertAddressesToStrings(array $addresses)
    {
        $strings = [];

        foreach ( $addresses as $address ) {
            $strings[$address->getAddress()] = $address->getName();
        }

        return empty( $strings ) ? '' : $strings;
    }

    /**
     * Converts string representations of address to their instances.
     *
     * @param string|array<int|string, string> $strings
     *
     * @return Address[]
     */
    private function convertStringsToAddresses($strings): array
    {
        if (is_string( $strings )) {
            return [new Address( $strings )];
        }

        $addresses = [];

        foreach ( $strings as $address => $name ) {
            if (!is_string( $address )) {
                // email address without name
                $addresses[] = new Address( $name );
                continue;
            }

            $addresses[] = new Address( $address,$name );
        }

        return $addresses;
    }

}
