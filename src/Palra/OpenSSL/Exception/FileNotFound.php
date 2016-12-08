<?php
/**
 * FileNotFound.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Palra\OpenSSL\Exception;


class FileNotFound extends \Exception
{

    /**
     * FileNotFound constructor.
     */
    public function __construct($path, $code = 0, \Exception $previous = null)
    {
        parent::__construct(sprintf('The file `%s` could not be found', $path), $code, $previous);
    }
}