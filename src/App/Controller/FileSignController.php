<?php
/**
 * FileSignController.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use Palra\OpenSSL\PrivateKey;
use Palra\OpenSSL\PublicKey;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FileSignController
 * @author Loïc Payol <loicpayol@gmail.com>
 */
class FileSignController
{
    public function publicKeyDownloadAction(Application $app)
    {
        /** @var PublicKey $pkey */
        $pkey = $app['public_key'];

        $res = new Response();
        $res->setContent($pkey->export());
        $res->headers->set('Content-Type', 'application/octet-stream');
        $res->headers->set('Content-Disposition', 'attachment; filename="public_key.pem"');

        return $res;
    }

    public function generateIndexAction(Application $app)
    {
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        return $twig->render('generate.html.twig');
    }

    public function generatePostAction(Request $request, Application $app)
    {
        $passphrase = $request->request->get('passphrase');
        $confirm = $request->request->get('confirm');

        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];

        if ($passphrase !== $confirm) {
            return $twig->render('generate.html.twig', array(
                'error' => 'Les deux phrases de passe ne sont pas identiques'
            ));
        }

        // Génération de la clé privée
        $privateKey = PrivateKey::generate(8192);
        $publicKey = $privateKey->getPublicKey();

        $zip = new \ZipArchive();
        $name = tempnam(__DIR__ . '/../../../app/', 'pkey') . '-' . time();
        if (false === $zip->open($name, \ZipArchive::CREATE)) {
            return $twig->render('generate.html.twig', array(
                'error' => 'Erreur interne'
            ));
        }

        $zip->addFromString('public_key.pem', $publicKey->export());
        $zip->addFromString('private_key.pem', $privateKey->export($passphrase));
        $zip->close();

        $res = new Response();
        $res->setContent(file_get_contents($name));
        $res->headers->set('Content-Type', 'application/zip');
        $res->headers->set('Content-Disposition', 'attachment; filename="p_keys.zip"');

        return $res;
    }
}