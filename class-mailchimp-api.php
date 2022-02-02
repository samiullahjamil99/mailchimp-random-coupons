<?php
class Mailchimp_API {
  private $apikey;
  private $client;
  protected static $_instance = null;
  public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}
  public function __construct() {
    $mailchimp_options = get_option('mrc_options');
    $this->apikey = $mailchimp_options['mrc_field_apikey'];
    $this->client = new MailchimpMarketing\ApiClient();
    $this->client->setConfig([
      'apiKey' => $this->apikey,
      'server' => substr($this->apikey,strpos($this->apikey,'-')+1),
    ]);
  }
  public function get_lists() {
    $response = $this->client->lists->getAllLists();
    return $response->lists;
  }
  public function get_list_webhooks($listid) {
    $list_webhooks = $this->client->lists->getListWebhooks($listid);
    return $list_webhooks->webhooks;
  }
  public function get_all_webhooks() {
    $webhooks = array();
    $lists = $this->get_lists();
    foreach($lists as $list) {
      $list_webhooks = $this->get_list_webhooks($list->id);
      $webhooks = array_merge($webhooks,$list_webhooks);
    }
    return $webhooks;
  }
  public function create_subscribe_webhook($listid, $url) {
    $webhooks = $this->get_list_webhooks($listid);
    $webhook_found = false;
    foreach($webhooks as $webhook) {
      if ($webhook->url === $url)
        $webhook_found = true;
    }
    if (!$webhook_found) {
      $response = $this->client->lists->createListWebhook($listid,array(
        'url' => $url,
        'events' => array(
          'subscribe' => true,
          'unsubscribe' => false,
          'profile' => false,
          'upmail' => false,
        ),
        'sources' => array(
          'user' => true,
          'admin' => true,
          'api' => false,
        ),
      ));
    } else {
      $response = false;
    }
    return $response;
  }
  public function delete_subscribe_webhook($listid, $webhookid) {
    $response = $this->client->lists->deleteListWebhook($listid, $webhookid);
    return $response;
  }
}
