<?php
/**
 * SignatureController.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use Palra\OpenSSL\Exception\OpenSSLException;
use Palra\OpenSSL\PrivateKey;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SignatureController
{
    public function indexAction(Application $app)
    {
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        return $twig->render('sign.html.twig');
    }

    public function postAction(Request $request, Application $app)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $passphrase = $request->request->get('passphrase', '');

        try {
            /** @var PrivateKey $pkey */
            $pkey = $app['private_key']($passphrase);
            $signature = $pkey->sign($file);
        } catch (OpenSSLException $e) {
            /** @var \Twig_Environment $twig */
            $twig = $app['twig'];
            return $twig->render('sign.html.twig', array(
                'error' => $e
            ));
        }

        $res = new Response();
        $res->setContent($signature);
        $res->headers->set('Content-Type', 'application/octet-stream');
        $res->headers->set('Content-Disposition', sprintf('attachment; filename="%s.sha256.dat"', $file->getClientOriginalName()));

        return $res;
    }
}