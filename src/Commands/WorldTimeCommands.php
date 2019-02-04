<?php

namespace Drupal\worldtime\Commands;

use Drush\Commands\DrushCommands;
use Symfony\Component\Filesystem\Filesystem;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class WorldTimeCommands extends DrushCommands {

  /**
   * Download and install the jClocksGMT plugin for World Time Clock Widget.
   *
   * @param string $path
   *   Optional. A path where to install the jClocksGMT plugin.
   *   If omitted Drush will use the default location.
   *
   * @command worldtime:plugin
   * @aliases worldtimeplugin,wtcwplugin,worldtime-plugin
   */
  public function download($path = '') {
    $fs = new Filesystem();

    if (empty($path)) {
      $path = drush_get_context('DRUSH_DRUPAL_ROOT') . '/libraries/jclocksgmt';
    }

    // Create path if it doesn't exist.
    // Exit with a message otherwise.
    if (!$fs->exists($path)) {
      $fs->mkdir($path);
    }
    else {
      $this->logger()->notice(dt('jClocksGMT is already present at @path. No download required.', ['@path' => $path]));
      return;
    }

    // Load the worldtime defined library.
    if ($library = \Drupal::service('library.discovery')->getLibraryByName('worldtime', 'jclocksgmt')) {

      // Download the file.
      $client = new Client();
      $destination = tempnam(sys_get_temp_dir(), 'jclocksgmt-tmp');
      try {
        $client->get($library['remote'] . '/archive/master.zip', ['save_to' => $destination]);
      }
      catch (RequestException $e) {
        // Remove the directory.
        $fs->remove($path);
        $this->logger()->error(dt('Drush was unable to download the jClocksGMT library from @remote. @exception', [
          '@remote' => $library['remote'] . '/archive/master.zip',
          '@exception' => $e->getMessage(),
        ], 'error'));
        return;
      }

      // Move downloaded file.
      $fs->rename($destination, $path . '/jclocksgmt.zip');

      // Unzip the file.
      $zip = new \ZipArchive();
      $res = $zip->open($path . '/jclocksgmt.zip');
      if ($res === TRUE) {
        $zip->extractTo($path);
        $zip->close();
      }
      else {
        // Remove the directory if unzip fails and exit.
        $fs->remove($path);
        $this->logger()->error(dt('Error: unable to unzip jClocksGMT file.', [], 'error'));
        return;
      }

      // Remove the downloaded zip file.
      $fs->remove($path . '/jclocksgmt.zip');

      // Move the file.
      $fs->mirror($path . '/jclocksgmt-master', $path, NULL, ['override' => TRUE]);
      $fs->remove($path . '/jclocksgmt-master');

      // Success.
      $this->logger()->notice(dt('The jClocksGMT library has been successfully downloaded to @path.', [
        '@path' => $path,
      ], 'success'));
    }
    else {
      $this->logger()->error(dt('Drush was unable to load the jClocksGMT library'));
    }
  }

}
