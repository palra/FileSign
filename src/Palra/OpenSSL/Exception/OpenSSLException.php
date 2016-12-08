<?php

namespace Palra\OpenSSL\Exception;

/**
 * OpenSSLException.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Class OpenSSLException, that domps the last OpenSSL error in the exception
 * message
 * @author Loïc Payol <loicpayol@gmail.com>
 */
class OpenSSLException extends \Exception
{

    /**
     * OpenSSLException constructor.
     */
    public function __construct($message = "An error occured with OpenSSL : %s", $code = 0, \Exception $previous = null)
    {
        $opensslError = '';
        do {
            $error = openssl_error_string();
            $opensslError .= $error . ' ';
        } while (false !== $error);

        parent::__construct(
            sprintf($message, $opensslError),
            $code,
            $previous
        );
    }
}