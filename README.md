Digital Waybill API
===================

--------------------------------------------------------------------------------
Introduction
------------

The Digital Waybill API is a RESTful web services gateway to services 
provided by the Digital Waybill OrderPanel. This includes services 
such as querying for existing orders, placing new orders, accessing customer 
information, and various other functionality that was previously only available 
directly through the OrderPanel itself, or through other Digital Waybill client 
software such as the RemotePanel, QuickEntry, and 2-Way apps.

--------------------------------------------------------------------------------
Language-Specific Libraries and Examples
----------------------------------------

Language-specific libraries and examples of implementation of the Digital Waybill
API can be found in the subdirectories of this repository.

--------------------------------------------------------------------------------
Authentication
--------------

### Overview  
Access to the Digital Waybill API is closely tied to existing access-control
features of the panel, and follows two main schemes: RemotePanel access and
QuickEntry access. Access to all API features is granted through an API key,
which is directly linked to either of these two schemes. API clients providing a
RemotePanel API key are given full access with no further information. API
clients providing a QuickEntry API key are granted customer-specific information
*only* after providing a customer number and password in addition to the API key.

### RemotePanel Access
RemotePanel API access provides the highest level of access to an API client
user. Access is essentially the same as the access granted to a RemotePanel
user, that is, full access is granted to all orders, customers, and courier
information. This type of access should generally only be used when integrating
the OrderPanel with a system completely trusted by the courier.

### QuickEntry Access
QuickEntry API access provides access similar to the access provided by the
QuickEntry software, the order entry apps for mobile devices, and the web
ordering system. This access is intended for customer-level access, and grants
access to customer-level data only. This type of access is appropriate when
integrating with systems directly used by a customer, such that the customer
would be able to provide login information to gain access to that customer's
data. An example of such a system might be a courier company's website which
places an order on behalf of the customer, or allows a customer to get order
status information on previously-placed orders. 

### Configuration
Access to the Digital Waybill API is controlled through the Settings > Advanced
menu. Two API keys are automatically generated the first time the system is
configured: one for RemotePanel access and one for QuickEntry access. Next to
each API key field is a Generate button. This button is used to regenerate a new
API key for either access scheme. Be aware that generating a new key will
*immediately* invalidate the old key, and in general should only be necessary if
there is a security concern.


--------------------------------------------------------------------------------
Request Format
--------------

### Overview
All API requests are made through the web service gateway at api.dwaybill.com
using the HTTP protocol. Unencrypted, plaintext requests are accepted on TCP
port 80, and SSL/TLS encrypted requests are accepted on TCP port 443. The API
request format is detailed in this section. For your convenience, you may use
the following credentials to test any API functionality described in this
document:

<table border="1" cellpadding="3">
  <tr><td style="font-weight:bold;">CID</td><td>2000105850</td></tr>
  <tr><td style="font-weight:bold;">Remote API Key</td><td>d9e8df23d7149ed1c70a9b98539ec776539ec776</td></tr>
  <tr><td style="font-weight:bold;">QuickEntry API Key</td><td>f1d621905cece65bcbbb5018adacdd39adacdd39</td></tr>
  <tr><td style="font-weight:bold;">Customer Number</td><td>DYN833</td></tr>
  <tr><td style="font-weight:bold;">Customer Password</td><td>pass</td></tr>
</table>

### Identifying the Courier
All API requests are handled through the gateway at api.dwaybill.com. In order
to properly route the request to the correct OrderPanel, the courier's CID must
be provided as the first segment of the URI path for all requests. For example,
for a courier with CID 2000105850, all request URIs will begin with `/2000105850`.

### Providing the API version number
All requests must specify the API version number as the query string parameter
`v`. The current API version is 1.0. The values `1` or `1.0` are both
acceptable.

### Providing Credentials
Every request made to the API gateway must provide access credentials as part of
the request URI's query string. These parameters may appear anywhere within the
query string.

<table border="1" cellpadding="3">
<tr>
  <th>Query Parameter</th>
  <th>Description</th>
</tr>
<tr>
  <td>key</td>
  <td>The RemotePanel or QuickEntry API key. Required.</td>
</tr>
<tr>
  <td>customer_number</td>
  <td>The client's customer number. Required only if the API key is for QuickEntry access. Ignored otherwise.</td>
</tr>
<tr>
  <td>password</td>
  <td>The client's password. Required only if the API key is for QuickEntry access. Ignored otherwise.</td>
</tr>
</table>

A valid request URI for the method `/orders.json` made by a client using a 
QuickEntry API key might look like this:

    https://api.dwaybill.com/2000105850/orders.json?v=1&key=f1d621905cece65bcbbb5018adacdd39adacdd39&customer_number=DYN833&password=pass


### Important Remarks Regarding Date/Time Fields
Some requests require you to provide date/time information. Date/time data
should be provided as a localtime representation (local to the OrderPanel) 
in RFC 822/1123 format with no timezone offset information. Alternatively, the
date/time can be represented in the format described in section *Important 
Remarks Regarding Date/Time Fields* in the **Types** section of this document.

--------------------------------------------------------------------------------
Response Format
---------------

### Overview
All API requests that are able to reach the OrderPanel (that is, the order
panel is runnning and has successfully connected to Digital Waybill's servers)
will receive an HTTP response with a JSON-encoded body with this top-level format:

    {
      "status" : status_code,
      "error" : error_message,
      "body" : body_content
    }

<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <td>status</td>
  <td>Value will always be equal to the HTTP response code value. Successful requests return with status code 200. Other possible status codes are described throughout this document.</td>
</tr>
<tr>
  <td>error</td>
  <td>Value may contain a textual description of any errors or warnings that may have occurred while processing the request.  </td>
</tr>
<tr>
  <td>body</td>
  <td>Value will contain the requested information for successfull requests.</td>
</tr>
</table>

### Note
Some errors may be returned from the API gateway service itself. These responses
contain an empty HTTP body with a meaningful HTTP status code. Possible status
codes that can originate from the API gateway include:


<table border="1" cellpadding="3">
<tr>
  <th>Status Code</th>
  <th>Description</th>
</tr>
<tr>
  <td>400 Bad Request</td>
  <td>Gateway unable to handle the request URI provided, or the HTTP request
      itself was invalid.
  </td>
</tr>
<tr>
  <td>404 Not Found</td>
  <td>Gateway was unable to recognize the CID provided in the request.</td>
</tr>
<tr>
  <td>502 Bad Gateway</td>
  <td>Unable to communicate with the gateway servers at this time. Please
      contact Digital Waybill support for assistance.
  </td>
</tr>
<tr>
  <td>503 Service Unavailable</td>
  <td>Gateway recognizes the provided CID as valid, but is unable to contact the 
      OrderPanel at this time. If you receive this message, you may retry your 
      request, but we request that you do so by implementing an exponential backoff
      algorithm to prevent excessive server load. If the problem persists,
      please contact the courier company for more information.
  </td>
</tr>
</table>


--------------------------------------------------------------------------------
GET Methods
-----------

### Overview
All API requests for information are made using the HTTP GET method. Valid GET
methods are described in the following sections.

### Retrieving Order Information
Order information can be retrieved using the GET `/orders.json` method. When
using this method, the value for the response's `body` key will contain the
following structure:

    {
      "count" : number_of_orders_in_response,
      "page_number" : page_number_within_paginated_result_set,
      "page_size" : number_of_orders_per_page,
      "orders" : [array_of_Order_objects]
    }

If the URI is given with no additional query parameters (`/{CID}/orders.json`), 
the default behavior is to return all orders that the client has access to. 
If the client is using a RemotePanel API key, this will include all orders. For
QuickEntry API keys, this will include all orders for the corresponding
customer.

To retrieve details for a specific order, you may include the order number
directly in the URI. For example, to retrieve details for order number 12345,
your request URI would be `/{CID}/orders.json/12345`.

Result sets may be very large. To avoid excessive load on both the client and
server side, results can be paginated and retrieved through multiple requests.
To paginate a result set, use the query parameters `page_size` and
`page_number`. `page_size` is an optional parameter that specifies how many
orders should be returned per page. If the parameter is too large or is omitted, 
a default maximum page size may be enforced. The actual page size is returned as
part of the response. The `page_number` parameter specifies which page of the
result set you are requesting. The first page of a paginated result set is page 1. 
The page number is also returned as part of the response. All result sets
include the `count` parameter, which indicates the full size of the paginated 
result set.

The order information is returned within a JSON array under the `orders` key.
Each member of the array is a JSON object of type `Orders`. The `Orders` type is
described in the **Types** section of this document.

Possible status codes for this method include:

<table border="1" cellpadding="3">
<tr>
  <th>Status Code</th>
  <th>Description</th>
</tr>
<tr>
  <td>200 OK</td>
  <td>Success</td>
</tr>
<tr>
  <td>403 Forbidden</td>
  <td>You do not have appropriate permissions to access this order.</td>
</tr>
<tr>
  <td>404 Not Found</td>
  <td>No order was found matching the given parameters.</td>
</tr>
</table>


--------------------------------------------------------------------------------
POST Methods
------------

### Overview
API requests which create new entities are submitted as HTTP POST requests.
Valid POST requests are described in the following sections.

### Placing an Order
To place an order, an HTTP POST request is made to the URI `/{CID}/orders.json`.
The body of the POST message should contain the JSON-encoded data described
below:


    {
        "customer_number" : String,
        "cost_center" : String,
        "order_type" : String,
        "ready_time" : String,
        "round_trip" : Boolean,
        "route_stops" : [
            {
                "company" : String,
                "address" : String,
                "suite" : String,
                "city" : String,
                "state" : String,
                "postal_code" : String,
                "country" : String,
                "contact" : {
                    "name" : String,
                    "phone" : String
                }
            },
            {
                "company" : String,
                "address" : String,
                "suite" : String,
                "city" : String,
                "state" : String,
                "postal_code" : String,
                "country" : String,
                "contact" : {
                    "name" : String,
                    "phone" : String
                },
                "notes" : String,
                "special_instructions" : String,
                "service_type" : String,
                "package" : String,
                "number_of_pieces" : Integer,
                "weight" : Integer,
                "vehicle" : String
            }
        ]
    }


<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>customer_number</td>
  <td>The customer number for this order.</td>
</tr>
<tr>
  <td>cost_center</td>
  <td>The cost center for this order. Must be a cost center under the given
  customer_number.</td>
</tr>
<tr>
  <td>order_type</td>
  <td>The order's type. May be "Pickup", "Deliver", or "Third-Party".</td>
</tr>
<tr>
  <td>ready_time</td>
  <td>The time at which the package(s) will be ready for pickup.</td>
</tr>
<tr>
  <td>round_trip</td>
  <td>True if a round-trip order.</td>
</tr>
<tr>
  <td>route_stops</td>
  <td>An array with two elements. The first element contains the pickup stop
  information, and the second contains the deliver stop information. These
  objects are described in the next table.</td>
</tr>
</table>

The first item in the route\_stops array is a PickupStop object:
<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>company</td>
  <td>Company name</td>
</tr>
<tr>
  <td>address</td>
  <td>Street address</td>
</tr>
<tr>
  <td>suite</td>
  <td>Suite number</td>
</tr>
<tr>
  <td>city</td>
  <td>City</td>
</tr>
<tr>
  <td>state</td>
  <td>State</td>
</tr>
<tr>
  <td>postal_code</td>
  <td>Postal code/Zip code</td>
</tr>
<tr>
  <td>country</td>
  <td>Country</td>
</tr>
<tr>
  <td>name</td>
  <td>Contact name</td>
</tr>
<tr>
  <td>phone</td>
  <td>Contact phone number</td>
</tr>
</table>

The second item in the route\_stops array is a DeliverStop object:
<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>company</td>
  <td>Company name</td>
</tr>
<tr>
  <td>address</td>
  <td>Street address</td>
</tr>
<tr>
  <td>suite</td>
  <td>Suite number</td>
</tr>
<tr>
  <td>city</td>
  <td>City</td>
</tr>
<tr>
  <td>state</td>
  <td>State</td>
</tr>
<tr>
  <td>postal_code</td>
  <td>Postal code/Zip code</td>
</tr>
<tr>
  <td>country</td>
  <td>Country</td>
</tr>
<tr>
  <td>name</td>
  <td>Contact name</td>
</tr>
<tr>
  <td>phone</td>
  <td>Contact phone number</td>
</tr>
<tr>
  <td>notes</td>
  <td>Notes for this order</td>
</tr>
<tr>
  <td>special_instructions</td>
  <td>Special instructions for this order</td>
</tr>
<tr>
  <td>service_type</td>
  <td>The service type for this order</td>
</tr>
<tr>
  <td>package</td>
  <td>The package type for this order (only one package per order is supported
  at this time)</td>
</tr>
<tr>
  <td>number_of_pieces</td>
  <td>The number of pieces for this package type for this order</td>
</tr>
<tr>
  <td>weight</td>
  <td>The weight of this package for this order</td>
</tr>
<tr>
  <td>vehicle</td>
  <td>The vehicle type for this order</td>
</tr>
</table>

Possible status codes for this method include:

<table border="1" cellpadding="3">
<tr>
  <th>Status Code</th>
  <th>Description</th>
</tr>
<tr>
  <td>200 OK</td>
  <td>Order was placed successfully. The resulting order number is returned in
  the response. This order number can be used with the
  <code>/{CID}/orders.json/{order_number}</code> method to confirm details, get price,
  etc.</td>
</tr>
<tr>
  <td>403 Forbidden</td>
  <td>You are not authorized to place orders using this customer number.</td>
</tr>
<tr>
  <td>404 Not Found</td>
  <td>No order was found matching the given parameters.</td>
</tr>
</table>


--------------------------------------------------------------------------------
Types
-----

### Overview
The following sections describe the various object types returned in API gateway
responses. Each section begins with a type definition for the object, followed
by a description of each field in the object. **Important:** The API gateway
may return fields which are not described in this documentation. These fields 
may represent deprecated values, or values which have not yet been provisioned 
for API usage. *Using any field which is not contained in the documentation 
is not supported.*

### Important Remarks Regarding Date/Time Fields
As the JSON format does not implement a date/time type, fields specified as type 
`Date` are JSON string types which represent a date/time in the format:
`%a, %d %b %Y %H:%M:%S` (i.e. `Tue, 08 Jan 2013 08:58:59`), where:

    %a - An abbreviated textual representation of the day (Sun through Sat)
    %d - Two-digit day of the month (with leading zeros)
    %b - Abbreviated month name (Jan through Dec)
    %Y - Four digit representation for the year
    %H - Two digit representation of the hour in 24-hour format
    %M - Two digit representation of the minute
    %S - Two digit representation of the second

### OrderType
The `OrderType` type is an enumeration including the following values:
<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>Pickup</td>
  <td>The order's cost center is attached to the pickup stop.</td>
</tr>
<tr>
  <td>Deliver</td>
  <td>The order's cost center is attached to the delivery stop.</td>
</tr>
<tr>
  <td>Third-Party</td>
  <td>The order's cost center is not attached to any of the stops.</td>
</tr>
<tr>
  <td>Route</td>
  <td>The order is a route. The cost center may or may not be associated with
  any of the route stops.</td>
</tr>
</table>

### Origin
The `Origin` type is an enumeration including the following values:
<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>ClientOrder</td>
  <td>The order was placed using one of the Digital Waybill client applications.</td>
</tr>
<tr>
  <td>TelephoneOrder</td>
  <td>The order was placed directly from the OrderPanel.</td>
</tr>
<tr>
  <td>WebOrder</td>
  <td>The order was placed from the web interface at
  <code>http://www.dwaybill.com/{CID}</code></td>
</tr>
</table>

### Status
The `Status` type is an enumeration including the following values:
<table border="1" cellpadding="3">
<tr>
  <th>Value</th>
  <th>Description</th>
</tr>
<tr>
  <td>New</td>
  <td>The order is a new order.</td>
</tr>
<tr>
  <td>Dispatched</td>
  <td>The order has been dispatched to a driver.</td>
</tr>
<tr>
  <td>PickedUp</td>
  <td>The order has been picked up by the driver.</td>
</tr>
<tr>
  <td>Completed</td>
  <td>The order has been completed.</td>
</tr>
<tr>
  <td>Cancelled</td>
  <td>The order has been cancelled.</td>
</tr>
<tr>
  <td>Delivered</td>
  <td>The order has been delivered by the driver.</td>
</tr>
<tr>
  <td>Confirmed</td>
  <td>The order has been acknowledged and accepted by the driver.</td>
</tr>
<tr>
  <td>Undefined</td>
  <td>The order is not in any of the defined states.</td>
</tr>
</table>

### Order
The `Order` type describes an order:

    {
        "time" : Date,
        "order_number" : Integer,
        "price" : Float,
        "customer_number" : String,
        "cost_center" : String,
        "final_price" : Float,
        "pending" : Boolean,
        "origin" : Origin,
        "status_date" : Date,
        "ready_time" : Date,
        "deliver_by" : Date,
        "dispatch_driver" : String,
        "version" : String,
        "status" : Status,
        "order_type" : OrderType,
        "route_stops" : Array,
    }

<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <td>time</td>
  <td>The time the order was placed.</td>
</tr>
<tr>
  <td>order_number</td>
  <td>The order number.</td>
</tr>
<tr>
  <td>customer_number</td>
  <td>The customer number attached to the order.</td>
</tr>
<tr>
  <td>cost_center</td>
  <td>The cost center attached to the order.</td>
</tr>
<tr>
  <td>final_price</td>
  <td>The order's price.</td>
</tr>
<tr>
  <td>pending</td>
  <td>True if the order has not yet been completed.</td>
</tr>
<tr>
  <td>origin</td>
  <td>The order's origin.</td>
</tr>
<tr>
  <td>status_date</td>
  <td>Time of order's last change in status.</td>
</tr>
<tr>
  <td>ready_time</td>
  <td>The ready for pickup time for the order.</td>
</tr>
<tr>
  <td>deliver_by</td>
  <td>The deliver by time for the order.</td>
</tr>
<tr>
  <td>dispatch_driver</td>
  <td>Driver to which this order is dispatched.</td>
</tr>
<tr>
  <td>version</td>
  <td>A string uniquely representing the order's current state.</td>
</tr>
<tr>
  <td>status</td>
  <td>The order's current status.</td>
</tr>
<tr>
  <td>order_type</td>
  <td>The order's type.</td>
</tr>
<tr>
  <td>route_stops</td>
  <td>An array of PickupStop/DeliverStop objects. For non-route orders, the
  array will contain two elements: the first will be a PickupStop object, and
  the second will be a DeliverStop object. For route orders, the array will
  contain only DeliverStop objects, with each object corresponding to a single
  stop along the route.</td>
</tr>
</table>


### Contact
The `Contact` type describes a contact:

    {
        "name" : String,
        "phone" : String
    }

<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <td>name</td>
  <td>Contact name</td>
</tr>
<tr>
  <td>phone</td>
  <td>Contact phone number</td>
</tr>
</table>

### PickupStop
The `PickupStop` type describes pickup stop for a non-route order:

    {
        "company" : String,
        "address" : String,
        "suite" : String,
        "city" : String,
        "state" : String,
        "postal_code" : String,
        "country" : String,
        "contact" : Contact
    }

<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <td>company</td>
  <td>Company name</td>
</tr>
<tr>
  <td>address</td>
  <td>Street address</td>
</tr>
<tr>
  <td>suite</td>
  <td>Suite number</td>
</tr>
<tr>
  <td>city</td>
  <td>City</td>
</tr>
<tr>
  <td>state</td>
  <td>State</td>
</tr>
<tr>
  <td>postal_code</td>
  <td>Postal code/Zip code</td>
</tr>
<tr>
  <td>country</td>
  <td>Country</td>
</tr>
<tr>
  <td>contact</td>
  <td>Point of contact</td>
</tr>
</table>


### DeliverStop

#### Description
The `DeliverStop` type describes delivery stop for any type of order.

#### Signature Images
If a captured signature is available for a particular DeliverStop object, the
`signature` field will contain the URI for that signature's image. OrderPanel
versions prior to 4.3.1 give URI paths that do not include the cid path segment.
This is a bug that was corrected in version 4.3.1. 

For example, the following is a complete URL to a valid signature image:

    


#### JSON Format
    {
        "company" : String,
        "address" : String,
        "suite" : String,
        "city" : String,
        "state" : String,
        "postal_code" : String,
        "country" : String,
        "contact" : Contact,
        "paper_waybill" : String,
        "special_instructions" : String,
        "service_type" : String,
        "package" : String,
        "number_of_pieces" : Integer,
        "weight" : Integer,
        "packages" : Array<Package>,
        "vehicle" : String,
        "driver_number" : String,
        "dispatch_message" : String,
        "notes" : String,
        "signature" : String,
        "signature_contact" : String,
        "reference" : String,
        "distance" : Integer
        "air_distance" : Integer
        "driver_pricelist" : String,
        "receive_date" : Date,
        "dispatch_date" : Date,
        "pickup_date" : Date,
        "delivery_date" : Date,
        "cancel_date" : Date,
        "confirm_date" : Date
        "route_stop_id" : Integer
    }


#### Field Descriptions
<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <td>company</td>
  <td>Company name</td>
</tr>
<tr>
  <td>address</td>
  <td>Street address</td>
</tr>
<tr>
  <td>suite</td>
  <td>Suite number</td>
</tr>
<tr>
  <td>city</td>
  <td>City</td>
</tr>
<tr>
  <td>state</td>
  <td>State</td>
</tr>
<tr>
  <td>postal_code</td>
  <td>Postal code/Zip code</td>
</tr>
<tr>
  <td>country</td>
  <td>Country</td>
</tr>
<tr>
  <td>contact</td>
  <td>Point of contact</td>
</tr>
<tr>
  <td>paper_waybill</td>
  <td>Paper waybill number</td>
</tr>
<tr>
  <td>special_instructions</td>
  <td>Special instructions</td>
</tr>
<tr>
  <td>service_type</td>
  <td>Service type</td>
</tr>
<tr>
  <td>package</td>
  <td>Package type of first package in this stop</td>
</tr>
<tr>
  <td>number_of_pieces</td>
  <td>Number of pieces for the first package in this stop</td>
</tr>
<tr>
  <td>weight</td>
  <td>Package weight for first package in this stop</td>
</tr>
<tr>
  <td>packages</td>
  <td>All packages in this order</td>
</tr>
<tr>
  <td>vehicle</td>
  <td>Vehicle type</td>
</tr>
<tr>
  <td>driver_number</td>
  <td>Dispatched driver's driver number</td>
</tr>
<tr>
  <td>dispatch_message</td>
  <td>Dispatch message for driver</td>
</tr>
<tr>
  <td>notes</td>
  <td>Notes</td>
</tr>
<tr>
  <td>signature</td>
  <td>If a signature is available, this field contains the URI used to access
  the SVG image of the signature.</td>
</tr>
<tr>
  <td>signature_contact</td>
  <td>Signature contact</td>
</tr>
<tr>
  <td>reference</td>
  <td>Reference</td>
</tr>
<tr>
  <td>distance</td>
  <td>Street distance in meters</td>
</tr>
<tr>
  <td>air_distance</td>
  <td>Straight-line distance is meters</td>
</tr>
<tr>
  <td>driver_pricelist</td>
  <td>Driver pricelist for this stop</td>
</tr>
<tr>
  <td>receive_date</td>
  <td>Date the order was received</td>
</tr>
<tr>
  <td>dispatch_date</td>
  <td>Date the order was dispatched</td>
</tr>
<tr>
  <td>pickup_date</td>
  <td>Date the order was picked up</td>
</tr>
<tr>
  <td>delivery_date</td>
  <td>Date the order was delivered</td>
</tr>
<tr>
  <td>cancel_date</td>
  <td>Date the order was canceled</td>
</tr>
<tr>
  <td>confirm_date</td>
  <td>Date the order was acknowledged and accepted by the driver</td>
</tr>
<tr>
  <td>route_stop_id</td>
  <td>Route stop ID</td>
</tr>
</table>

### Package
The `Package` type describes a package:

    {
        "package" : String,
        "number_of_pieces" : Integer,
        "weight" : Integer
    }

<table border="1" cellpadding="3">
<tr>
  <th>Key</th>
  <th>Description</th>
</tr>
<tr>
  <th>package</th>
  <th>Package type</th>
</tr>
<tr>
  <th>number_of_pieces</th>
  <th>Number of pieces of this package type</th>
</tr>
<tr>
  <th>weight</th>
  <th>Weight of each piece of this package type</th>
</tr>
</table>
