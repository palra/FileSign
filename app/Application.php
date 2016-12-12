<?php
/**
 * Application.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Palra\OpenSSL\PrivateKey;
use Palra\OpenSSL\PublicKey;
use Silex\Application as BaseApplication;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\Loader\YamlFileLoader;
use Symfony\Component\Translation\Translator;

class Application extends BaseApplication
{
    /**
     * Application constructor.
     */
    public function __construct(array $values = array())
    {
        $values = array_merge($values, require __DIR__ . '/parameters.php');
        parent::__construct($values);


        $this->register(new TwigServiceProvider(), array(
            'twig.path' => __DIR__ . '/../src/App/Resources/views'
        ));
        $this->extendTwig();

        $this->register(new Silex\Provider\SessionServiceProvider());

        $this->register(new LocaleServiceProvider());
        $this->register(new TranslationServiceProvider(), array(
            'locale_fallbacks' => array('fr_FR')
        ));

        $this->registerOpenSSL();
        $this->registerControllers();
        $this->configureTranslator();
    }

    private function extendTwig()
    {
        $this->extend('twig', function (\Twig_Environment $twig, Application $app) {
            $twig->addFunction(new Twig_SimpleFunction('asset', function ($asset) use ($app) {
                /** @var \Symfony\Component\HttpFoundation\RequestStack $stack */
                $stack = $app['request_stack'];
                $request = $stack->getCurrentRequest();
                return $request->getBasePath() . $asset;
            }));

            return $twig;
        });
    }

    private function registerOpenSSL()
    {
        $app = $this;

        $this['public_key'] = function ($app) {
            return PublicKey::fromFilePath($app['public_key.path']);
        };

        $this['private_key'] = $this->protect(function ($passphrase = "") use ($app) {
            return PrivateKey::fromFilePath($app['private_key.path'], $passphrase);
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

    private function configureTranslator()
    {
        $this->extend('translator', function (Translator $translator) {
            $translator->addLoader('yaml', new YamlFileLoader());

            $list = glob(__DIR__ . '/../src/App/Resources/translations/*.yml');
            foreach ($list as $file) {
                $file = new SplFileInfo(realpath($file));
                $translator->addResource('yaml', $file->getPathname(), $file->getBasename('.' . $file->getExtension()));
            }

            return $translator;
        });

        $this->before(function (Request $request, Application $app) {
            if ($request->hasSession()) {
                if ($locale = $request->query->get('_locale', false)) {
                    $request->getSession()->set('_locale', $locale);

                    // Redirect to the same URI and removing _locale parameter
                    $path = str_replace("_locale=$locale", '', $request->getUri());
                    $path = str_replace("?&", "?", $path);
                    $path = preg_replace('/\?$/', '', $path);

                    return $app->redirect($path);
                }

                $app['locale'] = $request->getSession()->get('_locale', $app['locale']);
                $request->setLocale($app['locale']);

                /** @var Translator $trans */
                $trans = $app['translator'];
                $trans->setLocale($app['locale']);
            }
        });
    }
}