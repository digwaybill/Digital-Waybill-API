<?php
namespace DigitalWaybill;

const API_VERSION = 1;
const API_USER_AGENT = 'DigitalWaybillPHPAPI-1.0.0';
const API_HTTP_PORT = 80;
const API_HTTP_SSL_PORT = 443;
const API_HOST = 'api.dwaybill.com';
const API_TIMEOUT_SEC = 60;

function formatDateTime( $time )
{
    // Tue, 08 Jan 2013 08:58:59 
    return date( 'Y-m-d H:i:s', $time );
}

class Auth
{
    const AUTH_TYPE_QUICKENTRY = 1;
    const AUTH_TYPE_REMOTEPANEL = 2;

    private $_cid;
    private $_key;
    private $_customerNumber;
    private $_password;
    private $_authType;

    public function __construct( $authType, $cid, $key, $customerNumber = null, $password = null )
    {
        $this->_cid = $cid;
        $this->_key = $key;
        $this->_authType = $authType;
        switch( $authType )
        {
            case self::AUTH_TYPE_REMOTEPANEL:
                break;
            case self::AUTH_TYPE_QUICKENTRY:
                if( $customerNumber === null || $password === null )
                {
                    throw new \Exception( "Customer number and password are not optional when using AUTH_TYPE_QUICKENTRY authentication" );
                }
                $this->_customerNumber = $customerNumber;
                $this->_password = $password;
                break;
            default:
                throw new \Exception("Invalid auth type");
                break;
        }
    }

    public function getAuthParams()
    {
        $qs = sprintf( 'key=%s', urlencode( $this->_key ) );
        if( $this->_authType == self::AUTH_TYPE_QUICKENTRY )
        {
            $qs .= sprintf( '&customer_number=%s&password=%s', urlencode($this->_customerNumber), urlencode($this->_password) );
        }
        return $qs;
    }

    public function getCid()
    {
        return $this->_cid;
    }
}

class Connection
{


    private $_ch;
    private $_info;

    public function __construct( $path, $auth, $params = null, $method = 'GET', $data = null, $secure = true )
    {
        $this->_ch = curl_init();
        curl_setopt( $this->_ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $this->_ch, CURLOPT_USERAGENT, API_USER_AGENT );
        curl_setopt( $this->_ch, CURLOPT_TIMEOUT, API_TIMEOUT_SEC );

        $url = 'http';
        if( $secure )
        {
            $url .= 's';
            // curl_setopt( $this->_ch, CURLOPT_SSL_VERIFYHOST, 2 );
            // curl_setopt( $this->_ch, CURLOPT_CERTINFO, true );
            curl_setopt( $this->_ch, CURLOPT_PORT, API_HTTP_SSL_PORT );
        }
        else
        {
            curl_setopt( $this->_ch, CURLOPT_PORT, API_HTTP_PORT );
        }
       
        $url .= '://' . API_HOST . '/' . $auth->getCid() . $path . '?v=' . API_VERSION . '&' . $auth->getAuthParams();

        if( $params !== null )
        {
            foreach( $params as $key => $value )
            {
                $url .= sprintf( '&%s=%s', urlencode($key), urlencode($value) );
            }
        }

        //printf( "final url: %s\n", $url );
        curl_setopt( $this->_ch, CURLOPT_URL, $url );

        if( $data !== null )
        {
            curl_setopt( $this->_ch, CURLOPT_POSTFIELDS, $data );
        }

        curl_setopt( $this->_ch, CURLOPT_CUSTOMREQUEST, strtoupper($method) );
    }

    public function execute()
    {
        $response = curl_exec( $this->_ch );

        if( $response === false )
        {
            throw new \Exception( curl_error( $this->_ch ) );
        }

        $this->_info = curl_getinfo( $this->_ch );

        return $response;
    }

    public function getHttpCode()
    {
        return $this->_info['http_code'];
    }

    public function getContentType()
    {
        return $this->_info['content_type'];
    }
}

class PickupStop
{
    public $company = '';
    public $address = '';
    public $suite = '';
    public $city = '';
    public $state = '';
    public $postalCode = '';
    public $country = '';
    public $contactName = '';
    public $contactPhone = '';

    private function validate()
    {
    }

    public function getObject()
    {
        $this->validate();

        $obj = (object)array(
            "company" => $this->company,
            "address" => $this->address,
            "suite" => $this->suite,
            "city" => $this->city,
            "state" => $this->state,
            "postal_code" => $this->postalCode,
            "country" => $this->country,
            "contact" => (object)array(
                "name" => $this->contactName,
                "phone" => $this->contactPhone
            )
        );
        return $obj;
    }
}

class DeliverStop extends PickupStop
{
    public $notes = '';
    public $specialInstructions = '';
    public $serviceType = '';
    public $package = '';
    public $numberOfPieces = 1;
    public $weight = 1;
    public $vehicle = '';

    private function validate()
    {
        if( !is_numeric($this->weight) )
        {
            throw new \Exception("Package weight must be numeric");
        }
        if( !is_numeric($this->numberOfPieces) )
        {
            throw new \Exception("Number of pieces must be numeric");
        }
    }
    
    public function getObject()
    {
        $obj = parent::getObject();

        $this->validate();

        $obj->notes = $this->notes;
        $obj->special_instructions = $this->specialInstructions;
        $obj->service_type = $this->serviceType;
        $obj->package = $this->package;
        $obj->number_of_pieces = $this->numberOfPieces;
        $obj->weight = $this->weight;
        $obj->vehicle = $this->vehicle;

        return $obj;
    }
}

class Order
{
    public $customerNumber;
    public $costCenter;
    public $orderType;
    public $readyTime;
    public $roundTrip;
    private $_routeStops;
    private $_orderNumber;
    private $_new;

    public function __construct( $new = false )
    {
        $this->_new = $new;
        $this->roundTrip = false;
        $this->_routeStops = array(null,null);
    }

    public function setPickup( $pickupStop )
    {
        if( get_class($pickupStop) != 'DigitalWaybill\PickupStop' )
        {
            throw new \Exception("Pickup stop must be an instance of the PickupStop class");
        }
        $this->_routeStops[0] = $pickupStop;
    }
    
    public function setDeliver( $deliverStop )
    {
        if( get_class($deliverStop) != 'DigitalWaybill\DeliverStop' )
        {
            throw new \Exception("Deliver stop must be an instance of the DeliverStop class");
        }
        $this->_routeStops[1] = $deliverStop;
    }

    private function validate()
    {
        if( !in_array( $this->orderType, array(
            'Pickup',
            'Deliver',
            'Third-Party' ) ) )
        {
            throw new \Exception("Invalid order type");
        }
        if( !is_bool( $this->roundTrip ) )
        {
            throw new \Exception("Round trip must be a boolean value");
        }
    }

    private function getObject()
    {
        $this->validate();

        $routeStopObjects = array();
        foreach( $this->_routeStops as $rs )
        {
            array_push( $routeStopObjects, $rs->getObject() );
        }

        $obj = (object)array(
            "customer_number" => $this->customerNumber,
            "cost_center" => $this->costCenter,
            "order_type" => $this->orderType,
            "ready_time" => formatDateTime( $this->readyTime ),
            "round_trip" => $this->roundTrip,
            "route_stops" => $routeStopObjects
        );
        return $obj;
    }

    public function send( $auth )
    {
        if( $this->_new === true )
        {
            $obj = $this->getObject();

            $json = json_encode($obj);

            $c = new Connection( '/orders.json', $auth, null, 'POST', $json, true );
            $r = $c->execute();

            if( $c->getHttpCode() == 200 )
            {
                if( $c->getContentType() == 'application/json' )
                {
                    $o = json_decode($r);
                    if( $o && isset($o->body) && isset($o->body->order_number) )
                    {
                        return $o->body->order_number;
                    }
                }
                else
                {
                    throw new \Exception( "An unexpected error occurred. Please try again later." );
                }
            }
            else if( $c->getHttpCode() == 503 )
            {
                throw new \Exception( "The order panel is currently offline or unavailable. Please try again later. If the problem persists, please contact the courier company." );
            }
            else if( $c->getContentType() == 'application/json' )
            {
                $o = json_decode($r);
                if( $o && isset($o->error) )
                {
                    throw new \Exception($o->error);
                }
                else
                {
                    throw new \Exception( "An unexpected error occurred. Please try again later." );
                }
            }
            else
            {
                throw new \Exception( "An unexpected error occurred. Please try again later." );
            }

        }
        else
        {
            throw new \Exception( "Cannot re-send an existing order" );
        }
    }
}

?>
