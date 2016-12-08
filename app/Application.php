<?php
/**
 * Application.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Silex\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * Application constructor.
     */
    public function __construct(array $values = array())
    {
        $values = array_merge($values, require __DIR__ . '/parameters.php');
        parent::__construct($values);

        $this->register(new \Silex\Provider\TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../src/App/Resources/views'
        ));

        $this->registerOpenSSL();
        $this->registerControllers();
    }

    private function registerOpenSSL()
    {
        $app = $this;

        $this['public_key'] = function ($app) {
            return \Palra\OpenSSL\PublicKey::fromFilePath($app['public_key.path']);
        };

        $this['private_key'] = $this->protect(function ($passphrase = "") use ($app) {
            return \Palra\OpenSSL\PrivateKey::fromFilePath($app['private_key.path'], $passphrase);
        });
    }

    private function registerControllers()
    {
        $this->get('/', 'App\\Controller\\VerifyController::indexAction')
            ->bind('fs_verify_index');
        $this->post('/', 'App\\Controller\\VerifyController::postAction')
            ->bind('fs_verify_post');
        $this->get('/sign', 'App\\Controller\\SignatureController::indexAction')
            ->bind('fs_sign_index');
        $this->post('/sign', 'App\\Controller\\SignatureController::postAction')
            ->bind('fs_sign_post');
        $this->get('/public_key', 'App\\Controller\\FileSignController::publicKeyDownloadAction')
            ->bind('fs_public_key');
    }
}