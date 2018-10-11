<?php

namespace Drupal\dp_reward;

use Google_Client;

class GoogleAuth {

  public function googleClient () {

    $config = \Drupal::config('dp_reward.settings');

    $client = new Google_Client();
    $client->setClientId($config->get('google_client_id'));
    $client->setClientSecret($config->get('google_client_secret'));
    $client->setRedirectUri($config->get('google_redirect_uri'));
    $client->addScope('https://www.googleapis.com/auth/adsense.readonly');
    $client->setAccessType('offline');

    return $client;
  }

  public function getToken($client) {

    $config = \Drupal::config('dp_reward.settings');

    unset($_SESSION['access_token']);

    // Configure token storage on disk.
    // If you want to store refresh tokens in a local disk file, set this to true.
    define('STORE_ON_DISK', TRUE, TRUE);
    define('TOKEN_FILENAME', 'tokens.dat', TRUE);


    // If we're logging out we just need to clear our local access token.
    // Note that this only logs you out of the session. If STORE_ON_DISK is
    // enabled and you want to remove stored data, delete the file.
    if (isset($_REQUEST['logout'])) {
      unset($_SESSION['access_token']);
    }

    // If we have a code back from the OAuth 2.0 flow, we need to exchange that
    // with the authenticate() function. We store the resultant access token
    // bundle in the session (and disk, if enabled), and redirect to this page.
    if (isset($_GET['code'])) {
      $client->authenticate($_GET['code']);
      // Note that "getAccessToken" actually retrieves both the access and refresh
      // tokens, assuming both are available.
      $_SESSION['access_token'] = $client->getAccessToken();
      if (STORE_ON_DISK) {
        file_put_contents(TOKEN_FILENAME, serialize($_SESSION['access_token']));
      }
      $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
      header('Location: ' . filter_var($config->get('google_redirect_uri'), FILTER_SANITIZE_URL));
      exit;
    }

    // If we have an access token, we can make requests, else we generate an
    // authentication URL.
    if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
      $client->setAccessToken($_SESSION['access_token']);
    }
    else {
      if (STORE_ON_DISK && file_exists(TOKEN_FILENAME) &&
        filesize(TOKEN_FILENAME) > 0 ) {
        // Note that "setAccessToken" actually sets both the access and refresh token,
        // assuming both were saved.
        $client->setAccessToken(unserialize(file_get_contents(TOKEN_FILENAME)));
        $_SESSION['access_token'] = $client->getAccessToken();
      }
      else {
        // If we're doing disk storage, generate a URL that forces user approval.
        // This is the only way to guarantee we get back a refresh token.
        if (STORE_ON_DISK) {
          $client->setApprovalPrompt('force');
        }
        $authUrl = $client->createAuthUrl();
      }
    }

    if (isset($authUrl)) {
      print '<div class="reward-google-auth">';

      $current_user = \Drupal::currentUser();
      $roles = $current_user->getRoles();
      if (in_array('administrator', $roles)) {
        print '<a class="reward-google-auth__link" href="' . $authUrl . '">Get Authentication!</a>';
      }
      else {
        print '<div class="reward-google-auth__info">Error! Please, tell to site administrator about it</div>';
      }
      print '</div>';

    }
    else {
      //      print '<a class="logout" href="?logout">Logout</a>';

    };

    if ($client->getAccessToken()) {
      $accessToken = $client->getAccessToken()['access_token'];

      return $accessToken;
    }

    else {
      return NULL;
    }
  }

  public function checkToken($accessToken) {

    $token_info_url = 'https://www.googleapis.com/oauth2/v1/tokeninfo?access_token='.$accessToken;
    $token_info = file_get_contents($token_info_url);

    return ($token_info) ? TRUE : FALSE;
  }

}
