<?php
/**
 * PublicKey.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Palra\OpenSSL;

use Palra\OpenSSL\Exception\FileNotFound;
use Palra\OpenSSL\Exception\OpenSSLException;

/**
 * Wraps an OpenSSL public key resource
 * @author Loïc Payol <loicpayol@gmail.com>
 */
class PublicKey
{
    /** @var resource */
    private $opensslResource;

    /**
     * PublicKey constructor.
     * @param resource $resource The OpenSSL public key resource
     */
    public function __construct($resource)
    {
        $this->opensslResource = $resource;
    }

    /**
     * Builds a PublicKey instance from a string representation of it, with a
     * given and optional passphrase
     *
     * @param string $string The string representation of the passphrase
     * @return PublicKey The PublicKey instance
     */
    public static function fromString($string)
    {
        $resource = openssl_pkey_get_public($string);

        if (false === $resource) {
            throw new OpenSSLException("An error occured while reading a public key from a string : %s");
        }

        return new self($resource);
    }

    /**
     * Builds a PublicKey instance from a public key file
     *
     * @param string $path The path to the private key
     * @throws OpenSSLException
     */
    public static function fromFilePath($path)
    {
        $realpath = realpath($path);

        if (false === $realpath) {
            throw new FileNotFound($path);
        }

        $resource = openssl_pkey_get_public(
            sprintf('file://%s', $realpath)
        );

        if (false === $resource) {
            throw new OpenSSLException();
        }

        return new self($resource);
    }

    public function __toString()
    {
        return $this->export();
    }

    /**
     * @return string The exported public key
     * @throws OpenSSLException
     */
    public function export()
    {
        $res = openssl_pkey_get_details($this->opensslResource);
        if (false === $res) {
            throw new OpenSSLException("An error occured while exporting the public key : %s");
        }

        return $res['key'];
    }

    /**
     * @return resource
     */
    public function getOpensslResource()
    {
        return $this->opensslResource;
    }

    /**
     * Verifies $data against $signature
     *
     * @param string|\SplFileInfo $data The data to verify
     * @param string|\SplFileInfo $signature The signature
     * @param int $signature_alg See {@link http://php.net/manual/en/function.openssl-verify.php}
     * @return bool True if the verification succeed, false if it failed.
     * @throws OpenSSLException
     */
    public function verify($data, $signature, $signature_alg = OPENSSL_ALGO_SHA256)
    {
        $data = FileUtils::fileToString($data);
        $signature = FileUtils::fileToString($signature);

        $valid = openssl_verify($data, $signature, $this->opensslResource, $signature_alg);

        if (-1 === $valid) {
            throw new OpenSSLException("An error occured while verifying a signature : %s");
        }

        return $valid === 1;
    }

    /**
     * Saves the public key to a file
     *
     * @param string $path The path to the file
     */
    public function saveToFile($path)
    {
        file_put_contents($path, $this->export());
    }
}