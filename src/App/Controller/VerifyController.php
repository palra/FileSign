<?php
/**
 * VerifyController.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;


use Palra\OpenSSL\Exception\OpenSSLException;
use Palra\OpenSSL\PublicKey;
use Silex\Application;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class VerifyController
{
    public function indexAction(Application $app)
    {
        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        return $twig->render('verify.html.twig');
    }

    public function postAction(Request $request, Application $app)
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        /** @var UploadedFile $sign */
        $sign = $request->files->get('sign');

        try {
            /** @var PublicKey $pkey */
            $pkey = $app['public_key'];
            $valid = $pkey->verify($file, $sign);
        } catch (OpenSSLException $e) {
            /** @var \Twig_Environment $twig */
            $twig = $app['twig'];
            return $twig->render('verify.html.twig', array(
                'error' => $e
            ));
        }

        /** @var \Twig_Environment $twig */
        $twig = $app['twig'];
        return $twig->render('verify.html.twig', array(
            'valid' => $valid
        ));
    }
}