<?php

if (!defined('KIRBY')) {
  //  die('no kirby here');
  header("Location: /");
  exit();
}

kirby()->routes(array(
  array(
    'pattern' => 'content-backup',
    'method' => 'POST',
    'action' => function()
    {

      /* get kirby functions */

      $kirby = kirby();
      $site  = $kirby->site();

      /* ---------------------------------------------- */
      /* check if logged in */
      /* ---------------------------------------------- */

      if (!$site->user()) {
        // die('user not logged in');
        header("Location: /");
        exit();
      }

      /* ---------------------------------------------- */
      /* user is logged in */
      /* ---------------------------------------------- */

      else {

        /* ---------------------------------------------- */
        /* check if request is made from the same server */
        /* ---------------------------------------------- */

        if (!preg_match('#^http(s)://(www\.)?' . $_SERVER['SERVER_NAME'] . '.*#i', getenv("HTTP_REFERER"))) {
          // die('domains do not match');
          header("Location: /");
          exit();
        }

        /* ---------------------------------------------- */
        /* check if request is made from POST */
        /* ---------------------------------------------- */

        else {

          if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // die('no post-request');
            header("Location: /");
            exit();
          } else {

            /* ---------------------------------------------- */
            /* check if post-request is 'create' */
            /* ---------------------------------------------- */

            if (isset($_POST['action'])) {

              /* ---------------------------------------------- */
              /* all set clear, start building */
              /* ---------------------------------------------- */

              function backupSize($size)
              {
                $sizes = array(
                  " Bytes",
                  " KB",
                  " MB",
                  " GB",
                  " TB",
                  " PB",
                  " EB",
                  " ZB",
                  " YB"
                );
                if ($size == 0) {
                  return ('n/a');
                } else {
                  return (round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]);
                }
              }

              if ($_POST['action'] == 'create') {
                // die('post-request is "create"');

                function createRandom()
                {
                  $charset = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVQXYZ1234567890';
                  $url     = substr(str_shuffle($charset), 0, 16);
                  return $url;
                }

                $postfix      = createRandom();
                $timestamp    = date('Y-m-d_H-i-s', time());
                $backupFile   = $timestamp . '__' . preg_replace('#\.#', '_', $_SERVER['SERVER_NAME']) . '__' . $postfix . '.zip';
                $backupFolder = 'content/';

                global $fileList;

                class FlxZipArchive extends ZipArchive
                {

                  public function addDir($location, $name)
                  {
                    $this->addEmptyDir($name);
                    $this->addDirDo($location, $name);
                  }

                  public function addDirDo($location, $name)
                  {
                    global $fileList;
                    $name .= '/';
                    $location .= '/';
                    $dir = opendir($location);

                    while ($file = readdir($dir)) {
                      if ($file == '.' || $file == '..')
                        continue;
                      $do = (filetype($location . $file) == 'dir') ? 'addDir' : 'addFile';
                      $this->$do($location . $file, $name . $file);
                      $fileList .= '<li>' . $name . $file . '</li>';
                    }
                  }
                }

                $za  = new FlxZipArchive;
                $res = $za->open('backup/' . $backupFile, ZipArchive::CREATE);

                /* ---------------------------------------------- */
                /* backup was created successfuly */
                /* ---------------------------------------------- */

                if ($res === TRUE) {
                  /* automatically delete old backups */

                  foreach (glob('backup/*.zip') as $file) {
                    unlink($file);
                  }

                  /* create new backup, generate action-links */

                  $za->addDir($backupFolder, basename($backupFolder));
                  $za->close();

                  $sizeBackup = backupSize(filesize('backup/' . $backupFile));

                  $response1 = $response2 = null;

                  $response1 = '<button type="button" onclick="location.href=\'../backup/' . $backupFile . '\';" title="download this backup">Download</button> this backup to your local device.';
                  $response2 = 'Please, <button type="button" id="deleteBackup" class="clear-list" title="delete this backup">delete</button> this backup after download <span>(for security reasons)</span>.';

                  $response = 'Last backup created on : ' . date('F d, Y - H:i:s') . ' <span>(' . $sizeBackup . ')</span>.<br>';
                  $response .= '<ul><li>' . $response1 . '</li>';
                  $response .= '<li>' . $response2 . '</li></ul>';
                  $response .= '<script>$("#backupList").html("<br><i class=\"fa fa-eye fileList\"></i> <button type=\"button\" class=\"fileList\" title=\"show all the files in the backup\">show filelist</button><ul>' . $fileList . '</ul>");</script>';

                  echo $response;
                  exit();
                }

                else {

                  /* ---------------------------------------------- */
                  /* backup was not created successfuly */
                  /* ---------------------------------------------- */

                  echo '<b>Error</b> - the backup file can not be created.';
                  exit();
                }
              }

              /* ---------------------------------------------- */
              /* post-request is 'check' */
              /* ---------------------------------------------- */

              else if ($_POST['action'] == 'check') {
                // die('post-request is "check"');

                $lastBackup = $response1 = $response2 = $pathBackup = $sizeBackup = null;

                foreach (glob('backup/*.zip') as $file) {
                  $lastBackup = date('F d, Y - H:i:s', filemtime($file));
                  $pathBackup = $file;
                  $sizeBackup = backupSize(filesize($file));
                }

                $response1 = '<button type="button" onclick="location.href=\'' . $site->url() . '/' . $pathBackup . '\';" title="download this backup">Download</button> this backup to your local device.';
                $response2 = 'Please, <button type="button" id="deleteBackup" class="clear-list" title="delete this backup">delete</button> this backup after download <span>(for security reasons)</span>.';

                if (empty($lastBackup)) {
                  $lastBackup = '<span>no backup found</span>';
                  $response1  = 'There is no backup available.';
                  $response2  = 'Create a new one first.';
                } else {
                  $sizeBackup = ' <span>(' . $sizeBackup . ')</span>';
                }

                $response = 'Last backup created on : ' . $lastBackup . $sizeBackup . '.<br>';
                $response .= '<ul><li>' . $response1 . '</li>';
                $response .= '<li>' . $response2 . '</li></ul>';

                echo $response;
                exit();
              }

              /* ---------------------------------------------- */
              /* post-request is 'delete' */
              /* ---------------------------------------------- */

              else if ($_POST['action'] == 'delete') {

                foreach (glob('backup/*.zip') as $file) {
                  unlink($file);
                }

                $response = 'The backup is deleted.<br>';
                $response .= '<ul><li>There is no backup available.</li>';
                $response .= '<li>Create a new one first.</li></ul>';

                echo $response;
                exit();
              }

              /* ---------------------------------------------- */
              /* post-request was not valid */
              /* ---------------------------------------------- */

              else {
                // die('post-request is not "create"');
                die();
              }
            }

            /* ---------------------------------------------- */
            /* post-request did not contain an 'action' */
            /* ---------------------------------------------- */

            else {
              // die('post-request did not contain an "action"');
              die();
            }
          }
        }

        /* ---------------------------------------------- */

      }
    }
  )
));

?>