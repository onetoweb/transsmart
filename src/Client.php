<?php

namespace Onetoweb\Transsmart;

use Onetoweb\Transsmart\Exception\LoginException;
use Onetoweb\Transsmart\Exception\RequestException;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Exception\RequestException as GuzzleRequestException;
use GuzzleHttp\Psr7\Response;

/**
 * Transsmart Client API v2
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @license MIT
 */
class Client
{
    /**
     * @var string
     */
    private $username;
    
    /**
     * @var string
     */
    private $password;
    
    /**
     * @var string
     */
    private $account;
    
    /**
     * @var Token
     */
    private $token;
    
    /**
     * @var string
     */
    private $apiLocation = 'https://api.transsmart.com';
    
    /**
     * @var string
     */
    private $apiTestLocation = 'https://accept-api.transsmart.com';
        
    /**
     * @param string $username
     * @param string $password
     * @param string $account
     * @param bool $testmode = false (optional)
     */
    public function __construct($username, $password, $account, $testmode = false)
    {
        $this->username = $username;
        $this->password = $password;
        $this->account = $account;
        
        $this->setTestMode($testmode);
    }
    
    /**
     * @return string
     */
    private function getApiLocation()
    {
        return ($this->testMode ? $this->apiTestLocation : $this->apiLocation);
    }
    
    /**
     * @param bool $testmode = false (optional)
     */
    public function setTestMode($testmode = false)
    {
        $this->testMode = $testmode;
    }
    
    /**
     * @return Token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    /**
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * @throws LoginException
     */
    private function login()
    {
        try {
            
            $client = new GuzzleClient([
                'auth' => [$this->username, $this->password],
                'verify' => false
            ]);
            
            $result = $client->request('GET', "{$this->getApiLocation()}/login");
            
        } catch (GuzzleRequestException $requestException) {
            
            if ($requestException->hasResponse()) {
                
                $error = (string) $e->getResponse()->getBody()->getContents();
                
                throw new LoginException($error);
            }
            
        }
        
        $contents = json_decode($result->getBody()->getContents());
        
        $this->setToken(new Token($contents->token));
    }
    
    /**
     * Send a GET request
     *
     * @param string $endpoint
     * 
     * @throws RequestException
     * 
     * @return array
     */
    private function get($endpoint)
    {
        if ($this->getToken() == null or $this->getToken()->hasExpired()) {
            $this->login();
        }
        
        $endpoint = $this->getApiLocation() . $endpoint;
        
        try {
            
            $client = new GuzzleClient();
            
            $result = $client->request('get', $endpoint, [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer {$this->getToken()->getToken()}" 
                ]
            ]);
            
            $contents = $result->getBody()->getContents();
            
        } catch (GuzzleRequestException $requestException) {
            
            if ($requestException->hasResponse()) {
                
                $error = (string) $requestException->getResponse()->getBody()->getContents();
                
                throw new RequestException($error);
            }
            
            throw new RequestException($requestException->getMessage());
        }
        
        return json_decode($contents, true);
    }
    
    /**
     * Send a POST request
     *
     * @param string $endpoint
     * @param array $data
     *
     * @throws RequestException
     * 
     * @return array
     */
    private function post($endpoint, $data)
    {
        if ($this->getToken() == null or $this->getToken()->hasExpired()) {
            $this->login();
        }
        
        $endpoint = $this->getApiLocation() . $endpoint;
        
        try {
            
            $client = new GuzzleClient();
            
            $result = $client->request('POST', $endpoint, [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer {$this->getToken()->getToken()}",
                    'Content-Type' => 'application/json;charset=UTF-8'
                ],
                RequestOptions::JSON => $data,
            ]);
            
            $contents = $result->getBody()->getContents();
            
        } catch (GuzzleRequestException $requestException) {
            
            if ($requestException->hasResponse()) {
                
                $error = (string) $requestException->getResponse()->getBody()->getContents();
                
                throw new RequestException($error);
            }
            
            throw new RequestException($requestException->getMessage());
        }
        
        return json_decode($contents, true);
    }
    
    /**
     * @return array $data
     * @return string $action = 'BOOK' (optional)
     * 
     * @return array
     */
    public function bookShipment($data, $action = 'BOOK')
    {
        return $this->post("/v2/shipments/{$this->account}/$action?rawJob=true", $data);
    }
    
    /**
     * @return array
     */
    public function getServiceLevelTimes()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/serviceLevelTimes");
    }
    
    /**
     * @return array 
     */
    public function getCarriers()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/carriers");
    }
    
    /**
     * @return array
     */
    public function getAddresses()
    {
        return $this->get("/v2/addresses/{$this->account}");
    }
}