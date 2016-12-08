<?php
/**
 * FIleUtils.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Palra\OpenSSL;

class FileUtils
{
    /**
     * Converts a file or a string to a string
     *
     * @param string|\SplFileInfo $fileOrString The file or string to convert to
     * a string
     */
    public static function fileToString($fileOrString)
    {
        if ($fileOrString instanceof \SplFileInfo) {
            $fileOrString = file_get_contents($fileOrString->getPathname());
        }

        if (!is_string($fileOrString)) {
            throw new \LogicException(
                sprintf(
                    '$fileOrString should be a string or a \SplFileInfo, %s given',
                    gettype($fileOrString)
                )
            );
        }

        return (string)$fileOrString;
    }
}