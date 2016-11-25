<?php
/**
 * index.php
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;

$app->get('/', function() {
    ob_start();
    require_once __DIR__ . '/form.php';
    $out = ob_get_contents();
    ob_end_clean();

    return $out;
});

$app->post('/sign', function (Request $request) use ($app) {
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
    $file = $request->files->get('file');
    $text = file_get_contents($file->getPathname());

    $pkey = openssl_pkey_get_private(sprintf('file://%s', realpath('../app/private_key.pem')));
    openssl_sign($text, $signature, $pkey, OPENSSL_ALGO_SHA256);
    openssl_free_key($pkey);

    $res = new \Symfony\Component\HttpFoundation\Response();
    $res->setContent($signature);
    $res->headers->set('Content-Type', 'application/octet-stream');
    $res->headers->set('Content-Type', 'attachment; filename="signature.dat"');

    return $res;
});

$app->post('/verify', function(Request $request) use ($app) {
    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
    $file = $request->files->get('file');

    /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $sign */
    $sign = $request->files->get('sign');


    if($file === null || $sign == null) {
        return $app->redirect('/');
    }

    $text = file_get_contents($file->getPathname());
    $sign = file_get_contents($sign->getPathname());

    $pkey = openssl_pkey_get_private(sprintf('file://%s', realpath('../app/public_key.pem')));
    $valid = openssl_verify($text, $sign, $pkey, OPENSSL_ALGO_SHA256);

    ob_start();
    require_once __DIR__ . '/form.php';
    $out = ob_get_contents();
    ob_end_clean();

    return $out;
});

$app->run();