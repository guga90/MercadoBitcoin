<?php

namespace App;

class MercadoBitcoin {
    /*
      Nome: Mcoins
      Identificador: 9adf12a5537f0470a1016bec2defb690
      Segredo: d093d5f2ac14cbac2d4c45966e02761b9525724a5787b8c0b371a1c861215ceb
     */

    protected $apiId = null;
    protected $apiKey = null;
    protected $id_account = null;
    protected $urlBase = "https://www.mercadobitcoin.net";

    public function __construct($apiId = "", $apiKey = "", $id_account = 0) {
        $this->apiKey = $apiKey;
        $this->apiId = $apiId;
        $this->id_account = $id_account;
    }

    //https://www.mercadobitcoin.com.br/api-doc/
    public function ticker($currency = 'BTC') {
        $apiURL = "/{$currency}/ticker";
        return $this->initCurl($apiURL);
    }

    //https://www.mercadobitcoin.com.br/api-doc/
    public function bookOrders($currency = 'BTC') {
        $apiURL = "/{$currency}/orderbook";
        return $this->initCurl($apiURL);
    }

    //https://www.mercadobitcoin.com.br/api-doc/
    public function trades(
    $currency = 'BTC', $hours = 24, $since = ""
    ) {
        $timeZone = new \DateTimeZone('Brazil/East');

        $start_time = new \DateTime('now');
        $start_time->format(\DateTime::ATOM);
        $start_time->setTimezone($timeZone);
        $start_time->modify('-' . $hours . ' hour');
        $start_time = $start_time->getTimestamp();

        $end_time = new \DateTime('now');
        $end_time->format(\DateTime::ATOM);
        $end_time->setTimezone($timeZone);
        $end_time = $end_time->getTimestamp();

        if (!empty($since)) {
            $apiURL = "/{$currency}/trades?since={$since}";
        } else {
            $apiURL = "/{$currency}/trades/?{$start_time}/{$end_time}";
        }

        return $this->initCurl($apiURL);
    }

    //https://www.mercadobitcoin.com.br/api-doc/
    public function summary($currency = 'BTC') {
        $apiURL = "/{$currency}/day-summary/" . date('Y/m/d', strtotime("-1 days"));
        return $this->initCurl($apiURL);
    }

    private function initCurl($url = '', $apiKeyRequired = false, $fields = [], $method = 'GET') {

        $curl = curl_init();

        $header = [
            'Content-Type: application/json'
        ];

        $apiPath = '/api';

        $postFields = '';
        if ($apiKeyRequired) {

            $header = [
                'Content-Type: application/x-www-form-urlencoded'
            ];

            $apiPath = '/tapi/v3/';

            foreach (array_keys($fields) as $key) {

                if (is_array($fields[$key])) {
                    $fields[$key] = json_encode($fields[$key]);
                } else {
                    $fields[$key] = urlencode($fields[$key]);
                }
            }

            $postFields = http_build_query($fields);

            $message = $apiPath . "?" . $postFields;

            $header[] = "TAPI-ID:" . $this->apiId;
            $header[] = "TAPI-MAC:" . $this->signMessage($message);
        }

        $options = [
            CURLOPT_URL => $this->urlBase . $apiPath . $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $header
        ];

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        $err = curl_error($curl);
        curl_close($curl);

        ob_start();
        print_r(date('Y-m-d H:i:s') . ' = ' . $response);
        $sTXT = ob_get_contents();
        $hArq = fopen('logs/logs.txt', 'a+');
        fwrite($hArq, $sTXT . "\n\n");
        fclose($hArq);
        ob_end_clean();

        return $err ? "cURL Error #: {$err}" : json_decode($response);
    }

    private function getTapiNonce() {

        $modelAccount = new Account();
        $tapiNonce = $modelAccount->getTapiNonce($this->id_account);

        return $tapiNonce;
        
        /* list($usec, $sec) = explode(" ", microtime());
          return (int) ((float) $usec + (float) $sec); */
        
    }

    private function signMessage($message) {
        $signedMessage = hash_hmac('sha512', $message, $this->apiKey);
        return $signedMessage;
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function listOrders($coin_pair = 'BRLBTC', $has_fills = "", $status_list = array()) {

        $tapi_method = 'list_orders';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'has_fills', 'status_list');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function listSystemMessages($level = 'INFO') {

        $tapi_method = 'list_system_messages';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'level');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function getAccountInfo() {

        $tapi_method = 'get_account_info';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function getOrder($order_id = 0, $coin_pair = 'BRLBTC') {

        $tapi_method = 'get_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'order_id');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function listOrderbook($coin_pair = 'BRLBTC') {

        $tapi_method = 'list_orderbook';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function placeBuyOrder($coin_pair = 'BRLBTC', $quantity = 0, $limit_price = 0) {

        $tapi_method = 'place_buy_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'quantity', 'limit_price');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function placeSellOrder($coin_pair = 'BRLBTC', $quantity = 0, $limit_price = 0) {

        $tapi_method = 'place_sell_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'quantity', 'limit_price');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function placeMarketBuyOrder($coin_pair = 'BRLBTC', $cost = 0) {

        $tapi_method = 'place_market_buy_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'cost');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function placeMarketSellOrder($coin_pair = 'BRLBTC', $quantity = 0) {

        $tapi_method = 'place_market_sell_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'quantity');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function cancelOrder($coin_pair = 'BRLBTC', $order_id = "") {

        $tapi_method = 'cancel_order';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin_pair', 'order_id');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function getWithdrawal($coin = 'BTC', $withdrawal_id = "") {

        $tapi_method = 'get_withdrawal';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin', 'withdrawal_id');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

    // https://www.mercadobitcoin.com.br/trade-api
    public function withdrawCoin($coin = 'BTC', $description = "", $address = "", $quantity = 0, $tx_fee = 0, $tx_aggregate = "", $via_blockchain = "false") {

        $tapi_method = 'withdraw_coin';
        $tapi_nonce = $this->getTapiNonce();

        $fields = compact('tapi_method', 'tapi_nonce', 'coin', 'description', 'address', 'quantity', 'tx_fee', 'tx_aggregate', 'via_blockchain');

        $apiURL = '';
        $apiKeyRequired = true;

        return $this->initCurl($apiURL, $apiKeyRequired, $fields, 'POST');
    }

}
