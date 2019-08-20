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
     * Get Api location
     * 
     * @return string
     */
    private function getApiLocation()
    {
        return ($this->testMode ? $this->apiTestLocation : $this->apiLocation);
    }
    
    /**
     * Set test mode
     * 
     * @param bool $testmode = false (optional)
     */
    public function setTestMode($testmode = false)
    {
        $this->testMode = $testmode;
    }
    
    /**
     * Set Token
     * 
     * @return Token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
    
    /**
     * Get Token
     * 
     * @return Token
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Login to get a Token
     * 
     * @throws LoginException
     * 
     * @return Token
     */
    public function login()
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
        
        return $this->getToken();
    }
    
    /**
     * Send a GET request
     *
     * @param string $endpoint
     * 
     * @return array
     */
    private function get($endpoint)
    {
        return $this->request('GET', $endpoint);
    }
    
    /**
     * Send a POST request
     *
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     */
    private function post($endpoint, $data)
    {
        return $this->request('POST', $endpoint, $data);
    }
    
    /**
     * Send a PUT request
     *
     * @param string $endpoint
     * @param array $data
     *
     * @return array
     */
    private function put($endpoint, $data)
    {
        return $this->request('PUT', $endpoint, $data);
    }
    
    /**
     * Send a DELETE request
     *
     * @param string $endpoint
     *
     * @return array
     */
    private function delete($endpoint)
    {
        return $this->request('DELETE', $endpoint);
    }
    
    /**
     * Send request
     * 
     * @param string $method = 'GET'
     * @param string $endpoint
     * @param array $data = null (optional)
     *
     * @throws RequestException
     *
     * @return array
     */
    private function request($method = 'GET', $endpoint, $data = null)
    {
        if ($this->getToken() == null or $this->getToken()->hasExpired()) {
            $this->login();
        }
        
        $endpoint = $this->getApiLocation() . $endpoint;
        
        try {
            
            $client = new GuzzleClient();
            
            $options =  [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer {$this->getToken()->getToken()}"
                ]
            ];
            
            if(in_array($method, ['POST', 'PUT'])) {
                
                $options[RequestOptions::JSON] = $data;
                
            }
            
            $result = $client->request($method, $endpoint, $options);
            
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
     * Build http query
     * 
     * @param array $parameters = []
     * 
     * @return string
     */
    private function buildQuery($parameters = [])
    {
        if(count($parameters) > 0) {
            return '?'.http_build_query($parameters);
        }
        
        return '';
    }
    
    /**
     * Book shipment
     * 
     * @see https://devdocs.transsmart.com/#_2_1_shipment_booking
     * 
     * @param array $data
     * @param string $action = 'BOOK' (optional)
     * 
     * @return array
     */
    public function bookShipment($data, $action = 'BOOK')
    {
        return $this->post("/v2/shipments/{$this->account}/$action?rawJob=true", $data);
    }
    
    /**
     * Retrieve shipment
     * 
     * @see https://devdocs.transsmart.com/#_2_2_shipment_retrieval
     * 
     * @param string $reference
     *
     * @return array
     */
    public function retrieveShipment($reference)
    {
        return $this->get("/v2/shipments/{$this->account}/$reference");
    }
    
    /**
     * Retrieve shipments
     * 
     * @see https://devdocs.transsmart.com/#_multiple_shipments_retrieval
     * 
     * @param array $parameters = []
     * 
     * @return array
     */
    public function retrieveShipments($parameters = [])
    {
        return $this->get("/v2/shipments/{$this->account}".$this->buildQuery($parameters));
    }
    
    /**
     * Delete shipment
     * 
     * @see https://devdocs.transsmart.com/#_2_3_shipment_deletion
     * 
     * @param string $reference
     *
     * @return array
     */
    public function deleteShipment($reference)
    {
        return $this->delete("/v2/shipments/{$this->account}/$reference");
    }
    
    /**
     * Get shipment manifest list
     * 
     * @see https://devdocs.transsmart.com/#_retrieve_list
     * 
     * @param array $parameters = []
     *
     * @return array
     */
    public function getShipmentManifestList($parameters = [])
    {
         return $this->get("/v2/shipments/{$this->account}/manifest/list".$this->buildQuery($parameters));
    }
    
    /**
     * Manifest shipments
     * 
     * @see https://devdocs.transsmart.com/#_2_4_manifest_shipments
     * 
     * @param array $parameters = []
     *
     * @return array
     */
    public function manifestShipments($parameters = [])
    {
        return $this->get("/v2/shipments/{$this->account}/manifest".$this->buildQuery($parameters));
    }
    
    /**
     * Calculate rates
     *
     * @see https://devdocs.transsmart.com/#_3_0_rates_calculation
     *
     * @param array $data
     * @param array $parameters = []
     *
     * @return array
     */
    public function calculateRates($data, $parameters = [])
    {
        return $this->post("/v2/rates/{$this->account}".$this->buildQuery($parameters), $data);
    }
    
    /**
     * Print document
     *
     * @see https://devdocs.transsmart.com/#_4_0_document_printing
     *
     * @param string $reference
     *
     * @return array
     */
    public function printDocument($reference)
    {
        return $this->get("/v2/prints/{$this->account}/$reference?rawJob=true");
    }
    
    /**
     * Get shipment status
     *
     * @see https://devdocs.transsmart.com/#_5_1_for_a_single_shipment
     *
     * @param string $reference
     *
     * @return array
     */
    public function getShipmentStatus($reference)
    {
        return $this->get("/v2/statuses/{$this->account}/shipments/$reference");
    }
    
    /**
     * Get shipments statuses
     *
     * @see https://devdocs.transsmart.com/#_5_2_for_multiple_shipments
     *
     * @param array $parameters
     *
     * @return array
     */
    public function getShipmentsStatuses($parameters = [])
    {
        return $this->get("/v2/statuses/{$this->account}/shipments".$this->buildQuery($parameters));
    }
    
    /**
     * Get address
     *
     * @see https://devdocs.transsmart.com/#_6_2a_single_address_retrieval
     *
     * @param string $id
     *
     * @return array
     */
    public function getAddress($id)
    {
        return $this->get("/v2/addresses/{$this->account}/$id");
    }
    
    /**
     * Get addresses
     * 
     * @see https://devdocs.transsmart.com/#_6_2b_retrieve_multiple_addresses
     * 
     * @param array $parameters = []
     * 
     * @return array
     */
    public function getAddresses($parameters = [])
    {
        return $this->get("/v2/addresses/{$this->account}".$this->buildQuery($parameters));
    }
    
    /**
     * Create address
     *
     * @see https://devdocs.transsmart.com/#_6_1_creating_addresses
     * 
     * @param array $data
     *
     * @return array
     */
    public function createAddress($data)
    {
        return $this->post("/v2/addresses/{$this->account}", $data);
    }
    
    /**
     * Update address
     *
     * @see https://devdocs.transsmart.com/#_6_3_updating_addresses
     *
     * @param string $id
     * @param array $data
     *
     * @return array
     */
    public function updateAddress($id, $data)
    {
        return $this->put("/v2/addresses/{$this->account}/$id", $data);
    }
    
    /**
     * Delete address
     *
     * @see https://devdocs.transsmart.com/#_6_4_deleting_addresses
     * 
     * @param string $id
     *
     * @return array
     */
    public function deleteAddress($id)
    {
        return $this->delete("/v2/addresses/{$this->account}/$id");
    }
    
    /**
     * Get carriers
     * 
     * @see https://devdocs.transsmart.com/#_get_list_of_carriers
     * 
     * @return array
     */
    public function getCarriers()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/carriers");
    }
    
    /**
     * Get carrier
     *
     * @see https://devdocs.transsmart.com/#_get_one_carrier
     *
     * @param string $nr
     *
     * @return array
     */
    public function getCarrier($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/carriers/$nr");
    }
    
    /**
     * Get costcenters
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_costcenters
     *
     * @return array
     */
    public function getCostcenters()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/costCenters");
    }
    
    /**
     * Get costcenter
     *
     * @see https://devdocs.transsmart.com/#_get_one_costcenter
     *
     * @param string $nr
     *
     * @return array
     */
    public function getCostcenter($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/costCenters/$nr");
    }
    
    /**
     * Get incoterms
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_incoterms
     *
     * @return array
     */
    public function getIncoterms()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/incoterms");
    }
    
    /**
     * Get incoterm
     *
     * @see https://devdocs.transsmart.com/#_get_one_incoterm
     *
     * @param string $nr
     *
     * @return array
     */
    public function getIncoterm($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/incoterms/$nr");
    }
    
    /**
     * Get mail types
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_mail_types
     *
     * @return array
     */
    public function getMailTypes()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/mailTypes");
    }
    
    /**
     * Get mail type
     *
     * @see https://devdocs.transsmart.com/#_get_one_mail_type
     *
     * @param string $nr
     *
     * @return array
     */
    public function getMailType($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/mailTypes/$nr");
    }
    
    /**
     * Get package definitions
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_package_definitions
     *
     * @return array
     */
    public function getPackageDefinitions()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/packages");
    }
    
    /**
     * Get package definition
     *
     * @see https://devdocs.transsmart.com/#_get_one_package_definition
     *
     * @param string $nr
     *
     * @return array
     */
    public function getPackageDefinition($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/packages/$nr");
    }
    
    /**
     * Get service level times
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_usable_service_level_times
     *
     * @return array
     */
    public function getServiceLevelTimes()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/serviceLevelTimes");
    }
    
    /**
     * Get service level time
     *
     * @see https://devdocs.transsmart.com/#_get_one_usable_service_level_time
     *
     * @param string $nr
     *
     * @return array
     */
    public function getServiceLevelTime($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/serviceLevelTimes/$nr");
    }
    
    /**
     * Get service level others
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_usable_service_level_others
     *
     * @return array
     */
    public function getServiceLevelOthers()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/serviceLevelOthers");
    }
    
    /**
     * Get service level other
     *
     * @see https://devdocs.transsmart.com/#_get_one_usable_service_level_other
     *
     * @param string $nr
     *
     * @return array
     */
    public function getServiceLevelOther($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/serviceLevelOthers/$nr");
    }
    
    /**
     * Get booking profiles
     *
     * @see https://devdocs.transsmart.com/#_get_list_of_booking_profiles
     *
     * @return array
     */
    public function getBookingProfiles()
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/bookingProfiles");
    }
    
    /**
     * Get booking profile
     *
     * @see https://devdocs.transsmart.com/#_get_one_booking_profiles
     *
     * @param string $nr
     *
     * @return array
     */
    public function getBookingProfile($nr)
    {
        return $this->get("/v2/accounts/{$this->account}/listsettings/bookingProfiles/$nr");
    }
    
    /**
     * Get pickup locations
     * 
     * @see https://devdocs.transsmart.com/#_8_0_locations_retrieval
     * 
     * @param array $parameters = []
     * 
     * @return array
     */
    public function getPickupLocations($parameters = [])
    {
        return $this->get("/v2/locations/{$this->account}".$this->buildQuery($parameters));
    }
}