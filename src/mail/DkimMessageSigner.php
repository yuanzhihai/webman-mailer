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

use Symfony\Component\Mime\Crypto\DkimSigner;
use Symfony\Component\Mime\Message;

/**
 * @codeCoverageIgnore This class is a trivial proxy that requires no testing
 */
class DkimMessageSigner implements MessageSignerInterface
{
    private DkimSigner $dkimSigner;

    public function __construct(DkimSigner $dkimSigner)
    {
        $this->dkimSigner = $dkimSigner;
    }

    public function sign(Message $message,array $options = []): Message
    {
        return $this->dkimSigner->sign( $message,$options );
    }
}
