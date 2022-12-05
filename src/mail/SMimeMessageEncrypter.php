<?php
/**
 *
 * @author    yzh52521
 * @link      https://github.com/yzh52521/webman-mailer
 * @copyright 2022 yzh52521 all rights reserved.
 * @license   http://www.apache.org/licenses/LICENSE-2.0
 */
declare( strict_types = 1 );

namespace yzh52521\mailer\mail;

use Symfony\Component\Mime\Crypto\SMimeEncrypter;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
class SMimeMessageEncrypter implements MessageEncrypterInterface
{
    private SMimeEncrypter $encrypter;

    public function __construct(SMimeEncrypter $encrypter)
    {
        $this->encrypter = $encrypter;
    }

    public function encrypt(Message $message): Message
    {
        return $this->encrypter->encrypt( $message );
    }
}
