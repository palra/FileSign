/**
 * fileselect.js
 *
 * (c) Loïc Payol <contact@loicpayol.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$(function () {
  $(document).on('change', ':file', function () {
    $(this).trigger('fileselect');
  });

  $(':file').on('fileselect', function () {
    var $this = $(this),
      $input = $this.parents('.input-group').find(':text');

    if ($input.length) {
      $input.val($this.get(0).files[0].name);
    }
  });
});