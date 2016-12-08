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

use Palra\OpenSSL\PublicKey;
use Silex\Application;
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
}