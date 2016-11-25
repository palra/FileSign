<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Signature et vérification de fichiers</title>
</head>
<body>
  <h1>Signature et vérification de fichiers</h1>
  <hr>

  <?php
  if(isset($valid)) {
    if($valid == -1) { ?>
        <p>Il y a eu une erreur lors de la validation de la signature.</p>
    <?php } else if($valid == 0) { ?>
        <p><b>Le fichier n'est pas authentique.</b></p>
    <?php } else if($valid == 1) { ?>
        <p>Le fichier est authentique.</p>
    <?php }
  }
  ?>

  <h2>Signer un fichier</h2>
  <form action="/sign" method="POST" enctype="multipart/form-data">
    <label for="file">Fichier</label>
    <input type="file" name="file">
    <br>
    <input type="submit" name="submit" value="Signer">
  </form>

  <h2>Vérifier un fichier</h2>
  <form action="/verify" method="POST" enctype="multipart/form-data">
    <label for="file">Fichier</label>
    <input type="file" name="file">
    <br>
    <label for="sign">Signature</label>
    <input type="file" name="sign">
    <br>
    <input type="submit" name="submit" value="Vérifier">
  </form>
</body>
</html>