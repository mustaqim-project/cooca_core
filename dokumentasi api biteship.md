1. Maps 
Maps API Introduction
Biteship provides Maps API to ease and standardize location names for your needs. With Maps API, you can query specific areas, cities or districts within a selected country.

Endpoints
GET /v1/maps/areas

Search Area
Endpoint
GET /v1/maps/areas

This API will show you the name of the area. By simply send your input then we will send you the list of area corresponding to your input. The autocomplete will help to find the area more efficient and easy.

You can create something like this to be part of your website. For example you can use sample endpoints below with additional params as follows:

Example Request
GET /v1/maps/areas?countries=ID&input=Jakarta+Selatan&type=single

tip
NOTE: Please trigger your call function after user has done writing. Otherwise it will slow down your response due to multiple calls.

Response
{
    "success": true,
    "areas": [
        {
            "id": "IDNP6IDNC148IDND843IDZ12250",
            "name": "Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12250",
            "country_name": "Indonesia",
            "country_code": "ID",
            "administrative_division_level_1_name": "DKI Jakarta",
            "administrative_division_level_1_type": "province",
            "administrative_division_level_2_name": "Jakarta Selatan",
            "administrative_division_level_2_type": "city",
            "administrative_division_level_3_name": "Pesanggrahan",
            "administrative_division_level_3_type": "district",
            "postal_code": 12250
        },
        {
            "id": "IDNP6IDNC148IDND843IDZ12260",
            "name": "Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12260",
            "country_name": "Indonesia",
            "country_code": "ID",
            "administrative_division_level_1_name": "DKI Jakarta",
            "administrative_division_level_1_type": "province",
            "administrative_division_level_2_name": "Jakarta Selatan",
            "administrative_division_level_2_type": "city",
            "administrative_division_level_3_name": "Pesanggrahan",
            "administrative_division_level_3_type": "district",
            "postal_code": 12260
        },
        {
            "id": "IDNP6IDNC148IDND843IDZ12270",
            "name": "Pesanggrahan, Jakarta Selatan, DKI Jakarta. 12270",
            "country_name": "Indonesia",
            "country_code": "ID",
            "administrative_division_level_1_name": "DKI Jakarta",
            "administrative_division_level_1_type": "province",
            "administrative_division_level_2_name": "Jakarta Selatan",
            "administrative_division_level_2_type": "city",
            "administrative_division_level_3_name": "Pesanggrahan",
            "administrative_division_level_3_type": "district",
            "postal_code": 12270
        }
    ]
}

2. RATES
Rates API Introduction
Rates API will help you to browse multiple logistic options based on coordinates, area id, or postal codes that are requested through Biteship Platform. Biteship has 5 different cases with Rates API endpoint.

Biteship has Rates different rates accuracy depending on which type of API you’re using as shown below

Endpoints
POST    /v1/rates/couriers       // by coordinates
POST    /v1/rates/couriers       // by postal codes
POST    /v1/rates/couriers       // by area id
POST    /v1/rates/couriers       // by mix
POST    /v1/rates/couriers       // by type

Retrieve a Courier Rates
Endpoint
POST /v1/rates/couriers
Check your order history or tracking by orderId. You can get the Order ID from the Order API request.
API Parameters
origin_area_id string
OPTIONAL / REQUIRED
The origin area id. Area Id can be found using [Maps API](https://biteship.com/id/docs/api/locations/overview)
destination_area_id string
OPTIONAL / REQUIRED
The destination area id. Area Id can be found using [Maps API](https://biteship.com/id/docs/api/locations/overview)
origin_latitude number
OPTIONAL / REQUIRED
The origin latitude where items can be picked up from the shipper or seller.
origin_longitude number
OPTIONAL / REQUIRED
The origin longitude where items can be picked up from the shipper or seller.
destination_latitude number
OPTIONAL / REQUIRED
The destination latitude where items will be received by the end customer.
destination_longitude number
OPTIONAL / REQUIRED
The destination longitude where items will be received by the end customer.
origin_postal_code number
OPTIONAL / REQUIRED
Postal code for the origin location
destination_postal_code number
OPTIONAL / REQUIRED
Postal code for the destination location
type string
Optional
You can retrieve courier rates based on types. Currently there’s only one available type that can be used as follows: origin_suggestion_to_closest_destination Biteship will automatically select the nearest location based on your available location list
couriers string
REQUIRED
List of courier names which are available to query separated by commas. You can see list of available Biteship couriers in [Courier API](https://biteship.com/id/docs/api/couriers/overview)
items array
REQUIRED
The list of item you will send for deliveryShow child parametersitems.name
REQUIRED
Name of your package.
items.description
Optional
A description of your package. You can share the color, the details or any that help describing your item.
items.category
Optional
Categorization of your package, the value must be one of these table value. If empty, the default value will be othersValue
Description
fashion
Clothing, accessories, and personal adornments.
healthcare
Products related to health, wellness, and personal care.
food_and_drink
Items related to food and beverages. This category is important for instant delivery to ensure the courier assignment and avoid longer pick up & delivery time.
electronic
Electronic devices and accessories.
beauty
Beauty products and cosmetics.
outdoor_gear
Equipment and apparel for outdoor activities.
home_accessories
Items that enhance the decor and functionality of the home.
hobby
Products related to leisure and hobbies.
collection
Items that belong to a particular collection or set.
sparepart
Replacement parts and accessories.
groceries
Food and household items typically purchased for regular consumption.
frozen_food
Food items preserved by freezing, such as frozen meals, meat, vegetables, and desserts.
others
Miscellaneous items that don't fit into the other categories.
items.sku
Optional
Item SKU if you have one.
items.value
REQUIRED
The value of the item.
items.quantity
REQUIRED
The total of the item.
items.weight
REQUIRED
The weight of the item in grams.
items.height
Optional
The height of the item in centimeters. Item dimensions can affect the weight of your item which can cause a price different.
items.length
Optional
The length of the item in centimeters. Item dimensions can affect the weight of your item which can cause a price different.
items.width
Optional
The width of the item in centimeters. Item dimensions can affect the weight of your item which can cause a price different.
courier_insurance number
Optional
The amount of the insurance value. This is optional if you want to insured your shipment. For example, if your item is valued at IDR 1.000.000, then you should put 1000000 for the value.
destination_cash_on_delivery number
Optional
State the COD Amount if you want to activate COD delivery. COD amount cannot exceed IDR 15.000.000. You must fill in destination_cash_on_delivery_type too.
destination_cash_on_delivery_type string
Optional
The COD disbursement window. Value can be 7_days, 5_days, or 3_days.7_days you will receiver your money 7 days after the item is delivered.5_days you will receiver your money 5 days after the item is delivered.3_days you will receiver your money 3 days after the item is delivered.
Types of Request
Rates by Coordinates
Accuracy : Low
For Instant couriers such as Gojek, Grab, Paxel, Lalamove and Borzo require a coordinate to generate price.
For Example:CoordinateLat: -2.918334Lng: 104.77683
ProCan be used for instant delivery, integrated with Map services such as Google Map or OpenStreetMap. Can be used for instant delivery and also standard delivery rates.
ConsThis can consist of 2 different types of postal codes which are 30961 and 30161. This may result in price different between the courier and Biteship
JSON Body Request
{ "origin_latitude": -6.3031123, "origin_longitude": 106.7794934999, "destination_latitude": -6.2441792, "destination_longitude": 106.783529, "couriers": "grab,jne,tiki", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 2 } ]}
Rates by Postal Code
Accuracy : Medium
For Example:Postal code 30961 and 30161.
ProEasy to implement, customers can focus on input only postal code.
ConsBoth represent the same area but different districts. 30961 represent Gasing, Talang Kalapa, Banyuasin. While 30161 represent Sako Baru, Sako, Palembang.
There’s a chance that one postal code consist of two districts and the other way around
JSON Body Request
{ "origin_postal_code": 12440, "destination_postal_code": 12240, "couriers": "anteraja,jne,sicepat", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 2 } ]}
Rates by Area ID
Accuracy : High
For Example:IDNP33IDNC39IDND4653IDZ30961Gasing, Talang Kalapa, BanyuasinIDNP33IDNC325IDND3713IDZ30161Sako Baru, Sako, Palembang.
ProArea Id is using district as its lowest area level, Most courier in Indonesia still uses District as their point of reference to determine its shipping rates.
ConsExtra implementation to use [Maps API](https://biteship.com/id/docs/api/maps/overview) to get the AreaID. Cannot show instant delivery services.
JSON Body Request
{ "origin_area_id": "IDNP6IDNC148IDND836IDZ12410", "destination_area_id": "IDNP6IDNC148IDND836IDZ12430", "couriers": "paxel,jne,sicepat", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 2 } ]}
Rates by Mix
You can mix between postal code for origin and coordinate for destination (see example below) and the other way around. This also applicable if you want to use Area ID from [Maps API](https://biteship.com/id/docs/api/maps/overview) and combine them with other origin or destination field.
JSON Body Request
{ "origin_postal_code": 12440, "destination_latitude": -6.2441792, "destination_longitude": 106.783529, "couriers": "paxel,jne,sicepat", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 2 } ]}
Rates by Type
tip
NOTE: Make sure you already have list of location saved on your profile. You can use [Location API](https://biteship.com/id/docs/api/locations/overview) to insert your location data or go to [https://dashboard.biteship.com/address](https://dashboard.biteship.com/address)
Type origin_suggestion_to_closest_destination will help you to find the nearest origin location from your destination.
JSON Body Request
{ "type": "origin_suggestion_to_closest_destination", "destination_latitude": -6.2441792, "destination_longitude": 106.783529, "couriers": "paxel,jne,sicepat", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 2 } ]}
Rates with Insurance Fee
Insert courier_insurance for the total insurance value. There will be insurance_fee field in the pricing response if the courier supports insurance.
JSON Body Request
{ "origin_postal_code": 12440, "destination_postal_code": 12240, "couriers": "sicepat,jne", "courier_insurance": 199000, "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 1 } ]}
J&T Cargo mandatory insurance
J&T Cargo (jntcargo) does not follow the courier_insurance flow described above. Its insurance is mandatory on every shipment and is derived from the declared value of your items, so courier_insurance is ignored for this courier.
The fee we charge you:
insurance_fee = max( insurance_rate x declared goods value , minimum_fee )declared goods value = sum of (items[].value x items[].quantity), in IDR
The insurance rate and minimum fee that apply are configured on your account. For example, at a 0.5% rate with a 5,000 minimum fee:
Declared goods value
insurance_fee
100,000
5,000
1,000,000
5,000
2,000,000
10,000
10,000,000
50,000
The price field for a jntcargo option is the shipping cost plus this insurance_fee, the same way price already includes insurance and COD for other couriers.
caution
items[].value is required for J&T Cargo. If the declared goods value is zero, the behaviour depends on what else you requested:
You requested other couriers too — the request succeeds and jntcargo is silently omitted from pricing. Other couriers return normally. There is no error and no field telling you it was dropped.
You requested only jntcargo — the request is rejected with code 40001007.
On order creation (POST /v1/orders) — the request is rejected with code 40002072.
JSON Body Request
{ "origin_postal_code": 40111, "destination_postal_code": 60111, "couriers": "jntcargo", "items": [ { "name": "Mesin", "description": "Industrial machine part", "value": 3000000, "length": 60, "width": 40, "height": 40, "weight": 20000, "quantity": 1 } ]}
Response (pricing entry)
{ "available_for_cash_on_delivery": false, "available_for_proof_of_delivery": false, "available_for_instant_waybill_id": true, "available_for_insurance": true, "company": "jntcargo", "courier_name": "J&T Cargo", "courier_code": "jntcargo", "courier_service_name": "Standar", "courier_service_code": "ft", "description": "Layanan reguler", "duration": "2 - 5 days", "shipment_duration_range": "2 - 5", "shipment_duration_unit": "days", "service_type": "standard", "shipping_type": "freight", "insurance_fee": 15000, "price": 265000}
In the example above, at a 0.5% rate, insurance_fee is 15,000 and price is the J&T shipping cost plus that fee.
tip
J&T Cargo is a freight service and exposes a single option, ft ("Standar"). It is pickup-only and does not support cash on delivery or proof of delivery.
Rates with COD Fee
Insert destination_cash_on_delivery for the total COD amount and destination_cash_on_delivery_type for the COD disbursement window. There will be cash_on_delivery_fee field in the pricing response if the courier supports COD.
JSON Body Request
{ "origin_postal_code": 12440, "destination_postal_code": 12240, "couriers": "sicepat,jne", "destination_cash_on_delivery": 199000, "destination_cash_on_delivery_type": "7_days", "items": [ { "name": "Shoes", "description": "Black colored size 45", "value": 199000, "length": 30, "width": 15, "height": 20, "weight": 200, "quantity": 1 } ]}
API Response
If you have an active [Custom Rate](https://dashboard.biteship.com/setting/custom-rates) (configured in the dashboard), the pricing response will have an extra field either shipping_fee_discount or shipping_fee_surcharge, depending on the custom rate configuration.
For example, if you set a flat fee for a certain courier service in [Custom Rate](https://dashboard.biteship.com/setting/custom-rates), and the original shipment_fee is below the flat fee, you would receive a shipping_fee_surcharge field as the price difference.
If you set a percentage discount for a certain courier service in [Custom Rate](https://dashboard.biteship.com/setting/custom-rates), you would receive a shipping_fee_discount field as the absolute amount of the discount from the original shipment_fee.
The price field in each pricing option is the final price after applying the custom rate (discount/surcharge) and adding other fees if you have activated insurance or COD delivery.
Response
{ "success": true, "object": "courier_pricing", "message": "Success to retrieve courier pricing", "code": 20001007, "origin": { "location_id": "5dad2bf246d52d72b87378f6", "latitude": -6.3031123, "longitude": 106.7794934999, "postal_code": 12440, "country_name": "Indonesia", "country_code": "ID", "administrative_division_level_1_name": "DKI Jakarta", "administrative_division_level_1_type": "province", "administrative_division_level_2_name": "Jakarta Selatan", "administrative_division_level_2_type": "city", "administrative_division_level_3_name": "Cilandak", "administrative_division_level_3_type": "district", "administrative_division_level_4_name": "Lebak bulus", "administrative_division_level_4_type": "subdistrict", "address": "Jl. RS. Fatmawati Raya No.29, RT.8/RW.4, Cilandak Bar., Kec. Cilandak, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12430, Indonesia" }, "destination": { "location_id": "5dad2bf246d52d72b87378f6", "latitude": -6.2441792, "longitude": 106.783529, "postal_code": 12240, "country_name": "Indonesia", "country_code": "ID", "administrative_division_level_1_name": "DKI Jakarta", "administrative_division_level_1_type": "province", "administrative_division_level_2_name": "Jakarta Selatan", "administrative_division_level_2_type": "city", "administrative_division_level_3_name": "Cilandak", "administrative_division_level_3_type": "district", "administrative_division_level_4_name": "Lebak bulus", "administrative_division_level_4_type": "subdistrict", "address": "Jl. RS. Fatmawati Raya No.29, RT.8/RW.4, Cilandak Bar., Kec. Cilandak, Kota Jakarta Selatan, Daerah Khusus Ibukota Jakarta 12430, Indonesia" }, "pricing": [ { "available_collection_method": ["pickup"], "available_for_cash_on_delivery": true, "available_for_proof_of_delivery": true, "available_for_instant_waybill_id": true, "available_for_insurance": false, "company": "jne", "courier_name": "JNE", "courier_code": "jne", "courier_service_name": "City to City (CTC)", "courier_service_code": "ctc", "currency": "IDR", "description": "Pengiriman city to city", "duration": "2 - 3 days", "shipment_duration_range": "2 - 3", "shipment_duration_unit": "days", "service_type": "standard", "shipping_type": "parcel", "shipping_fee": 9000, "shipping_fee_discount": 0, "shipping_fee_surcharge": 0, "insurance_fee": 0, "cash_on_delivery_fee": 2000, "price": 11000, "tax_lines": [], "type": "ctc" }, { "available_collection_method": ["pickup"], "available_for_cash_on_delivery": true, "available_for_proof_of_delivery": false, "available_for_instant_waybill_id": true, "available_for_insurance": true, "company": "sicepat", "courier_name": "SiCepat", "courier_code": "sicepat", "courier_service_name": "Reguler", "courier_service_code": "reg", "currency": "IDR", "description": "Layanan reguler", "duration": "1 - 2 days", "shipment_duration_range": "1 - 2", "shipment_duration_unit": "days", "service_type": "standard", "shipping_type": "parcel", "shipping_fee": 32000, "shipping_fee_discount": 0, "shipping_fee_surcharge": 0, "insurance_fee": 1000, "cash_on_delivery_fee": 2000, "price": 35000, "tax_lines": [], "type": "reg" }, { "available_collection_method": ["pickup"], "available_for_cash_on_delivery": true, "available_for_proof_of_delivery": false, "available_for_instant_waybill_id": true, "available_for_insurance": true, "company": "sicepat", "courier_name": "SiCepat", "courier_code": "sicepat", "courier_service_name": "Besok Sampai Tujuan", "courier_service_code": "best", "description": "Besok sampai tujuan", "duration": "1 days", "shipment_duration_range": "1", "shipment_duration_unit": "days", "service_type": "overnight", "shipping_type": "parcel", "shipping_fee": 40000, "shipping_fee_discount": 0, "shipping_fee_surcharge": 2000, "insurance_fee": 1000, "cash_on_delivery_fee": 2000, "price": 45000, "tax_lines": [], "type": "best" }, { "available_collection_method": ["drop_off"], "available_for_cash_on_delivery": false, "available_for_proof_of_delivery": false, "available_for_instant_waybill_id": true, "available_for_insurance": true, "company": "wahana", "courier_name": "Wahana", "courier_code": "wahana", "courier_service_name": "Deno", "courier_service_code": "deno", "currency": "IDR", "description": "Layanan reguler", "duration": "2 - 3 days", "shipment_duration_range": "2 - 3", "shipment_duration_unit": "days", "service_type": "standard", "shipping_type": "parcel", "shipping_fee": 4000, "shipping_fee_discount": 4000, "shipping_fee_surcharge": 0, "insurance_fee": 1000, "cash_on_delivery_fee": 0, "price": 1000, "tax_lines": [], "type": "deno" } ]}

Error Codes
Below are the list of Rates error codes. You can use the 'Code' column and customize based on your platform.
Method
Endpoint
Code
Message
POST
/v1/rates/couriers
40001001
Failed due to invalid or not available postal code. Please contact [support@biteship.com](mailto:support@biteship.com) for this matter.
POST
/v1/rates/couriers
40001002
Get pricing failed caused by missing parameter(s). Please contact [support@biteship.com](mailto:support@biteship.com) for this problem.
POST
/v1/rates/couriers
40001007
J&T Cargo requires a declared item value (items[].value) for its mandatory insurance.
POST
/v1/rates/couriers
40001010
No courier available for requested location. Please activate other courier option to receive other pricing rate.
tip
40001007 is only returned when jntcargo is the only courier you requested and no item value was declared. If you requested other couriers alongside it, the request succeeds and jntcargo is simply omitted from the results — see [J&T Cargo mandatory insurance](https://biteship.com/id/docs/api/rates/retrieve#jt-cargo-mandatory-insurance).

3. LOCATION
Overview
Location API Introduction
Location API will let you create, edit and delete your location list data directly through an API call.

Endpoints
POST    /v1/locations
GET     /v1/locations/:id
POST    /v1/locations/:id
DELETE  /v1/locations/:id

Create a Location
Endpoint
POST /v1/locations
To create a location that will be saved in Biteship's dashboard under [Address Page](https://dashboard.biteship.com/address). Each location can be used for future shipment with Biteship dashboard. You can use the Location data to create an order using its location id.
API Parameters
name string
REQUIRED
The name of the location
contact_name string
REQUIRED
The contact name of the person in charge (PIC) in the location
contact_phone string
REQUIRED
The contact phone of the person in charge (PIC) in the location
address string
REQUIRED
The complete detail of the location address. Please input the house / location number.
note string
Optional
You can add additional information regarding the location. Such as house color, street name or else.
postal_code string
REQUIRED
The postal code for the location
latitude string
REQUIRED
Coordinate latitude for the location
longitude string
REQUIRED
Coordinate longitude for the location
type string
REQUIRED
Type of location, the value is either 'origin' or 'destination'.origin the location which sender is sending.destination where the item will be shipped and received by the recipient.
Type of Request
Standard API Request
JSON Body Request
{ "name":"Apotik Gambir", "contact_name":"Ahmad", "contact_phone":"08123456789", "address":"Jl. Gambir Selatan no 5. Blok F 92. Jakarta Pusat.", "note":"Dekat tulisan warung Bu Indah", "postal_code":10110, "latitude":-6.232123121, "longitude":102.22189911, "type":"origin",}
API Response
API Response
{ "success": true, "id": "61d565c69a3211036a05f3f8", "name": "Apotek Gambir", "contact_name": "Ahmad", "contact_phone": "08123456789", "address": "Jl. Gambir Selatan no 5. Blok F 92. Jakarta Pusat."}

Retrieve a Location
Endpoint
GET /v1/locations/:id

Check your location by location_id

API Response
{
   "success": true,
   "id": "61d565c69a3211036a05f3f8",
   "name": "Apotek Gambir",
   "contact_name": "Ahmad",
   "contact_phone": "08123456789",
   "address": "Jl. Gambir Selatan no 5. Blok F 92. Jakarta Pusat."
}

Update a Location
Endpoint
POST /v1/locations/:id

You can edit your existing location by sending a new value.

For Example Let’s pretend if you want to change your location name. You can just simply send a JSON body with only the origin_address field. It will automatically change your current location details.

JSON Body Request
{
   "name":"Apotik Monas",
}

API Response
{
   "success": true,
   "id": "61d565c69a3211036a05f3f8",
   "name": "Apotek Monas",
   "contact_name": "Ahmad",
   "contact_phone": "08123456789",
   "address": "Jl. Gambir Selatan no 5. Blok F 92. Jakarta Pusat."
}

Delete a Location
Endpoint
DELETE /v1/locations/:id

You can delete your existing location

API Response
{
   "success": true,
   "id": "61d565c69a3211036a05f3f8",
   "message": "Location successfully been removed"
}

4. DRAF ORDER
Overview
Draft order API allows users to save an order before moving forward to order creation. Draft order will become an order after it has been confirmed using Confirm Draft Order API.

Different than Order API, Draft Order API allow user to change its order detail, including the courier service. Biteship will not create a waybill when in the order is still in draft.

Endpoints
POST    /v1/draft_orders
GET     /v1/draft_orders/:id
GET     /v1/draft_orders/:id/rates
POST    /v1/draft_orders/:id
DELETE  /v1/draft_orders/:id
POST    /v1/draft_orders/:id/confirm

Status Flow
Status Flow
Draft Order Status
No	Status	Description	Available to Delete
1	placed	Draft order is just barely placed, cannot be confirmed.	✅
2	ready	Draft order is ready to be confirmed. Courier has been set.	✅
3	confirmed	Draft order is ready to be confirmed. Courier has been set.	❌

Create Draft Order
Endpoint
POST /v1/draft_orders
You can create draft order by calling the Create Draft Order API. You won't be charged until the draft order is confirmed using [Confirm Draft Order API](https://biteship.com/id/docs/api/draft_orders/confirm) and order is created with the draft order reference id.
Draft order and order connected through reference_id, if you want to collerate the draft order with your own order id, please send it as reference_id.
With draft order, you can create a draft without specifying the courier information.
The Request Payload will roughly similar with [Create Order API](https://biteship.com/id/docs/api/orders/create), with different constraints on some fields.
Draft Order Status
No
Status
Description
Available to Delete
1
placed
Draft order is just barely placed, cannot be confirmed.
✅
2
ready
Draft order is ready to be confirmed. Courier has been set.
✅
3
confirmed
Draft order is ready to be confirmed. Courier has been set.
❌
API Parameters
shipper_contact_name string
Optional
The name of the shipper.
shipper_contact_phone string
Optional
The phone number of the shipper.
shipper_contact_email string
Optional
The email of the shipper.
shipper_organization string
Optional
The organization of the shipper.
origin_contact_name string
REQUIRED
The name of the person in the pickup location.
origin_contact_phone string
REQUIRED
The phone number of the person in the pickup location.
origin_contact_email string
Optional
The email of the person in the pickup location.
origin_address string
REQUIRED
Complete address of the pickup location.
origin_note string
Optional
Additional information of the pickup location to ease pickup process.
origin_postal_code number
*REQUIRED/ OPTIONAL
Postal code of the pickup location.
origin_coordinate object
*REQUIRED/ OPTIONAL
Coordinates of the pickup location. If you use an instant courier, you must use coordinate.Show child parametersorigin_coordinate.latitude double
*REQUIRED/ OPTIONAL
Latitude of the pickup location.
origin_coordinate.longitude double
*REQUIRED/ OPTIONAL
Longitude of the pickup location.
origin_collection_method string
Optional
Use the available_collection_method from [Rates API](https://biteship.com/id/docs/api/rates/overview). Value can be pickup, or drop_off. Default to pickup.pickup your package will be picked by courier based on origin that you specify.drop_off you must drop off the package to the nearest courier agent.
destination_contact_name string
REQUIRED
The name of the person in destination location.
destination_contact_phone string
REQUIRED
The phone number of the person in destination location.
destination_contact_email string
Optional
The email of the person in destination location.
destination_address string
REQUIRED
Complete address of the destination location.
destination_note string
Optional
Additional information of the destination location to ease destination process.
destination_postal_code number
*REQUIRED/ OPTIONAL
Postal code of the destination location.
destination_coordinate object
*REQUIRED/ OPTIONAL
Coordinates of the destination location. If you use an instant courier, you must use coordinate.Show child parameters
destination_cash_on_delivery number
Optional
State the COD Amount if you want to activate COD delivery.
destination_cash_on_delivery_type string
Optional
The COD disbursement window. Value can be 7_days, 5_days, or 3_days.7_days you will receiver your money 7 days after the item is delivered.5_days you will receiver your money 5 days after the item is delivered.3_days you will receiver your money 3 days after the item is delivered.
destination_proof_of_delivery boolean
Optional
Proof of delivery feature.
destination_proof_of_delivery_note string
*REQUIRED/ OPTIONAL
Notes for proof of delivery. It is requiredif proof of delivery feature is activated.
courier_company string
Optional
Shipping provider that will be used for this particular shipment. List of available courier can be found using [Couriers API](https://biteship.com/id/docs/api/couriers/overview).
courier_type string
Optional
Courier type based on the courier company used. Each type can be different for each company. Value of type can be found within the [Rates API](https://biteship.com/id/docs/api/rates/overview) and [Couriers API](https://biteship.com/id/docs/api/couriers/overview).
courier_insurance number
Optional
The amount of the insurance value. This is optionalif you want to insured your shipment. For example, if your item is valued at IDR 1.000.000, then you should put 1000000 for the value.
delivery_type string
REQUIRED
Type of delivery order is now and scheduled.now will generate waybill instantly and pickup right away.scheduled will generate waybill instantly and pickup based on delivery date and delivery time.
delivery_date string
Optional
The delivery date format: “YYYY-MM-DD”
delivery_time string
Optional
The delivery time format: “HH:mm”
order_note string
Optional
Additional information for the shipment.
metadata object
Optional
You can insert any kind of data through this object for internal purposes.
reference_id string
Optional
You can insert your internal order id here. Must unique for each draft order id.
tags array
Optional
You can insert multiple custom tags (in string) for filtering your orders by tag later on.
items array
REQUIRED
The list of item you will send for deliveryShow child parameters
Type of Request
Without courier
JSON Body Request
{ "origin_contact_name": "Amir", "origin_contact_phone": "081234567890", "origin_address": "Plaza Senayan, Jalan Asia Afrik...", "origin_note": "Deket pintu masuk STC", "origin_postal_code": 12440, "destination_contact_name": "John Doe", "destination_contact_phone": "088888888888", "destination_contact_email": "johndoe@example.com", "destination_address": "Lebak Bulus MRT...", "destination_postal_code": 12950, "destination_note": "Near the gas station", "delivery_type": "now", "order_note": "Please be careful", "items": [ { "name": "Black L", "description": "White Shirt", "category": "fashion", "value": 165000, "quantity": 1, "height": 10, "length": 10, "weight": 200, "width": 10 } ]}
With courier
JSON Body Request
{ "origin_contact_name": "Amir", "origin_contact_phone": "081234567890", "origin_address": "Plaza Senayan, Jalan Asia Afrik...", "origin_note": "Deket pintu masuk STC", "origin_postal_code": 12440, "destination_contact_name": "John Doe", "destination_contact_phone": "088888888888", "destination_contact_email": "johndoe@example.com", "destination_address": "Lebak Bulus MRT...", "destination_postal_code": 12950, "destination_note": "Near the gas station", "courier_company": "sicepat", "courier_type": "reg", "delivery_type": "now", "order_note": "Please be careful", "items": [ { "name": "Black L", "description": "White Shirt", "category": "fashion", "value": 165000, "quantity": 1, "height": 10, "length": 10, "weight": 200, "width": 10 } ]}
*When you send origin and destination value, you must at least choose one type of origin or destination. Origin and destination must at least have postal codes, coordinates or area ids. You do not need to insert all of the three values.
API Response
Created
API Response
{ "success": true, "code": 20111002, "object": "draft_order", "id": "ef18275c-02a9-4887-a56b-f374edb96ec4", "order_id": null, "origin": { "area_id": "IDNP6IDNC148IDND836IDNZ12430", "address": "CITOS - Cilandak Town Square, Kota Jakarta Selatan, Jakarta 12430", "note": null, "contact_name": "John Doe", "contact_phone": "081234567901", "contact_email": "johndoe@example.com", "coordinate": { "latitude": null, "longitude": null }, "province_name": "DKI Jakarta", "city_name": "Jakarta Selatan", "district_name": "Cilandak", "postal_code": 12430, "collection_method": "pickup" }, "destination": { "area_id": "IDNP6IDNC147IDND835IDNZ10210", "address": "Jl. Contoh No. 12", "note": null, "contact_name": "Jake Doe", "contact_phone": "0812345678902", "contact_email": "jakedoe@example.com", "coordinate": { "latitude": null, "longitude": null }, "province_name": "DKI Jakarta", "city_name": "Jakarta Pusat", "district_name": "Tanah Abang", "postal_code": 10210, "proof_of_delivery": { "use": false, "fee": 0, "note": null, "link": null }, "cash_on_delivery": { "payment_method": null, "amount": null, "amount_currency": "IDR", "note": null, "type": null } }, "courier": { "name": null, "phone": null, "company": "sicepat", "type": "reg", "link": null, "tracking_id": null, "waybill_id": null, "insurance": { "amount": 0, "fee": 0, "note": "", "amount_currency": "IDR", "fee_currency": "IDR", }, "routing_code": null }, "delivery": { "type": "now", "datetime": "2024-09-19T03:40:22.810Z", "note": null, "distance": null, "distance_unit": "kilometer" }, "extra": [], "tags": [], "metadata": null, "items": [ { "name": "Black Leather Bag", "description": "Goods", "value": 30, "currency": "IDR", "quantity": 1, "height": 1, "width": 1, "length": 1, "weight": 1 } ], "currency": "IDR", "tax_lines": [], "price": 11500, "status": "ready", "reference_id": "example/35ef876e-3902-4186-873a-e9012ea1e354", "invoice_id": "1209839012839012", "user_id": "6448e9d77ff7510bbadfa605", "created_at": "2024-09-19T03:40:22.802Z", "updated_at": "2024-09-19T03:40:22.802Z", "placed_at": null, "ready_at": "2024-09-19T03:40:22.802Z", "confirmed_at": null, "deleted_at": null}

Retrieve Draft Order
Endpoint
GET /v1/draft_orders/:id

You can retrieve draft order information.

API Response
{
  "success": true,
  "code": 20011004,
  "object": "draft_order",
  "id": "ef18275c-02a9-4887-a56b-f374edb96ec4",
  "order_id": null,
  "origin": {
    "area_id": "IDNP6IDNC148IDND836IDNZ12430",
    "address": "CITOS - Cilandak Town Square, Kota Jakarta Selatan, Jakarta 12430",
    "note": null,
    "contact_name": "John Doe",
    "contact_phone": "081234567901",
    "contact_email": "johndoe@example.com",
    "coordinate": {
      "latitude": null,
      "longitude": null
    },
    "province_name": "DKI Jakarta",
    "city_name": "Jakarta Selatan",
    "district_name": "Cilandak",
    "postal_code": 12430,
    "collection_method": "pickup"
  },
  "destination": {
    "area_id": "IDNP6IDNC147IDND835IDNZ10210",
    "address": "Jl. Contoh No. 12",
    "note": null,
    "contact_name": "Jake Doe",
    "contact_phone": "0812345678902",
    "contact_email": "jakedoe@example.com",
    "coordinate": {
      "latitude": null,
      "longitude": null
    },
    "province_name": "DKI Jakarta",
    "city_name": "Jakarta Pusat",
    "district_name": "Tanah Abang",
    "postal_code": 10210,
    "proof_of_delivery": {
      "use": false,
      "fee": 0,
      "fee_currency": "IDR",
      "note": null,
      "link": null
    },
    "cash_on_delivery": {
      "payment_method": null,
      "amount": null,
      "amount_currency": "IDR",
      "note": null,
      "type": null
    }
  },
  "courier": {
    "name": null,
    "phone": null,
    "company": "sicepat",
    "type": "reg",
    "link": null,
    "tracking_id": null,
    "waybill_id": null,
    "insurance": {
      "amount": 0,
      "amount_currency": "IDR",
      "fee": 0,
      "fee_currency": "IDR",
      "note": ""
    },
    "routing_code": null
  },
  "delivery": {
    "type": "now",
    "datetime": "2024-09-19T03:40:22.810Z",
    "note": null,
    "distance": null,
    "distance_unit": "kilometer"
  },
  "extra": [],
  "tags": [],
  "metadata": null,
  "items": [
    {
      "name": "Black Leather Bag",
      "description": "Goods",
      "value": 30,
      "currency": "IDR",
      "quantity": 1,
      "height": 1,
      "width": 1,
      "length": 1,
      "weight": 1
    }
  ],
  "currency": "IDR",
  "tax_lines": [],
  "price": 11500,
  "status": "ready",
  "reference_id": "example/35ef876e-3902-4186-873a-e9012ea1e354",
  "invoice_id": "1209839012839012",
  "user_id": "6448e9d77ff7510bbadfa605",
  "created_at": "2024-09-19T03:40:22.802Z",
  "updated_at": "2024-09-19T03:40:22.802Z",
  "placed_at": null,
  "ready_at": "2024-09-19T03:40:22.802Z",
  "confirmed_at": null,
  "deleted_at": null
}

Retrieve Draft Order Rates
Endpoint
GET /v1/draft_orders/:id/rates

You can retrieve draft order rates.

API Response
{
    "success": true,
    "object": "courier_pricing",
    "message": "Success to retrieve courier pricing",
    "code": 20001003,
    "origin": {
        "location_id": null,
        "latitude": null,
        "longitude": null,
        "postal_code": 12430,
        "country_name": "Indonesia",
        "country_code": "ID",
        "administrative_division_level_1_name": "DKI Jakarta",
        "administrative_division_level_1_type": "province",
        "administrative_division_level_2_name": "Jakarta Selatan",
        "administrative_division_level_2_type": "city",
        "administrative_division_level_3_name": "Cilandak",
        "administrative_division_level_3_type": "district",
        "administrative_division_level_4_name": "Cilandak Barat",
        "administrative_division_level_4_type": "subdistrict",
        "address": null
    },
    "stops": [],
    "destination": {
        "location_id": null,
        "latitude": null,
        "longitude": null,
        "postal_code": 10210,
        "country_name": "Indonesia",
        "country_code": "ID",
        "administrative_division_level_1_name": "DKI Jakarta",
        "administrative_division_level_1_type": "province",
        "administrative_division_level_2_name": "Jakarta Pusat",
        "administrative_division_level_2_type": "city",
        "administrative_division_level_3_name": "Tanah Abang",
        "administrative_division_level_3_type": "district",
        "administrative_division_level_4_name": "Bendungan Hilir",
        "administrative_division_level_4_type": "subdistrict",
        "address": null
    },
    "pricing": [
        {
            "available_collection_method": [
                "pickup"
            ],
            "available_for_cash_on_delivery": false,
            "available_for_proof_of_delivery": false,
            "available_for_instant_waybill_id": true,
            "available_for_insurance": true,
            "company": "grab",
            "courier_name": "GRAB",
            "courier_code": "grab",
            "courier_service_name": "Instant",
            "courier_service_code": "instant",
            "description": "Instant service for on demand needs.",
            "duration": "1 - 3 Hours",
            "shipment_duration_range": "1 - 3",
            "shipment_duration_unit": "hours",
            "service_type": "same_day",
            "shipping_type": "parcel",
            "price": 11000,
            "type": "instant"
        },
        {
            "available_collection_method": [
                "pickup"
            ],
            "available_for_cash_on_delivery": true,
            "available_for_proof_of_delivery": false,
            "available_for_instant_waybill_id": true,
            "available_for_insurance": true,
            "company": "anteraja",
            "courier_name": "AnterAja",
            "courier_code": "anteraja",
            "courier_service_name": "Reguler",
            "courier_service_code": "reg",
            "description": "Regular shipment",
            "duration": "2 days",
            "shipment_duration_range": "2",
            "shipment_duration_unit": "days",
            "service_type": "standard",
            "shipping_type": "parcel",
            "price": 10000,
            "type": "reg"
        },
        {
            "available_collection_method": [
                "pickup"
            ],
            "available_for_cash_on_delivery": true,
            "available_for_proof_of_delivery": false,
            "available_for_instant_waybill_id": true,
            "available_for_insurance": true,
            "company": "sicepat",
            "courier_name": "SiCepat",
            "courier_code": "sicepat",
            "courier_service_name": "Reguler",
            "courier_service_code": "reg",
            "description": "Layanan reguler",
            "duration": "1 - 2 days",
            "shipment_duration_range": "1 - 2",
            "shipment_duration_unit": "days",
            "service_type": "standard",
            "shipping_type": "parcel",
            "price": 11500,
            "type": "reg"
        },
        {
            "available_collection_method": [
                "pickup"
            ],
            "available_for_cash_on_delivery": true,
            "available_for_proof_of_delivery": false,
            "available_for_instant_waybill_id": true,
            "available_for_insurance": true,
            "company": "sap",
            "courier_name": "SAP",
            "courier_code": "sap",
            "courier_service_name": "Regular Service",
            "courier_service_code": "reg",
            "description": "Regular Service",
            "duration": "4 days",
            "shipment_duration_range": "4",
            "shipment_duration_unit": "days",
            "service_type": "standard",
            "shipping_type": "parcel",
            "price": 8000,
            "type": "reg"
        },
        {
            "available_for_cash_on_delivery": false,
            "available_for_proof_of_delivery": false,
            "available_for_instant_waybill_id": true,
            "available_for_insurance": true,
            "company": "ninja",
            "courier_name": "Ninja Express",
            "courier_code": "ninja",
            "courier_service_name": "Reguler",
            "courier_service_code": "standard",
            "description": "Layanan reguler",
            "duration": "2 - 3 days",
            "shipment_duration_range": "2 - 3",
            "shipment_duration_unit": "days",
            "service_type": "standard",
            "shipping_type": "parcel",
            "price": 7777,
            "type": "standard"
        }
    ]
}

Update Draft Order
Endpoint
POST /v1/draft_orders/:id

You can update the draft order as long as it is not confirmed using Confirm Draft Order API yet. With this you can change the courier information, user information, etc. This endpoints also necessary if you want to make the draft order to be ready to confirm if you don't specify the courier information during Create Draft Order.

API Parameters
shipper_contact_name string
OPTIONAL
The name of the shipper.
shipper_contact_phone string
OPTIONAL
The phone number of the shipper.
shipper_contact_email string
OPTIONAL
The email of the shipper.
shipper_organization string
OPTIONAL
The organization of the shipper.
origin_contact_name string
OPTIONAL
The name of the person in the pickup location.
origin_contact_phone string
OPTIONAL
The phone number of the person in the pickup location.
origin_contact_email string
OPTIONAL
The email of the person in the pickup location.
origin_address string
OPTIONAL
Complete address of the pickup location.
origin_note string
OPTIONAL
Additional information of the pickup location to ease pickup process.
origin_postal_code number
OPTIONAL
Postal code of the pickup location.
origin_coordinate object
OPTIONAL
Coordinates of the pickup location. If you use an instant courier, you must use coordinate.
Show child parameters
origin_collection_method string
OPTIONAL
Use the available_collection_method from Rates API. Value can be pickup, or drop_off. Default to pickup.
pickup your package will be picked by courier based on origin that you specify.
drop_off you must drop off the package to the nearest courier agent.
destination_contact_name string
OPTIONAL
The name of the person in destination location.
destination_contact_phone string
OPTIONAL
The phone number of the person in destination location.
destination_contact_email string
OPTIONAL
The email of the person in destination location.
destination_address string
OPTIONAL
Complete address of the destination location.
destination_note string
OPTIONAL
Additional information of the destination location to ease destination process.
destination_postal_code number
OPTIONAL
Postal code of the destination location.
destination_coordinate object
OPTIONAL
Coordinates of the destination location. If you use an instant courier, you must use coordinate.
Show child parameters
destination_cash_on_delivery number
OPTIONAL
State the COD Amount if you want to activate COD delivery.
destination_cash_on_delivery_type string
OPTIONAL
The COD disbursement window. Value can be 7_days, 5_days, or 3_days.
7_days you will receiver your money 7 days after the item is delivered.
5_days you will receiver your money 5 days after the item is delivered.
3_days you will receiver your money 3 days after the item is delivered.
destination_proof_of_delivery boolean
OPTIONAL
Proof of delivery feature.
destination_proof_of_delivery_note string
OPTIONAL
Notes for proof of delivery. It is optionalif proof of delivery feature is activated.
courier_company string
OPTIONAL
Shipping provider that will be used for this particular shipment. List of available courier can be found using Couriers API.
courier_type string
OPTIONAL
Courier type based on the courier company used. Each type can be different for each company. Value of type can be found within the Rates API and Couriers API.
courier_insurance number
OPTIONAL
The amount of the insurance value. This is optionalif you want to insured your shipment. For example, if your item is valued at IDR 1.000.000, then you should put 1000000 for the value.
delivery_type string
OPTIONAL
Type of delivery order is now and scheduled.
now will generate waybill instantly and pickup right away.
scheduled will generate waybill instantly and pickup based on delivery date and delivery time.
delivery_date string
Optional
The delivery date format: “YYYY-MM-DD”
delivery_time string
Optional
The delivery time format: “HH:mm”
order_note string
OPTIONAL
Additional information for the shipment.
metadata object
OPTIONAL
You can insert any kind of data through this object for internal purposes.
reference_id string
OPTIONAL
You can insert your internal order id here. Must unique for each draft order id.
tags array
OPTIONAL
You can insert multiple custom tags (in string) for filtering your orders by tag later on.
items array
OPTIONAL
The list of item you will send for delivery
Show child parameters
Type of Request
Set courier
If you want to set the courier for the draft order, make sure you call the get the courier rates using Retrieve Draft Order Rates API. The only courier_company and courier_type value that are supported are the one that are displayed on the Retrieve Draft Order Rates API response.

Once you have selected the courier company and the courier type, the draft order status is automatically updated to "ready" status. Once the status is ready, you can confirm the Draft Order using Confirm Draft Order API and the order will be created.

JSON Body Request
{
  "courier_company": "sicepat",
  "courier_type": "reg"
}

Set origin and destination
This endpoint can be used to change the origin and destination information, for example, if previously the origin and destination are defined with postal code, it can be changed to coordinate.

Upon changing, if the courier_company and courier_type already set, and it's instant courier, but the origin or destination are set to other than coordinate, it will nullify the courier_company and courier_type, hence the draft order status will be placed.

JSON Body Request
{
  "origin_coordinate": {
    "latitude": -6.1751,
    "longitude": 106.8650
  },
  "destination_coordinate": {
    "latitude": -6.2115,
    "longitude": 106.8452
  }
}

When you send origin and destination value, you must at least choose one type of origin or destination. Origin and destination must at least have postal codes, coordinates or area ids. You do not need to insert all of the three values.

API Response
Updated
API Response
{
    "success": true,
    "code": 20011003,
    "object": "draft_order",
    "id": "ef18275c-02a9-4887-a56b-f374edb96ec4",
    "order_id": null,
    "origin": {
        "area_id": "IDNP6IDNC148IDND836IDNZ12430",
        "address": "CITOS - Cilandak Town Square, Kota Jakarta Selatan, Jakarta 12430",
        "note": null,
        "contact_name": "John Doe",
        "contact_phone": "081234567901",
        "contact_email": "johndoe@example.com",
        "coordinate": {
            "latitude": null,
            "longitude": null
        },
        "province_name": "DKI Jakarta",
        "city_name": "Jakarta Selatan",
        "district_name": "Cilandak",
        "postal_code": 12430,
        "collection_method": "pickup"
    },
    "destination": {
        "area_id": "IDNP6IDNC147IDND835IDNZ10210",
        "address": "Jl. Contoh No. 12",
        "note": null,
        "contact_name": "Jake Doe",
        "contact_phone": "0812345678902",
        "contact_email": "jakedoe@example.com",
        "coordinate": {
            "latitude": null,
            "longitude": null
        },
        "province_name": "DKI Jakarta",
        "city_name": "Jakarta Pusat",
        "district_name": "Tanah Abang",
        "postal_code": 10210,
        "proof_of_delivery": {
            "use": false,
            "fee": 0,
            "note": null,
            "link": null
        },
        "cash_on_delivery": {
            "payment_method": null,
            "amount": null,
            "amount_currency": "IDR",
            "note": null,
            "type": null
        }
    },
    "courier": {
        "name": null,
        "phone": null,
        "company": "sicepat",
        "type": "reg",
        "link": null,
        "tracking_id": null,
        "waybill_id": null,
        "insurance": {
            "amount": 0,
            "amount_currency": "IDR",
            "fee": 0,
            "fee_currency": "IDR",
            "note": ""
        },
        "routing_code": null
    },
    "delivery": {
        "type": "now",
        "datetime": "2024-09-19T03:40:22.810Z",
        "note": null,
        "distance": null,
        "distance_unit": "kilometer"
    },
    "extra": [],
    "tags": [],
    "metadata": null,
    "items": [
        {
            "name": "Black Leather Bag",
            "description": "Goods",
            "value": 30,
            "quantity": 1,
            "height": 1,
            "width": 1,
            "length": 1,
            "weight": 1
        }
    ],
    "currency": "IDR",
    "tax_lines": [],
    "price": 11500,
    "status": "ready",
    "reference_id": "example/35ef876e-3902-4186-873a-e9012ea1e354",
    "invoice_id": "1209839012839012",
    "user_id": "6448e9d77ff7510bbadfa605",
    "created_at": "2024-09-19T03:40:22.802Z",
    "updated_at": "2024-09-19T03:40:22.802Z",
    "placed_at": null,
    "ready_at": "2024-09-19T03:40:22.802Z",
    "confirmed_at": null,
    "deleted_at": null
}

Delete Draft Order
Endpoint
DELETE /v1/draft_orders/:id

Delete draft order API will remove your draft order. Once the draft order is deleted, it cannot be activated again. If you want to activate the draft order, you need to create another draft order.

Confirm Draft Order
Endpoint
POST /v1/draft_orders/:id/confirm

You can confirm draft order when the status is ready. When the draft order is confirmed, the draft order status will be updated to confirmed and it will create new order. The new order will have the same reference_id as the draft order, this is helpful to identify which order fulfills the draft order.

The id on response from this endpoint is the order's id. You can then continue the flow using Order API.

API Response
{
    "success": true,
    "message": "Order successfully created",
    "object": "order",
    "id": "66eba364e2e5a64816928197",
    "draft_order_id": "ef18275c-02a9-4887-a56b-f374edb96ec4",
    "shipper": {
        "name": "Amir",
        "email": "amir@example.com",
        "phone": "081234567901",
        "organization": "Biteship Test"
    },
    "origin": {
        "contact_name": "John Doe",
        "contact_phone": "081234567902",
        "coordinate": {
            "latitude": null,
            "longitude": null
        },
        "address": "CITOS - Cilandak Town Square, Kota Jakarta Selatan, Jakarta 12430",
        "note": "-",
        "postal_code": 12430,
        "collection_method": "pickup"
    },
    "destination": {
        "contact_name": "Jack Doe",
        "contact_phone": "081234567903",
        "contact_email": "jackdoe@example.com",
        "address": "Jl. Contoh No. 123",
        "note": "-",
        "proof_of_delivery": {
            "use": false,
            "fee": 0,
            "note": null,
            "link": null
        },
        "cash_on_delivery": {
            "id": null,
            "amount": 0,
            "fee": 0,
            "amount_currency": "IDR",
            "fee_currency": "IDR",
            "note": null,
            "type": null,
            "status": null,
            "payment_status": "pending",
            "payment_method": "cash"
        },
        "coordinate": {
            "latitude": null,
            "longitude": null
        },
        "postal_code": 10210
    },
    "stops": [],
    "courier": {
        "tracking_id": "66eba364e2e5a642a092819a",
        "waybill_id": "000000000000",
        "company": "sicepat",
        "name": null,
        "phone": null,
        "type": "reg",
        "link": "https://track.biteship.com?waybill_id=000000000000",
        "insurance": {
            "amount": 0,
            "fee": 0,
            "amount_currency": "IDR",
            "fee_currency": "IDR",
            "note": ""
        },
        "routing_code": null
    },
    "delivery": {
        "datetime": "2024-09-19T11:07+07:00",
        "note": null,
        "type": "now",
        "distance": null,
        "distance_unit": "kilometer"
    },
    "reference_id": "0000000000",
    "items": [
        {
            "name": "Black Leather Bag",
            "description": "Goods",
            "category": "others",
            "sku": null,
            "value": 30,
            "quantity": 1,
            "length": 1,
            "width": 1,
            "height": 1,
            "weight": 1
        }
    ],
    "extra": [],
    "currency": "IDR",
    "tax_lines": [],
    "price": 11500,
    "metadata": null,
    "note": null,
    "status": "confirmed"
}

Error Codes
Below are the list of Draft Order error codes. You can use the 'Code' column and customize based on your platform.

Method	Endpoint	Code	Message
DELETE	/v1/draft_orders/:id	42211006	Draft order with 'id=$DRAFT_ORDER_ID' has been confirmed.
GET	/v1/draft_orders/:id	40411007	Draft order with 'id=$DRAFT_ORDER_ID' is not found.
GET	/v1/draft_orders/:id/rates	40011001	Bad request.
GET	/v1/draft_orders/:id/rates	40411007	Draft order with 'id=$DRAFT_ORDER_ID' is not found.
GET	/v1/draft_orders/:id/rates	42211006	Draft order with 'id=$DRAFT_ORDER_ID' has been confirmed.
POST	/v1/draft_orders	40011001	Bad request.
POST	/v1/draft_orders	42211009	Invoice with 'number=$INVOICE_NUMBER' has been paid.
POST	/v1/draft_orders	42211010	'$CASH_ON_DELIVERY_TYPE' is not a valid cash on delivery type.
POST	/v1/draft_orders	42211011	Cash on delivery amount cannot exceed Rp 15.000.000,-
POST	/v1/draft_orders	42211012	Cash on delivery for '$COURIER_COMPANY' is not available for this account.
POST	/v1/draft_orders	42211013	Postal code '$POSTAL_CODE' is not registered.
POST	/v1/draft_orders	42211015	Reference ID '${reference_id}' is already taken.
POST	/v1/draft_orders/:id	40011001	Bad request.
POST	/v1/draft_orders/:id	40411007	Draft order with 'id=$DRAFT_ORDER_ID' is not found.
POST	/v1/draft_orders/:id	42211006	Draft order with 'id=$DRAFT_ORDER_ID' has been confirmed.
POST	/v1/draft_orders/:id	42211009	Invoice with 'number=$INVOICE_NUMBER' has been paid.
POST	/v1/draft_orders/:id	42211010	'$CASH_ON_DELIVERY_TYPE' is not a valid cash on delivery type.
POST	/v1/draft_orders/:id	42211011	Cash on delivery amount cannot exceed Rp 15.000.000,-
POST	/v1/draft_orders/:id	42211012	Cash on delivery for '$COURIER_COMPANY' is not available for this account.
POST	/v1/draft_orders/:id	42211013	Postal code '$POSTAL_CODE' is not registered.
POST	/v1/draft_orders/:id/confirm	40011001	Bad request.
POST	/v1/draft_orders/:id/confirm	40411007	Draft order with 'id=$DRAFT_ORDER_ID' is not found.
POST	/v1/draft_orders/:id/confirm	42211008	Draft order with 'id=$DRAFT_ORDER_ID' is not ready to be confirmed for it is on $DRAFT_ORDER_STATUS status.

5. ORDERS
Overview
Order API Introduction
Order objects are created to handle sellers’ shipments. You can create, retrieve, update, and cancel individual orders. Orders are identified by a unique, random ID

Endpoints
POST    /v1/orders
GET     /v1/orders/:id
POST    /v1/orders/:id
POST    /v1/orders/:id/cancel
DELETE  /v1/orders/:id  // deprecated

Status Flow
Below is the order flow of general shipment with Biteship

Biteship Flow Order
Order Status
No	Status	Description	Available to Delete
1	confirmed	Order is ready to be confirmed. AWB has been generated.	✅
2	scheduled	Order has been scheduled to be delivered. AWB has been generated.	✅
3	allocated	Order has been allocated, courier will pickup the package.	✅
4	picking_up	Courier is on the way to pickup the package. (First Mile)	✅
5	picked	Package has been picked up by courier.	❌
6	cancelled	Order has been cancelled.	❌
7	on_hold	Order is on hold for any reason.	❌
8	in_transit	Package is on the transit to the destination. (Middle Mile)	❌
8	dropping_off	Courier is dropping off the package to the receiver. (Last Mile)	❌
9	return_in_transit	Package is on the transit for a return to sender.	❌
10	returned	Package has been returned to sender.	❌
11	rejected	Order has been rejected.	❌
12	disposed	Package has been disposed / destroyed.	❌
13	courier_not_found	Cannot find courier for the order.	❌
14	delivered	Package has been delivered to the receiver.	❌
Courier Status Availability
For each uniqueness of all courier status, please go to this link All Courier Status Availability

Create an Order
Endpoint
POST /v1/orders

To request a new order to be picked up by the courier, you need to create a new order object. Make sure your Biteship Balance is sufficient when making this request. Try to request for invoice payment for more custom ordering.

If your environment is still in Staging, the courier will not pick up your request, though everything else will occur as if in live mode.

API Parameters
shipper_contact_name string
Optional
The name of the shipper.
shipper_contact_phone string
Optional
The phone number of the shipper.
shipper_contact_email string
Optional
The email of the shipper.
shipper_organization string
Optional
The organization of the shipper.
origin_contact_name string
REQUIRED
The name of the person in the pickup location.
origin_contact_phone string
REQUIRED
The phone number of the person in the pickup location.
origin_contact_email string
Optional
The email of the person in the pickup location.
origin_address string
REQUIRED
Complete address of the pickup location.
origin_note string
Optional
Additional information of the pickup location to ease pickup process.
origin_postal_code number
*REQUIRED / OPTIONAL
Postal code of the pickup location.
origin_coordinate object
*REQUIRED / OPTIONAL
Coordinates of the pickup location. If you use an instant courier, you must use coordinate.
Show child parameters
origin_area_id string
*REQUIRED / OPTIONAL
Use area_id from Maps API.
origin_location_id string
Optional
Use location_id from Locations API.
origin_collection_method string
Optional
Use the available_collection_method from Rates API. Value can be pickup, or drop_off. Default to pickup.
pickup your package will be picked by courier based on origin that you specify.
drop_off you must drop off the package to the nearest courier agent.
destination_contact_name string
REQUIRED
The name of the person in destination location.
destination_contact_phone string
REQUIRED
The phone number of the person in destination location.
destination_contact_email string
Optional
The email of the person in destination location.
destination_address string
REQUIRED
Complete address of the destination location.
destination_note string
Optional
Additional information of the destination location to ease destination process.
destination_postal_code number
*REQUIRED / OPTIONAL
Postal code of the destination location.
destination_coordinate object
*REQUIRED / OPTIONAL
Coordinates of the destination location. If you use an instant courier, you must use coordinate.
Show child parameters
destination_area_id string
*REQUIRED / OPTIONAL
Use area_id from Maps API.
destination_location_id string
Optional
Use location_id from Locations API.
destination_cash_on_delivery number
Optional
State the COD Amount if you want to activate COD delivery.
destination_cash_on_delivery_type string
Optional
The COD disbursement window. Value can be 7_days, 5_days, or 3_days.
7_days you will receiver your money 7 days after the item is delivered.
5_days you will receiver your money 5 days after the item is delivered.
3_days you will receiver your money 3 days after the item is delivered.
destination_proof_of_delivery boolean
Optional
Proof of delivery feature.
destination_proof_of_delivery_note string
*REQUIRED / OPTIONAL
Notes for proof of delivery. It is required if proof of delivery feature is activated.
courier_company string
REQUIRED
Shipping provider that will be used for this particular shipment. List of available courier can be found using Couriers API.
courier_type string
REQUIRED
Courier type based on the courier company used. Each type can be different for each company. Value of type can be found within the Rates API and Couriers API.
courier_insurance number
Optional
The amount of the insurance value. This is optional if you want to insured your shipment. For example, if your item is valued at IDR 1.000.000, then you should put 1000000 for the value.
delivery_type string
REQUIRED
Type of delivery order is now and scheduled.
now will generate waybill instantly and pickup right away.
scheduled will generate waybill instantly and pickup based on delivery date and delivery time.
delivery_date string
Optional
The delivery date format: “YYYY-MM-DD”
delivery_time string
Optional
The delivery time format: “HH:mm”
order_note string
Optional
Additional information for the shipment.
metadata object
Optional
You can insert any kind of data through this object for internal purposes.
reference_id string
Optional
You can insert your internal order id here. Must unique for each order id.
tags array
Optional
You can insert multiple custom tags (in string) for filtering your orders by tag later on.
items array
REQUIRED
The list of item you will send for delivery
Show child parameters
Type of Request
Order for Standard Couriers
JSON Body Request
{
  "shipper_contact_name": "Amir",
  "shipper_contact_phone": "088888888888",
  "shipper_contact_email": "biteship@test.com",
  "shipper_organization": "Biteship Org Test",
  "origin_contact_name": "Amir",
  "origin_contact_phone": "088888888888",
  "origin_address": "Plaza Senayan, Jalan Asia Afrik...",
  "origin_note": "Deket pintu masuk STC",
  "origin_postal_code": 12440,
  "destination_contact_name": "John Doe",
  "destination_contact_phone": "088888888888",
  "destination_contact_email": "jon@test.com",
  "destination_address": "Lebak Bulus MRT...",
  "destination_postal_code": 12950,
  "destination_note": "Near the gas station",
  "courier_company": "jne",
  "courier_type": "reg",
  "courier_insurance": 500000,
  "delivery_type": "now",
  "order_note": "Please be careful",
  "metadata": {},
  "items": [
    {
      "name": "Black L",
      "description": "White Shirt",
      "category": "fashion",
      "value": 165000,
      "quantity": 1,
      "height": 10,
      "length": 10,
      "weight": 200,
      "width": 10
    }
  ]
}

Order for Instant Couriers
JSON Body Request
{
  "shipper_contact_name": "Amir",
  "shipper_contact_phone": "088888888888",
  "shipper_contact_email": "biteship@test.com",
  "shipper_organization": "Biteship Org Test",
  "origin_contact_name": "Amir",
  "origin_contact_phone": "088888888888",
  "origin_address": "Plaza Senayan, Jalan Asia Afrik...",
  "origin_note": "Deket pintu masuk STC",
  "origin_coordinate": {
    "latitude": -6.2253114,
    "longitude": 106.7993735
  },
  "destination_contact_name": "John Doe",
  "destination_contact_phone": "088888888888",
  "destination_contact_email": "jon@test.com",
  "destination_address": "Lebak Bulus MRT...",
  "destination_note": "Near the gas station",
  "destination_coordinate": {
    "latitude": -6.28927,
    "longitude": 106.77492000000007
  },
  "courier_company": "grab",
  "courier_type": "instant",
  "courier_insurance": 500000,
  "delivery_type": "now",
  "order_note": "Please be careful",
  "metadata": {},
  "items": [
    {
      "name": "Black L",
      "description": "White Shirt",
      "category": "fashion",
      "category": "fashion",
      "value": 165000,
      "quantity": 1,
      "height": 10,
      "length": 10,
      "weight": 200,
      "width": 10
    }
  ]
}

Order for Cash on Delivery
JSON Body Request
{
  "shipper_contact_name": "Amir",
  "shipper_contact_phone": "088888888888",
  "shipper_contact_email": "biteship@test.com",
  "shipper_organization": "Biteship Org Test",
  "origin_contact_name": "Amir",
  "origin_contact_phone": "088888888888",
  "origin_address": "Plaza Senayan, Jalan Asia Afrik...",
  "origin_note": "Deket pintu masuk STC",
  "origin_postal_code": 12440,
  "destination_contact_name": "John Doe",
  "destination_contact_phone": "088888888888",
  "destination_contact_email": "jon@test.com",
  "destination_address": "Lebak Bulus MRT...",
  "destination_note": "Near the gas station",
  "destination_postal_code": 12950,
  "destination_cash_on_delivery": 500000,
  "destination_cash_on_delivery_type": "7_days",
  "courier_company": "sicepat",
  "courier_type": "reg",
  "courier_insurance": 500000,
  "delivery_type": "now",
  "order_note": "Please be careful",
  "metadata": {},
  "items": [
    {
      "name": "Black L",
      "description": "White Shirt",
      "category": "fashion",
      "value": 165000,
      "quantity": 1,
      "height": 10,
      "length": 10,
      "weight": 200,
      "width": 10
    }
  ]
}

Order for Drop Off collection method
JSON Body Request
{
  "shipper_contact_name": "Amir",
  "shipper_contact_phone": "088888888888",
  "shipper_contact_email": "biteship@test.com",
  "shipper_organization": "Biteship Org Test",
  "origin_contact_name": "Amir",
  "origin_contact_phone": "088888888888",
  "origin_address": "Plaza Senayan, Jalan Asia Afrik...",
  "origin_note": "Deket pintu masuk STC",
  "origin_postal_code": 12440,
  "origin_collection_method": "drop_off",
  "destination_contact_name": "John Doe",
  "destination_contact_phone": "088888888888",
  "destination_contact_email": "jon@test.com",
  "destination_address": "Lebak Bulus MRT...",
  "destination_note": "Near the gas station",
  "destination_postal_code": 12950,
  "courier_company": "sicepat",
  "courier_type": "reg",
  "courier_insurance": 500000,
  "delivery_type": "now",
  "order_note": "Please be careful",
  "metadata": {},
  "items": [
    {
      "name": "Black L",
      "description": "White Shirt",
      "category": "fashion",
      "value": 165000,
      "quantity": 1,
      "height": 10,
      "length": 10,
      "weight": 200,
      "width": 10
    }
  ]
}

*When you send origin and destination value, you must at least choose one type of origin or destination. Origin and destination must at least have postal codes, coordinates or area ids. You do not need to insert all of the three values.

API Response
Order Created
Response
{
  "success": true,
  "message": "Order successfully created",
  "object": "order",
  "id": "5dd599ebdefcd4158eb8470b",
  "draft_order_id": null,
  "shipper": {
    "name": "Biteship Indonesia",
    "email": "Biteship@gmail.com",
    "phone": "08123456789",
    "organization": "Biteship"
  },
  "origin": {
    "contact_name": "Akbar",
    "contact_phone": "08123456789",
    "coordinate": {
      "latitude": -6.2253114,
      "longitude": 106.7993735
    },
    "address": "Plaza Senayan, Jalan Asia Afrika, RT.1/RW.3",
    "note": "Deket pintu masuk STC",
    "postal_code": 12440
  },
  "destination": {
    "contact_name": "Bambang",
    "contact_phone": "088888888888",
    "contact_email": "mirsa@biteship.com",
    "address": "Lebak Bulus MRT, Jalan R.A.Kartini",
    "note": "Di deket pintu MRT",
    "proof_of_delivery": {
      "use": false,
      "fee": 0,
      "note": null,
      "link": null
    },
    "cash_on_delivery": {
      "id": "77bb0f60b029822ecb1411da",
      "amount": 500000,
      "amount_currency": "IDR",
      "fee": 20000,
      "fee_currency": "IDR",
      "note": null,
      "type": "7_days"
    },
    "coordinate": {
      "latitude": -6.28927,
      "longitude": 106.77492000000007
    },
    "postal_code": 12950
  },
  "courier": {
    "tracking_id": "6de509ebdefgh4158ij3451c",
    "waybill_id": "WYB-1112223333443",
    "company": "anteraja",
    "name": null,  // Deprecated
    "phone": null, // Deprecated
    "driver_name": null,
    "driver_phone": null,
    "driver_photo_url": null,
    "driver_plate_number": null,
    "type": "reg",
    "link": null,
    "insurance": {
      "amount": 500000,
      "amount_currency": "IDR",
      "fee": 2500,
      "fee_currency": "IDR",
      "note": ""
    },
    "routing_code": null
  },
  "delivery": {
    "datetime": "2029-09-24T12:00+07:00",
    "note": null,
    "type": "now",
    "distance": 9.8,
    "distance_unit": "kilometer"
  },
  "reference_id": null,
  "items": [
    {
      "name": "Black L",
      "description": "Feast/Bangkok'19 Invasion",
      "sku": null,
      "value": 165000,
      "quantity": 1,
      "length": 10,
      "width": 10,
      "height": 10,
      "weight": 200
    }
  ],
  "extra": [],
  "currency": "IDR",
  "tax_lines": [],
  "price": 48000,
  "metadata": {},
  "note": "Please be careful",
  "status": "confirmed"
}

Failed to Create Order due to Reference ID already used
Response
{
  "success": false,
  "error": "Reference id has already been used before. Please input other reference id",
  "code": 40002060,
  "details": {
    "order_id": "660105377589b8dea565208b", // The order that uses given reference id
    "waybill_id": "1028309128390", // The waybill of order that uses given reference id
    "reference_id": "66010548c90b557a9e2dd7a4" // Given reference id
  }
}

Retrieve an Order
Endpoint
GET /v1/orders/:id

Check your order history or tracking by orderId. You can get the Order ID from the Order API request. Please assume all field Nullable.

Response
{
  "success": true,
  "message": "Order successfully retrieved",
  "object": "order",
  "id": "5dd599ebdefcd4158eb8470b",
  "draft_order_id": null,
  "short_id": "URf_UO2nY3V",
  "shipper": {
    "name": "Amir",
    "email": "biteship@example.com",
    "phone": "088888888888",
    "organization": "Biteship Org"
  },
  "origin": {
    "contact_name": "Amir",
    "contact_phone": "088888888888",
    "address": "Plaza Senayan, Jalan Asia Afrik...",
    "note": "Deket pintu masuk STC",
    "postal_code": 10270,
    "coordinate": {
      "latitude": -6.2253114,
      "longitude": 106.7993735
    }
  },
  "destination": {
    "contact_name": "John Doe",
    "contact_phone": "088888888888",
    "contact_email": "jon@example.com",
    "address": "Lebak Bulus MRT...",
    "note": "Near the gas station",
    "proof_of_delivery": {
      "use": false,
      "fee": 0,
      "note": null,
      "link": null
    },
    "postal_code": 12310,
    "coordinate": {
      "latitude": -6.28927,
      "longitude": 106.77492000000007
    },
    "cash_on_delivery": {
      "id": null,
      "amount": 0,
      "amount_currency": "IDR",
      "fee": 0,
      "fee_currency": "IDR",
      "note": null,
      "type": null
    }
  },
  "delivery": {
    "datetime": "2023-09-24T12:00+07:00",
    "note": null,
    "type": "now",
    "distance": 15.2,
    "distance_unit": "kilometer"
  },
  "voucher": {
    "id": null,
    "name": null,
    "value": null,
    "type": null
  },
  "courier": {
    "tracking_id": "65ddac3879699035b83dc561",
    "waybill_id": "WYB-1112223333442",
    "company": "jnt",
    "history": [
      {
        "service_type": "-",
        "status": "confirmed",
        "note": "Order has been confirmed. Locating nearest driver to pickup.",
        "updated_at": "2021-01-11T14:03:41+07:00"
      },
      {
        "service_type": "-",
        "status": "allocated",
        "note": "Courier has been allocated. Waiting to pick up.",
        "updated_at": "2021-01-11T15:49:25+07:00"
      }
    ],
    "link": "https://example.com/10298309123809",
    "name": "John Doe",   // Deprecated
    "phone": "0888888888",  // Deprecated
    "driver_name": "John Doe",
    "driver_phone": "0888888888",
    "driver_photo_url": "https://picsum.photos/200",
    "driver_plate_number": "B 1234 ABC",
    "type": "instant",
    "shipment_fee": 25000,
    "insurance": {
      "amount": 500000,
      "amount_currency": "IDR",
      "fee": 2500,
      "fee_currency": "IDR",
      "note": null
    },
    "routing_code": "123-JKT45A-67"
  },
  "reference_id": null,
  "invoice_id": null,
  "items": [
    {
      "name": "Black L",
      "description": "Feast/Bangkok'19 Invasion",
      "sku": null,
      "value": 165000,
      "quantity": 1,
      "length": 72,
      "width": 54,
      "height": 1,
      "weight": 200
    }
  ],
  "extra": null,
  "metadata": null,
  "tags": [],
  "note": "Please be careful",
  "currency": "IDR",
  "tax_lines": [],
  "price": 27500,
  "status": "allocated",
  "ticket_status": null
}


Delete an Order
Endpoint
POST    /v1/orders/:id/cancel
DELETE  /v1/orders/:id  // deprecated

Order can be cancelled or rejected based on order id upon request.

Cancellation Reason Codes
To cancel an order, please use one of the codes provided by this endpoint. Each code is paired with a cancellation reason. The cancellation reasons are available in two languages: Bahasa (id) and English (en), represented by the lang query parameter. The default language is Bahasa if you do not specify a language upon retrieval.

Endpoint
GET    /v1/orders/cancellation_reasons?lang=id // in bahasa
GET    /v1/orders/cancellation_reasons?lang=en // in english

Cancellation Reasons in Bahasa
Response Example
{
    "success": true,
    "message": "Order cancellation reasons successfully retrieved",
    "cancellation_reasons": [
        {
            "code": "change_courier",
            "reason": "Ingin mengganti kurir"
        },
        {
            "code": "pickup_delay",
            "reason": "Waktu penjemputan terlalu lama"
        },
        {
            "code": "change_address",
            "reason": "Ingin mengganti alamat"
        },
        {
            "code": "others",
            "reason": "Pesanan dibatalkan oleh pedagang karena alasan lain"
        }
    ]
}

Cancellation Reasons in English
Response Example
{
    "success": true,
    "message": "Order cancellation reasons successfully retrieved",
    "cancellation_reasons": [
        {
            "code": "change_courier",
            "reason": "Want to change courier"
        },
        {
            "code": "pickup_delay",
            "reason": "Pickup time too long"
        },
        {
            "code": "change_address",
            "reason": "Want to change address"
        },
        {
            "code": "others",
            "reason": "Order cancelled by merchant for other reason"
        }
    ]
}

Type of Requests
Using Cancellation Reason Code
JSON Body Request
{   
    "cancellation_reason_code": "change_courier"
}

Custom Cancellation Reason
JSON Body Request
{   
    "cancellation_reason_code": "others",
    "cancellation_reason": "Accidentally ordered"
}

API Response
Response
{
    "success": true,
    "message": "Order successfully deleted",
    "object": "order",
    "id": "5dd5a396248481164a225af4",
    "status": "cancelled",
    "cancellation_reason_code": "others"
    "cancellation_reason": "Accidentally ordered"
}