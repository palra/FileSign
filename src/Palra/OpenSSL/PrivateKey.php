<?php
/**
 * PrivateKey.php
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
 * Wraps an OpenSSL private key resource
 * @author Loïc Payol <loicpayol@gmail.com>
 */
class PrivateKey
{
    const KEYTYPE_RSA = OPENSSL_KEYTYPE_RSA;
    const KEYTYPE_DSA = OPENSSL_KEYTYPE_DSA;
    const KEYTYPE_DH = OPENSSL_KEYTYPE_DH;
    const KEYTYPE_EC = OPENSSL_KEYTYPE_EC;

    /** @var resource */
    private $opensslResource;

    /**
     * PrivateKey constructor.
     * @param resource $resource The OpenSSL private key resource
     * @throws OpenSSLException
     */
    public function __construct($resource)
    {
        $this->opensslResource = $resource;
    }

    /**
     * Builds a PrivateKey instance from a string representation of it, with a
     * given and optional passphrase
     *
     * @param string $string The string representation of the passphrase
     * @param string $passphrase The optional passphrase
     * @return PrivateKey The PrivateKey instance
     */
    public static function fromString($string, $passphrase = null)
    {
        $resource = openssl_pkey_get_private($string, $passphrase);

        if (false === $resource) {
            throw new OpenSSLException("An error occured while reading a private key from a string : %s");
        }

        return new self($resource);
    }

    /**
     * Generates a new private key, and wraps it in a PrivateKey instance
     *
     * @param int $size The size in bits of the private key
     * @return PrivateKey The PrivateKey instance
     */
    public static function generate($size = 1024)
    {
        $res = openssl_pkey_new(array(
            'private_key_bits' => (int)$size
        ));

        if (false === $res) {
            throw new OpenSSLException('An error occured while generating a new private key : %s');
        }

        return new self($res);
    }

    /**
     * Builds a PrivateKey instance from a private key file, with a given
     * and optional passphrase.
     *
     * @param string $path The path to the private key
     * @param string $passphrase The optional passphrase
     * @throws OpenSSLException
     * @return PrivateKey The private key
     */
    public static function fromFilePath($path, $passphrase = null)
    {
        $realpath = realpath($path);

        if (false === $realpath) {
            throw new FileNotFound($path);
        }

        $resource = openssl_pkey_get_private(
            sprintf('file://%s', $realpath),
            $passphrase
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
     * Exports a string representation of the private key, optionally encrypted
     * with a given passphrase
     * @param string $passphrase The optional passphrase
     * @return string The string representation of the private key
     * @throws OpenSSLException
     */
    public function export($passphrase = null)
    {
        if (!empty($passphrase)) {
            $count = strlen($passphrase);
            if ($count < 4 || $count > 1023) {
                throw new \LogicException("The passphrase length must be between 4 and 1023");
            }
        }

        $res = openssl_pkey_export($this->opensslResource, $newKey, $passphrase);
        if (false === $res) {
            throw new OpenSSLException("An error occured while exporting the private key : %s");
        }

        return $newKey;
    }

    /**
     * @return resource
     */
    public function getOpensslResource()
    {
        return $this->opensslResource;
    }

    /**
     * Saves the private key to a file, and optionally encrypt with a passphrase
     *
     * @param string $path The path to the file
     * @param string $passphrase The optional passphrase
     */
    public function saveToFile($path, $passphrase = null)
    {
        file_put_contents($path, $this->export($passphrase));
    }

    /**
     * Create a signature of a given data.
     *
     * @param string|\SplFileInfo $data The data to sign
     * @param int $signature_alg See {@link http://php.net/manual/en/function.openssl-verify.php}
     * @return string The signature
     * @throws OpenSSLException
     */
    public function sign($data, $signature_alg = OPENSSL_ALGO_SHA256)
    {
        if ($data instanceof \SplFileInfo) {
            $data = file_get_contents($data->getPathname());
        }

        if (!is_string($data)) {
            throw new \LogicException(
                sprintf(
                    '$data should be a string or a \SplFileInfo, %s given',
                    gettype($data)
                )
            );
        }

        $res = openssl_sign($data, $signature, $this->opensslResource, $signature_alg);
        if (false === $res) {
            throw new OpenSSLException("An error occured while signing : %s");
        }

        return $signature;
    }

    /**
     * Returns the PublicKey instance associated with this private key
     *
     * @return PublicKey The PublicKey instance
     * @throws OpenSSLException
     */
    public function getPublicKey()
    {
        $res = openssl_pkey_get_details($this->opensslResource);
        if (false === $res) {
            throw new OpenSSLException("An error occured while fetching public key from private key : %s");
        }

        return PublicKey::fromString($res['key']);
    }
}